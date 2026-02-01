<?php
include '../settings.php';
include '../classes/common.php';
// Start output buffering and ensure any PHP warnings/fatals are returned as JSON
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
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$uid = (int)$_SESSION['uid'];
$eventId = isset($_POST['eventId']) ? (int)$_POST['eventId'] : 0;
$date = isset($_POST['eventDateTime']) ? trim($_POST['eventDateTime']) : '';
$objective = isset($_POST['eventObjective']) ? trim($_POST['eventObjective']) : '';
$client = isset($_POST['eventClient']) ? (int)$_POST['eventClient'] : 0;
$zone = isset($_POST['eventZone']) ? trim($_POST['eventZone']) : '';
$tip = isset($_POST['eventTipVizita']) ? trim($_POST['eventTipVizita']) : '';
$detalii = isset($_POST['eventDetalii']) ? trim($_POST['eventDetalii']) : '';
$finalized = isset($_POST['eventFinalized']) ? 1 : 0;
$inviteEmail = isset($_POST['inviteEmail']) ? trim($_POST['inviteEmail']) : '';
$duration = isset($_POST['eventDurationMinutes']) ? (int)$_POST['eventDurationMinutes'] : 60;

// normalize datetime and compute end time
$eventDateTime = date('Y-m-d H:i:s', strtotime($date));
$eventStart = new DateTime($eventDateTime);
$eventEnd = clone $eventStart;
$eventEnd->add(new DateInterval('PT' . max(1, $duration) . 'M'));
$eventEndStr = $eventEnd->format('Y-m-d H:i:s');

// prepare invite emails array/clean string
$inviteEmailsArray = array();
$inviteEmailsClean = '';
if (!empty($inviteEmail)) {
    // split invite list without using preg_split (avoid PCRE JIT issues)
    $normalized = str_replace([";", "\r", "\n", "\t"], ',', $inviteEmail);
    $normalized = str_replace(' ', ',', $normalized);
    $parts = array_filter(array_map('trim', explode(',', $normalized)));
    foreach ($parts as $p) {
        if ($p === '') continue;
        if (filter_var($p, FILTER_VALIDATE_EMAIL)) $inviteEmailsArray[] = $p;
    }
    $inviteEmailsArray = array_values(array_unique($inviteEmailsArray));
    $inviteEmailsClean = implode(',', $inviteEmailsArray);
}

$logFile = __DIR__ . '/../logs/clientssaveplan.log';

header('Content-Type: application/json; charset=utf-8');
if (empty($date) || empty($objective)) {
    echo json_encode(['success'=>false,'message'=>'Missing fields']);
    exit;
}

// compute normalized start/end datetimes
$startDT = date('Y-m-d H:i:s', strtotime($date));
$endDT = date('Y-m-d H:i:s', strtotime($startDT . ' + ' . max(1,$duration) . ' minutes'));

// check overlapping appointments for this user on the same date
$day = date('Y-m-d', strtotime($startDT));
$overlapStmt = mysqli_prepare($conn, "SELECT programare_id, programare_data_inceput, programare_durata, programare_data_sfarsit FROM clienti_programari WHERE programare_user = ? AND DATE(programare_data_inceput) = ? " . ($eventId>0?" AND programare_id <> ?":""));
if ($overlapStmt) {
    if ($eventId>0) mysqli_stmt_bind_param($overlapStmt, 'isi', $uid, $day, $eventId);
    else mysqli_stmt_bind_param($overlapStmt, 'is', $uid, $day);
    mysqli_stmt_execute($overlapStmt);
    $ovRes = mysqli_stmt_get_result($overlapStmt);
    while ($ov = mysqli_fetch_array($ovRes, MYSQLI_ASSOC)) {
        $exStart = $ov['programare_data_inceput'];
        if (!empty($ov['programare_data_sfarsit'])) {
            $exEnd = $ov['programare_data_sfarsit'];
        } elseif (!empty($ov['programare_durata'])) {
            $exEnd = date('Y-m-d H:i:s', strtotime($exStart . ' + ' . intval($ov['programare_durata']) . ' minutes'));
        } else {
            // assume 60 minutes
            $exEnd = date('Y-m-d H:i:s', strtotime($exStart . ' + 60 minutes'));
        }
        if (!(strtotime($endDT) <= strtotime($exStart) || strtotime($startDT) >= strtotime($exEnd))) {
            echo json_encode(['success'=>false,'message'=>'Overlap with existing appointment at ' . $exStart]);
            mysqli_stmt_close($overlapStmt);
            exit;
        }
    }
    mysqli_stmt_close($overlapStmt);
}

