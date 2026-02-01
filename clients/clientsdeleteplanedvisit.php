<?php
include '../settings.php';
include '../classes/common.php';
// Ensure any PHP warnings/fatals are returned as JSON to the frontend
if (!ob_get_level()) ob_start();
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    @ob_end_clean();
    http_response_code(500);
    echo json_encode(['success'=>false, 'message'=>"PHP Error: $message in $file on line $line"]);
    exit;
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err !== null) {
        @ob_end_clean();
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>'Fatal error: ' . $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line']]);
        exit;
    }
});

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != 'Yes') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}
$id = isset($_POST['eventId']) ? (int)$_POST['eventId'] : 0;
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
// Fetch appointment details before deleting
$fetch = mysqli_prepare($conn, "SELECT programare_invite_email, programare_sent, programare_data_inceput, programare_data_sfarsit, programare_obiectiv, programare_user, programare_graph_event_id FROM clienti_programari WHERE programare_id=?");
mysqli_stmt_bind_param($fetch, 'i', $id);
mysqli_stmt_execute($fetch);
$res = mysqli_stmt_get_result($fetch);
$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
mysqli_stmt_close($fetch);

// If there are invitees or sent flag, attempt cancellation
if ($row && (!empty($row['programare_invite_email']) || $row['programare_sent'])) {
    // Try Graph delete if we have stored event id
    $graphId = $row['programare_graph_event_id'] ?? null;
    $organizerId = $row['programare_user'] ?? null;
    $inviteList = $row['programare_invite_email'];
    $objective = $row['programare_obiectiv'] ?? 'Programare';
    $start = $row['programare_data_inceput'] ?? null;
    $end = $row['programare_data_sfarsit'] ?? null;

    $logFile = __DIR__ . '/../logs/clientsdelete.log';

    if ($graphId && $organizerId) {
        // try to find organizer email
        $ue = mysqli_prepare($conn, "SELECT utilizator_Email FROM date_utilizatori WHERE utilizator_id=?");
        mysqli_stmt_bind_param($ue, 'i', $organizerId);
        mysqli_stmt_execute($ue);
        $uer = mysqli_stmt_get_result($ue);
        $urow = mysqli_fetch_array($uer, MYSQLI_ASSOC);
        mysqli_stmt_close($ue);
        $organizerEmail = $urow['utilizator_Email'] ?? null;
            if ($organizerEmail) {
                // obtain app token via client_credentials to call Graph
                try {
                    $tokenUrl = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';
                    $fields = http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $appid,
                        'client_secret' => $clientsecret ?? $site_client_secret,
                        'scope' => 'https://graph.microsoft.com/.default'
                    ]);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
                    $tokenResp = curl_exec($ch);
                    $tokenErr = curl_error($ch);
                    if (PHP_VERSION_ID < 80500) { curl_close($ch); }
                    $tarr = json_decode($tokenResp, true);
                    $accessToken = $tarr['access_token'] ?? null;
                    if (empty($accessToken)) {
                        @file_put_contents($logFile, date('c') . " - GRAPH TOKEN FAILED: " . substr($tokenResp ?? '',0,400) . "\n", FILE_APPEND);
                    } else {
                        $url = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($organizerEmail) . '/events/' . rawurlencode($graphId);
                        $headr = ['Authorization: Bearer ' . $accessToken];
                        $ch2 = curl_init();
                        curl_setopt($ch2, CURLOPT_URL, $url);
                        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'DELETE');
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch2, CURLOPT_HTTPHEADER, $headr);
                        $resp = curl_exec($ch2);
                        $httpcode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        if (PHP_VERSION_ID < 80500) { curl_close($ch2); }
                        @file_put_contents($logFile, date('c') . " - GRAPH DELETE resp code: $httpcode; resp: " . substr($resp,0,800) . "\n", FILE_APPEND);
                    }
                } catch (Exception $e) {
                    @file_put_contents($logFile, date('c') . " - GRAPH DELETE EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
                }
        }
    }

    // Always attempt to send ICS cancellation to invitees as fallback
    if (!empty($inviteList)) {
        // build ICS CANCEL
        $uid = $graphId ?: uniqid('prog_');
        $dtstamp = gmdate('Ymd\THis\Z');
        $dtstart = $start ? gmdate('Ymd\THis\Z', strtotime($start)) : '';
        $dtend = $end ? gmdate('Ymd\THis\Z', strtotime($end)) : '';
        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . ($siteCompanyShortSite ?? 'masterapp') . "//RO\r\nMETHOD:CANCEL\r\nBEGIN:VEVENT\r\n";
        $ical .= "UID:" . $uid . "\r\n";
        if ($dtstamp) $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
        if ($dtstart) $ical .= "DTSTART:" . $dtstart . "\r\n";
        if ($dtend) $ical .= "DTEND:" . $dtend . "\r\n";
        $ical .= "SUMMARY:ANULARE: " . addslashes($objective) . "\r\n";
        if (!empty($organizerEmail)) $ical .= "ORGANIZER:MAILTO:" . $organizerEmail . "\r\n";
        // attendees
        // split invite list without using preg_split to avoid PCRE JIT allocation issues
        $normalized = str_replace([";", "\r", "\n", "\t"], ',', $inviteList);
        $normalized = str_replace(' ', ',', $normalized);
        $parts = array_filter(array_map('trim', explode(',', $normalized)));
        foreach ($parts as $p) { if ($p !== '') $ical .= "ATTENDEE;CN=". $p .":MAILTO:". $p ."\r\n"; }
        $ical .= "END:VEVENT\r\nEND:VCALENDAR";

        // send mail simple using PHPMailer if available
        try {
            if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                // fallback to mail()
                $to = $inviteList;
                $subject = 'Anulare programare: ' . $objective;
                $boundary = md5(time());
                $headers = "From: " . ($siteCompanyEmail ?? 'noreply@' . $siteURLShort) . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
                $body = "--$boundary\r\n";
                $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
                $body .= "Anulăm evenimentul: $objective\r\n\r\n";
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: text/calendar; charset=UTF-8; method=CANCEL\r\n\r\n";
                $body .= $ical . "\r\n";
                $body .= "--$boundary--";
                @mail($to, $subject, $body, $headers);
            } else {
                $mailClass = 'PHPMailer\\\\PHPMailer\\\\PHPMailer';
                $mail = new $mailClass(true);
                $mail->setFrom($siteCompanyEmail, $siteCompanyShortSite ?? $strSiteName);
                $parts = array_filter(array_map('trim', explode(',', str_replace([";", "\r", "\n", "\t"], ',', str_replace(' ', ',', $inviteList)))));
                foreach ($parts as $p) { if ($p) $mail->addAddress($p); }
                $mail->Subject = 'Anulare programare: ' . $objective;
                $mail->Body = 'Anulăm evenimentul: ' . $objective;
                $mail->addStringAttachment($ical, 'cancellation.ics', 'base64', 'text/calendar; charset=UTF-8; method=CANCEL');
                $mail->send();
            }
            @file_put_contents($logFile, date('c') . " - ICS CANCEL SENT to: $inviteList for programare_id=$id\n", FILE_APPEND);
        } catch (Exception $e) {
            @file_put_contents($logFile, date('c') . " - ICS CANCEL ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

// Proceed with delete
$stmt = mysqli_prepare($conn, "DELETE FROM clienti_programari WHERE programare_id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
$ok = mysqli_stmt_execute($stmt);
$err = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
if ($ok) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'message'=>$err]);
?>
