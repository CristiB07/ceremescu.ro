<?php
include '../settings.php';
include '../classes/common.php';

// Ensure any PHP warnings/fatals are returned as JSON to the frontend
if (!ob_get_level()) ob_start();
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    @ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "PHP Error: $message in $file on line $line"]);
    exit;
});

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err !== null) {
        @ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line']]);
        exit;
    }
});

session_start();
$role = $_SESSION['function'];
$uid = $_SESSION['uid'];

$eventId = $_POST['eventId'];

header('Content-Type: application/json');

if (empty($eventId)) {
    echo json_encode(['success' => false, 'message' => 'ID lipsă']);
    exit;
}

// Fetch appointment details (respect user scope)
$fetchQuery = "SELECT programare_invite_email, programare_sent, programare_data_inceput, programare_data_sfarsit, programare_obiectiv, programare_user, programare_graph_event_id FROM sales_programari WHERE programare_id=?" . ($role=='USER' ? ' AND programare_user=?' : '');
$fetch = mysqli_prepare($conn, $fetchQuery);
if ($role=='USER') mysqli_stmt_bind_param($fetch, 'ii', $eventId, $uid); else mysqli_stmt_bind_param($fetch, 'i', $eventId);
mysqli_stmt_execute($fetch);
$res = mysqli_stmt_get_result($fetch);
$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
mysqli_stmt_close($fetch);

if ($row && (!empty($row['programare_invite_email']) || $row['programare_sent'])) {
    $graphId = $row['programare_graph_event_id'] ?? null;
    $organizerId = $row['programare_user'] ?? null;
    $inviteList = $row['programare_invite_email'];
    $objective = $row['programare_obiectiv'] ?? 'Programare';
    $start = $row['programare_data_inceput'] ?? null;
    $end = $row['programare_data_sfarsit'] ?? null;

    $logFile = __DIR__ . '/../logs/salesdelete.log';

    if ($graphId && $organizerId) {
        $ue = mysqli_prepare($conn, "SELECT utilizator_Email FROM date_utilizatori WHERE utilizator_id=?");
        mysqli_stmt_bind_param($ue, 'i', $organizerId);
        mysqli_stmt_execute($ue);
        $uer = mysqli_stmt_get_result($ue);
        $urow = mysqli_fetch_array($uer, MYSQLI_ASSOC);
        mysqli_stmt_close($ue);
        $organizerEmail = $urow['utilizator_Email'] ?? null;
        if ($organizerEmail) {
            $url = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($organizerEmail) . '/events/' . rawurlencode($graphId);
            $headr = ['Authorization: Bearer ' . $site_client_token];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
            $resp = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (PHP_VERSION_ID < 80500) { curl_close($ch); }
            @file_put_contents($logFile, date('c') . " - GRAPH DELETE resp code: $httpcode; resp: " . substr($resp,0,800) . "\n", FILE_APPEND);
        }
    }

    if (!empty($inviteList)) {
        $uidv = $graphId ?: uniqid('prog_');
        $dtstamp = gmdate('Ymd\THis\Z');
        $dtstart = $start ? gmdate('Ymd\THis\Z', strtotime($start)) : '';
        $dtend = $end ? gmdate('Ymd\THis\Z', strtotime($end)) : '';
        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . ($siteCompanyShortSite ?? 'masterapp') . "//RO\r\nMETHOD:CANCEL\r\nBEGIN:VEVENT\r\n";
        $ical .= "UID:" . $uidv . "\r\n";
        if ($dtstamp) $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
        if ($dtstart) $ical .= "DTSTART:" . $dtstart . "\r\n";
        if ($dtend) $ical .= "DTEND:" . $dtend . "\r\n";
        $ical .= "SUMMARY:ANULARE: " . addslashes($objective) . "\r\n";
        if (!empty($organizerEmail)) $ical .= "ORGANIZER:MAILTO:" . $organizerEmail . "\r\n";
        // split invite list without using preg_split to avoid PCRE JIT issues
        $normalized = str_replace([";", "\r", "\n", "\t"], ',', $inviteList);
        $normalized = str_replace(' ', ',', $normalized);
        $parts = array_filter(array_map('trim', explode(',', $normalized)));
        foreach ($parts as $p) { if ($p !== '') $ical .= "ATTENDEE;CN=". $p .":MAILTO:". $p ."\r\n"; }
        $ical .= "END:VEVENT\r\nEND:VCALENDAR";

        try {
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $to = $inviteList; $subject = 'Anulare programare: ' . $objective;
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
                $mailClass = 'PHPMailer\\PHPMailer\\PHPMailer';
                $mail = new $mailClass(true);
                $mail->setFrom($siteCompanyEmail, $siteCompanyShortSite ?? $strSiteName);
                $parts = array_filter(array_map('trim', explode(',', str_replace([";", "\r", "\n", "\t"], ',', str_replace(' ', ',', $inviteList)))));
                foreach ($parts as $p) { if ($p) $mail->addAddress($p); }
                $mail->Subject = 'Anulare programare: ' . $objective;
                $mail->Body = 'Anulăm evenimentul: ' . $objective;
                $mail->addStringAttachment($ical, 'cancellation.ics', 'base64', 'text/calendar; charset=UTF-8; method=CANCEL');
                $mail->send();
            }
            @file_put_contents($logFile, date('c') . " - ICS CANCEL SENT to: $inviteList for programare_id=$eventId\n", FILE_APPEND);
        } catch (Exception $e) {
            @file_put_contents($logFile, date('c') . " - ICS CANCEL ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

// Delete row
$query = "DELETE FROM sales_programari WHERE programare_id=?";
if ($role == 'USER') {
    $query .= " AND programare_user=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $eventId, $uid);
} else {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $eventId);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
?>