if ($eventId > 0) {
    $stmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_data_inceput=?, programare_data_sfarsit=?, programare_obiectiv=?, programare_client=?, programare_zona=?, programare_tipvizita=?, programare_detalii=?, programare_finalizata=?, programare_durata=?, programare_invite_email=? WHERE programare_id=?");
    // types: start(s), end(s), objective(s), client(i), zone(s), tip(s), detalii(s), finalized(i), duration(i), inviteEmail(s), eventId(i)
    mysqli_stmt_bind_param($stmt, "sssisssiisi", $startDT, $endDT, $objective, $client, $zone, $tip, $detalii, $finalized, $duration, $inviteEmailsClean, $eventId);
    $ok = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    if ($ok) {
        $insertedId = 0; // update case
        // Attempt Microsoft Graph calendar creation and fallback to ICS+SMTP similar to sales flow
        $graphCreated = false;
        if (!empty($inviteEmailsClean) || !empty($inviteEmail)) {
            // fetch user data for organizer
            $userEmail = '';$userPassEncrypted = ''; $userUpgraded = 1; $userFirstName=''; $userLastName='';
            $ustmt = mysqli_prepare($conn, "SELECT utilizator_Email, utilizator_Parola, utilizator_Upgraded, utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_ID = ? LIMIT 1");
            if ($ustmt) {
                mysqli_stmt_bind_param($ustmt, 'i', $uid);
                mysqli_stmt_execute($ustmt);
                $ures = mysqli_stmt_get_result($ustmt);
                $urow = mysqli_fetch_array($ures, MYSQLI_ASSOC);
                if ($urow) {
                    $userEmail = $urow['utilizator_Email'] ?? '';
                    $userPassEncrypted = $urow['utilizator_Parola'] ?? '';
                    $userUpgraded = intval($urow['utilizator_Upgraded'] ?? 1);
                    $userFirstName = $urow['utilizator_Prenume'] ?? '';
                    $userLastName = $urow['utilizator_Nume'] ?? '';
                }
                mysqli_stmt_close($ustmt);
            }

            // try decrypting user password if needed (legacy storage)
            $mailerPassword = '';
            if (!empty($userPassEncrypted)) {
                $mailerPassword = $userPassEncrypted;
                if ($userUpgraded === 0) {
                    try {
                        $email_hash = hash('sha256', $userEmail);
                        $stmt_key = $conn->prepare("SELECT cheie_secundara FROM date_utilizatori_chei WHERE cheie_primara = ?");
                        if ($stmt_key) {
                            $stmt_key->bind_param("s", $email_hash);
                            $stmt_key->execute();
                            $result_key = $stmt_key->get_result();
                            $row_key = $result_key->fetch_assoc();
                            $stmt_key->close();
                            if ($row_key && !empty($row_key['cheie_secundara'])) {
                                $encryption_key = hex2bin($row_key['cheie_secundara']);
                                $encrypted_data = base64_decode($userPassEncrypted);
                                if (strlen($encrypted_data) >= 16) {
                                    $iv = substr($encrypted_data, 0, 16);
                                    $ciphertext = substr($encrypted_data, 16);
                                    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
                                    if ($decrypted !== false) $mailerPassword = $decrypted; else $mailerPassword = '';
                                }
                            }
                        }
                    } catch (Exception $e) { error_log('Password decrypt error: '.$e->getMessage()); }
                }
            }

            // Attempt Graph if configured
            if (isset($usemicrosoft) && $usemicrosoft == 1 && !empty($appid) && !empty($site_client_secret) && !empty($tenantid) && !empty($userEmail)) {
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
                    if ($tokenResp !== false) {
                        $tarr = json_decode($tokenResp, true);
                        if (isset($tarr['access_token'])) {
                            $accessToken = $tarr['access_token'];
                            $graphAttendees = [];
                            foreach ($inviteEmailsArray as $att) {
                                if (!empty($userEmail) && strcasecmp($att, $userEmail) === 0) continue;
                                $graphAttendees[] = ['emailAddress'=>['address'=>$att,'name'=>$att],'type'=>'required'];
                            }
                            $eventData = [
                                'subject' => $objective,
                                'body' => ['contentType'=>'HTML','content'=>nl2br(htmlspecialchars($detalii))],
                                'start' => ['dateTime' => $eventStart->format('Y-m-d\TH:i:s'), 'timeZone'=>'Europe/Bucharest'],
                                'end' => ['dateTime' => $eventEnd->format('Y-m-d\TH:i:s'), 'timeZone'=>'Europe/Bucharest'],
                                'location' => ['displayName' => $zone],
                                'attendees' => $graphAttendees
                            ];
                            $createUrl = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($userEmail) . '/events';
                            $ch2 = curl_init();
                            curl_setopt($ch2, CURLOPT_URL, $createUrl);
                            curl_setopt($ch2, CURLOPT_POST, true);
                            curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($eventData));
                            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
                            $createResp = curl_exec($ch2);
                            $createErr = curl_error($ch2);
                            $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                            if (PHP_VERSION_ID < 80500) { curl_close($ch2); }
                            if ($createResp !== false && $httpCode >=200 && $httpCode < 300) {
                                $graphCreated = true;
                                $createArr = json_decode($createResp, true);
                                $graphEventId = isset($createArr['id']) ? $createArr['id'] : null;
                                @file_put_contents($logFile, date('c') . " - GRAPH CREATED: " . ($graphEventId ?? 'n/a') . "\n", FILE_APPEND);
                                // update DB programare_sent/programare_invite_email
                                $sentAt = date('Y-m-d H:i:s');
                                $idToUpdate = $eventId;
                                $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?");
                                if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                                // persist graph event id: ensure column exists then update
                                if (!empty($graphEventId)) {
                                    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM clienti_programari LIKE 'programare_graph_event_id'");
                                    if (mysqli_num_rows($colCheck) == 0) {
                                        @mysqli_query($conn, "ALTER TABLE clienti_programari ADD COLUMN programare_graph_event_id VARCHAR(255) NULL");
                                    }
                                    $upd = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_graph_event_id = ? WHERE programare_id = ?");
                                    if ($upd) { mysqli_stmt_bind_param($upd, 'si', $graphEventId, $idToUpdate); mysqli_stmt_execute($upd); mysqli_stmt_close($upd); }
                                }
                            } else {
                                @file_put_contents($logFile, date('c') . " - GRAPH FAILED HTTP $httpCode: " . substr($createResp ?? '',0,1000) . "\n", FILE_APPEND);
                            }
                        } else {
                            @file_put_contents($logFile, date('c') . " - GRAPH TOKEN NO ACCESS TOKEN: " . substr($tokenResp,0,200) . "\n", FILE_APPEND);
                        }
                    }
                } catch (Exception $e) {
                    @file_put_contents($logFile, date('c') . " - GRAPH EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }

            // If Graph not created, fallback to ICS+SMTP
            if (!$graphCreated) {
                try {
                    $dtStart = clone $eventStart;
                    $dtEnd = clone $eventEnd;
                    $uidCal = uniqid('prog_') . '@' . ($_SERVER['SERVER_NAME'] ?? 'masterapp');
                    $dtstamp = gmdate('Ymd') . 'T' . gmdate('His') . 'Z';
                    $dtstartStr = $dtStart->format('Ymd\THis');
                    $dtendStr = $dtEnd->format('Ymd\THis');
                    $escape_ical = function($text) {
                        $text = str_replace('\\', '\\\\', $text);
                        $text = str_replace(array("\r\n","\n","\r"),'\\n',$text);
                        $text = str_replace(array(';',','), array('\\;','\\,'), $text);
                        return $text;
                    };
                    $summary = $escape_ical(mb_convert_encoding($objective,'UTF-8','auto'));
                    $description = $escape_ical(mb_convert_encoding($detalii,'UTF-8','auto'));
                    $location = $escape_ical(mb_convert_encoding($zone,'UTF-8','auto'));
                    $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . ($siteCompanyShortSite ?? 'masterapp') . "//RO\r\nMETHOD:REQUEST\r\nBEGIN:VEVENT\r\n";
                    $ical .= "UID:" . $uidCal . "\r\n";
                    $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
                    $ical .= "DTSTART;TZID=Europe/Bucharest:" . $dtstartStr . "\r\n";
                    $ical .= "DTEND;TZID=Europe/Bucharest:" . $dtendStr . "\r\n";
                    $ical .= "SUMMARY:" . addcslashes($summary, "\r\n") . "\r\n";
                    if (!empty($description)) $ical .= "DESCRIPTION:" . addcslashes($description, "\r\n") . "\r\n";
                    if (!empty($location)) $ical .= "LOCATION:" . addcslashes($location, "\r\n") . "\r\n";
                    if (!empty($userEmail)) { $ical .= "ORGANIZER:mailto:" . $userEmail . "\r\n"; $orgCN = $escape_ical(trim($userFirstName . ' ' . $userLastName) ?: $userEmail); $ical .= "ATTENDEE;CN=" . $orgCN . ";ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:" . $userEmail . "\r\n"; }
                    $attendees = array();
                    if (!empty($inviteEmailsClean)) foreach ($inviteEmailsArray as $p) $attendees[] = $p;
                    $attendees = array_values(array_unique($attendees));
                    foreach ($attendees as $att) { if (!empty($userEmail) && strcasecmp($att,$userEmail)===0) continue; $attCN = $escape_ical($att); $ical .= "ATTENDEE;CN=" . $attCN . ":mailto:" . $att . "\r\n"; }
                    $ical .= "END:VEVENT\r\nEND:VCALENDAR\r\n";

                    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                        require_once __DIR__ . '/../vendor/autoload.php';
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        try {
                            if (empty($userEmail) || empty($mailerPassword)) {
                                error_log('Skipping invite send: missing user SMTP credentials for user ID ' . $uid);
                            } else {
                                $mail->isSMTP();
                                $mail->Host = $SmtpServer;
                                $mail->SMTPAuth = true;
                                $mail->Username = $userEmail;
                                $mail->Password = $mailerPassword;
                                $mail->SMTPSecure = 'tls';
                                $mail->Port = $SmtpPort;
                                $fromName = trim(($userFirstName . ' ' . $userLastName) ?: ($siteCompanyShortSite ?? 'MasterApp'));
                                $mail->setFrom($userEmail, $fromName);
                                $mail->CharSet = 'UTF-8'; $mail->Encoding = 'base64'; $mail->isHTML(false);
                                foreach ($attendees as $rcpt) { if (!empty($userEmail) && strcasecmp($rcpt,$userEmail)===0) continue; $mail->addAddress($rcpt); }
                                if (!empty($userEmail)) $mail->addAddress($userEmail);
                                $mail->Subject = 'Invitație programare: ' . $summary;
                                $mail->Body = "Aveți o programare:\n\n" . $summary . "\n" . $description . "\n\nData: " . $eventDateTime;
                                $tmpIcs = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'invitation_' . $uid . '_' . time() . '.ics';
                                @file_put_contents($tmpIcs, $ical);
                                if (file_exists($tmpIcs)) $mail->addAttachment($tmpIcs, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST'); else $mail->addStringAttachment($ical, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST');
                                $mail->addCustomHeader('Content-Class', 'urn:content-classes:calendarmessage');
                                $mail->send();
                                @file_put_contents($logFile, date('c') . " - SMTP sent to: " . implode(',', $attendees) . "\n", FILE_APPEND);
                                $sentAt = date('Y-m-d H:i:s'); $idToUpdate = $eventId; $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?"); if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                                if (!empty($tmpIcs) && file_exists($tmpIcs)) @unlink($tmpIcs);
                            }
                        } catch (Exception $e) {
                            $errMsg = 'Invite send error for user ID ' . $uid . ': ' . $e->getMessage(); if (isset($mail) && property_exists($mail,'ErrorInfo')) $errMsg .= ' PHPMailer: ' . $mail->ErrorInfo; @file_put_contents($logFile, date('c') . ' - ' . $errMsg . "\n", FILE_APPEND);
                        }
                    } else {
                        // fallback: send simple mail to invite addresses
                        $to = $inviteEmailsClean ?: $inviteEmail;
                        $subject = 'Invitație programare: ' . $objective;
                        $headers = 'From: ' . ($SmtpUser ?: ($siteCompanyEmail ?? 'no-reply@localhost')) . "\r\n" . 'Content-Type: text/html; charset=UTF-8';
                        $body = '<p>Detalii: ' . htmlspecialchars($detalii) . '</p><p>Data: ' . htmlspecialchars($eventDateTime) . '</p><p>Locație: ' . htmlspecialchars($zone) . '</p>';
                        @mail($to, $subject, $body, $headers);
                        $sentAt = date('Y-m-d H:i:s'); $idToUpdate = $eventId; $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?"); if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                    }
                } catch (Exception $e) { @file_put_contents($logFile, date('c') . " - ICS/SMTP exception: " . $e->getMessage() . "\n", FILE_APPEND); }
            }
        }
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>$err]);
    }
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO clienti_programari (programare_user, programare_client, programare_data_inceput, programare_data_sfarsit, programare_obiectiv, programare_finalizata, programare_zona, programare_tipvizita, programare_detalii, programare_durata, programare_invite_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iisssisssis", $uid, $client, $startDT, $endDT, $objective, $finalized, $zone, $tip, $detalii, $duration, $inviteEmailsClean);
    $ok = mysqli_stmt_execute($stmt);
    $err = mysqli_stmt_error($stmt);
    $insertedId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    if ($ok) {
        // run the same invite flow as update branch so inserts also create Graph/ICS invites
        $eventId = $insertedId;
        if (!empty($inviteEmailsClean) || !empty($inviteEmail)) {
            $graphCreated = false;
            // fetch user data for organizer
            $userEmail = '';$userPassEncrypted = ''; $userUpgraded = 1; $userFirstName=''; $userLastName='';
            $ustmt = mysqli_prepare($conn, "SELECT utilizator_Email, utilizator_Parola, utilizator_Upgraded, utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_ID = ? LIMIT 1");
            if ($ustmt) {
                mysqli_stmt_bind_param($ustmt, 'i', $uid);
                mysqli_stmt_execute($ustmt);
                $ures = mysqli_stmt_get_result($ustmt);
                $urow = mysqli_fetch_array($ures, MYSQLI_ASSOC);
                if ($urow) {
                    $userEmail = $urow['utilizator_Email'] ?? '';
                    $userPassEncrypted = $urow['utilizator_Parola'] ?? '';
                    $userUpgraded = intval($urow['utilizator_Upgraded'] ?? 1);
                    $userFirstName = $urow['utilizator_Prenume'] ?? '';
                    $userLastName = $urow['utilizator_Nume'] ?? '';
                }
                mysqli_stmt_close($ustmt);
            }

            // try decrypting user password if needed (legacy storage)
            $mailerPassword = '';
            if (!empty($userPassEncrypted)) {
                $mailerPassword = $userPassEncrypted;
                if ($userUpgraded === 0) {
                    try {
                        $email_hash = hash('sha256', $userEmail);
                        $stmt_key = $conn->prepare("SELECT cheie_secundara FROM date_utilizatori_chei WHERE cheie_primara = ?");
                        if ($stmt_key) {
                            $stmt_key->bind_param("s", $email_hash);
                            $stmt_key->execute();
                            $result_key = $stmt_key->get_result();
                            $row_key = $result_key->fetch_assoc();
                            $stmt_key->close();
                            if ($row_key && !empty($row_key['cheie_secundara'])) {
                                $encryption_key = hex2bin($row_key['cheie_secundara']);
                                $encrypted_data = base64_decode($userPassEncrypted);
                                if (strlen($encrypted_data) >= 16) {
                                    $iv = substr($encrypted_data, 0, 16);
                                    $ciphertext = substr($encrypted_data, 16);
                                    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
                                    if ($decrypted !== false) $mailerPassword = $decrypted; else $mailerPassword = '';
                                }
                            }
                        }
                    } catch (Exception $e) { error_log('Password decrypt error: '.$e->getMessage()); }
                }
            }

            // Attempt Graph if configured
            if (isset($usemicrosoft) && $usemicrosoft == 1 && !empty($appid) && !empty($site_client_secret) && !empty($tenantid) && !empty($userEmail)) {
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
                    if (PHP_VERSION_ID < 80500) { curl_close($ch); }
                    if ($tokenResp !== false) {
                        $tarr = json_decode($tokenResp, true);
                        if (isset($tarr['access_token'])) {
                            $accessToken = $tarr['access_token'];
                            $graphAttendees = [];
                            foreach ($inviteEmailsArray as $att) {
                                if (!empty($userEmail) && strcasecmp($att, $userEmail) === 0) continue;
                                $graphAttendees[] = ['emailAddress'=>['address'=>$att,'name'=>$att],'type'=>'required'];
                            }
                            $eventData = [
                                'subject' => $objective,
                                'body' => ['contentType'=>'HTML','content'=>nl2br(htmlspecialchars($detalii))],
                                'start' => ['dateTime' => $eventStart->format('Y-m-d\TH:i:s'), 'timeZone'=>'Europe/Bucharest'],
                                'end' => ['dateTime' => $eventEnd->format('Y-m-d\TH:i:s'), 'timeZone'=>'Europe/Bucharest'],
                                'location' => ['displayName' => $zone],
                                'attendees' => $graphAttendees
                            ];
                            $createUrl = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($userEmail) . '/events';
                            $ch2 = curl_init();
                            curl_setopt($ch2, CURLOPT_URL, $createUrl);
                            curl_setopt($ch2, CURLOPT_POST, true);
                            curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($eventData));
                            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
                            $createResp = curl_exec($ch2);
                            $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                            if (PHP_VERSION_ID < 80500) { curl_close($ch2); }
                            if ($createResp !== false && $httpCode >=200 && $httpCode < 300) {
                                $graphCreated = true;
                                $createArr = json_decode($createResp, true);
                                $graphEventId = isset($createArr['id']) ? $createArr['id'] : null;
                                @file_put_contents($logFile, date('c') . " - GRAPH CREATED: " . ($graphEventId ?? 'n/a') . "\n", FILE_APPEND);
                                $sentAt = date('Y-m-d H:i:s');
                                $idToUpdate = $eventId;
                                $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?");
                                if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                                if (!empty($graphEventId)) {
                                    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM clienti_programari LIKE 'programare_graph_event_id'");
                                    if (mysqli_num_rows($colCheck) == 0) {
                                        @mysqli_query($conn, "ALTER TABLE clienti_programari ADD COLUMN programare_graph_event_id VARCHAR(255) NULL");
                                    }
                                    $upd = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_graph_event_id = ? WHERE programare_id = ?");
                                    if ($upd) { mysqli_stmt_bind_param($upd, 'si', $graphEventId, $idToUpdate); mysqli_stmt_execute($upd); mysqli_stmt_close($upd); }
                                }
                            } else {
                                @file_put_contents($logFile, date('c') . " - GRAPH FAILED HTTP $httpCode: " . substr($createResp ?? '',0,1000) . "\n", FILE_APPEND);
                            }
                        } else {
                            @file_put_contents($logFile, date('c') . " - GRAPH TOKEN NO ACCESS TOKEN: " . substr($tokenResp,0,200) . "\n", FILE_APPEND);
                        }
                    }
                } catch (Exception $e) {
                    @file_put_contents($logFile, date('c') . " - GRAPH EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }

            // If Graph not created, fallback to ICS+SMTP
            if (!$graphCreated) {
                try {
                    $dtStart = clone $eventStart;
                    $dtEnd = clone $eventEnd;
                    $uidCal = uniqid('prog_') . '@' . ($_SERVER['SERVER_NAME'] ?? 'masterapp');
                    $dtstamp = gmdate('Ymd') . 'T' . gmdate('His') . 'Z';
                    $dtstartStr = $dtStart->format('Ymd\THis');
                    $dtendStr = $dtEnd->format('Ymd\THis');
                    $escape_ical = function($text) {
                        $text = str_replace('\\', '\\\\', $text);
                        $text = str_replace(array("\r\n","\n","\r"),'\\n',$text);
                        $text = str_replace(array(';',','), array('\\;','\\,'), $text);
                        return $text;
                    };
                    $summary = $escape_ical(mb_convert_encoding($objective,'UTF-8','auto'));
                    $description = $escape_ical(mb_convert_encoding($detalii,'UTF-8','auto'));
                    $location = $escape_ical(mb_convert_encoding($zone,'UTF-8','auto'));
                    $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . ($siteCompanyShortSite ?? 'masterapp') . "//RO\r\nMETHOD:REQUEST\r\nBEGIN:VEVENT\r\n";
                    $ical .= "UID:" . $uidCal . "\r\n";
                    $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
                    $ical .= "DTSTART;TZID=Europe/Bucharest:" . $dtstartStr . "\r\n";
                    $ical .= "DTEND;TZID=Europe/Bucharest:" . $dtendStr . "\r\n";
                    $ical .= "SUMMARY:" . addcslashes($summary, "\r\n") . "\r\n";
                    if (!empty($description)) $ical .= "DESCRIPTION:" . addcslashes($description, "\r\n") . "\r\n";
                    if (!empty($location)) $ical .= "LOCATION:" . addcslashes($location, "\r\n") . "\r\n";
                    if (!empty($userEmail)) { $ical .= "ORGANIZER:mailto:" . $userEmail . "\r\n"; $orgCN = $escape_ical(trim($userFirstName . ' ' . $userLastName) ?: $userEmail); $ical .= "ATTENDEE;CN=" . $orgCN . ";ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:" . $userEmail . "\r\n"; }
                    $attendees = array();
                    if (!empty($inviteEmailsClean)) foreach ($inviteEmailsArray as $p) $attendees[] = $p;
                    $attendees = array_values(array_unique($attendees));
                    foreach ($attendees as $att) { if (!empty($userEmail) && strcasecmp($att,$userEmail)===0) continue; $attCN = $escape_ical($att); $ical .= "ATTENDEE;CN=" . $attCN . ":mailto:" . $att . "\r\n"; }
                    $ical .= "END:VEVENT\r\nEND:VCALENDAR\r\n";

                    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                        require_once __DIR__ . '/../vendor/autoload.php';
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        try {
                            if (empty($userEmail) || empty($mailerPassword)) {
                                error_log('Skipping invite send: missing user SMTP credentials for user ID ' . $uid);
                            } else {
                                $mail->isSMTP();
                                $mail->Host = $SmtpServer;
                                $mail->SMTPAuth = true;
                                $mail->Username = $userEmail;
                                $mail->Password = $mailerPassword;
                                $mail->SMTPSecure = 'tls';
                                $mail->Port = $SmtpPort;
                                $fromName = trim(($userFirstName . ' ' . $userLastName) ?: ($siteCompanyShortSite ?? 'MasterApp'));
                                $mail->setFrom($userEmail, $fromName);
                                $mail->CharSet = 'UTF-8'; $mail->Encoding = 'base64'; $mail->isHTML(false);
                                foreach ($attendees as $rcpt) { if (!empty($userEmail) && strcasecmp($rcpt,$userEmail)===0) continue; $mail->addAddress($rcpt); }
                                if (!empty($userEmail)) $mail->addAddress($userEmail);
                                $mail->Subject = 'Invitație programare: ' . $summary;
                                $mail->Body = "Aveți o programare:\n\n" . $summary . "\n" . $description . "\n\nData: " . $eventDateTime;
                                $tmpIcs = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'invitation_' . $uid . '_' . time() . '.ics';
                                @file_put_contents($tmpIcs, $ical);
                                if (file_exists($tmpIcs)) $mail->addAttachment($tmpIcs, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST'); else $mail->addStringAttachment($ical, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST');
                                $mail->addCustomHeader('Content-Class', 'urn:content-classes:calendarmessage');
                                $mail->send();
                                @file_put_contents($logFile, date('c') . " - SMTP sent to: " . implode(',', $attendees) . "\n", FILE_APPEND);
                                $sentAt = date('Y-m-d H:i:s'); $idToUpdate = $eventId; $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?"); if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                                if (!empty($tmpIcs) && file_exists($tmpIcs)) @unlink($tmpIcs);
                            }
                        } catch (Exception $e) {
                            $errMsg = 'Invite send error for user ID ' . $uid . ': ' . $e->getMessage(); if (isset($mail) && property_exists($mail,'ErrorInfo')) $errMsg .= ' PHPMailer: ' . $mail->ErrorInfo; @file_put_contents($logFile, date('c') . ' - ' . $errMsg . "\n", FILE_APPEND);
                        }
                    } else {
                        // fallback: send simple mail to invite addresses
                        $to = $inviteEmailsClean ?: $inviteEmail;
                        $subject = 'Invitație programare: ' . $objective;
                        $headers = 'From: ' . ($SmtpUser ?: ($siteCompanyEmail ?? 'no-reply@localhost')) . "\r\n" . 'Content-Type: text/html; charset=UTF-8';
                        $body = '<p>Detalii: ' . htmlspecialchars($detalii) . '</p><p>Data: ' . htmlspecialchars($eventDateTime) . '</p><p>Locație: ' . htmlspecialchars($zone) . '</p>';
                        @mail($to, $subject, $body, $headers);
                        $sentAt = date('Y-m-d H:i:s'); $idToUpdate = $eventId; $updateStmt = mysqli_prepare($conn, "UPDATE clienti_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?"); if ($updateStmt) { mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate); mysqli_stmt_execute($updateStmt); mysqli_stmt_close($updateStmt); }
                    }
                } catch (Exception $e) { @file_put_contents($logFile, date('c') . " - ICS/SMTP exception: " . $e->getMessage() . "\n", FILE_APPEND); }
            }
        }
        echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>$err]);
}
?>
