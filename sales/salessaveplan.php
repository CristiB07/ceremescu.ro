<?php
// Prevent PHP warnings/notices being sent as HTML to the client
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

include '../settings.php';
include '../classes/common.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$role = isset($_SESSION['function']) ? $_SESSION['function'] : '';
$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

// Prepare server-side log file
$logFile = __DIR__ . '/../logs/salessaveplan.log';
// initial call log removed to avoid noisy logging; subsequent logs remain

$eventId = $_POST['eventId'] ?? '';
$eventDateTime = date('Y-m-d H:i:s', strtotime($_POST['eventDateTime']));
$eventObjective = $_POST['eventObjective'];
$eventClient = $_POST['eventClient'] ?? null;
$eventZone = $_POST['eventZone'] ?? '';
$eventFinalized = isset($_POST['eventFinalized']) ? 1 : 0;
$eventTipVizita = $_POST['eventTipVizita'] ?? '';
$eventDetalii = substr(trim($_POST['eventDetalii'] ?? ''), 0, 500);
$sendInvite = isset($_POST['sendInvite']) ? 1 : 0;
$inviteEmailRaw = trim($_POST['inviteEmail'] ?? '');
// Support multiple invite emails (comma/semicolon/space separated)
$inviteEmailsClean = '';
$inviteEmailsArray = [];
if (!empty($inviteEmailRaw)) {
    // split invite list without using preg_split (avoid PCRE JIT)
    $normalized = str_replace([";", "\r", "\n", "\t"], ',', $inviteEmailRaw);
    $normalized = str_replace(' ', ',', $normalized);
    $parts = array_filter(array_map('trim', explode(',', $normalized)));
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        if (filter_var($p, FILTER_VALIDATE_EMAIL)) $inviteEmailsArray[] = $p;
    }
    $inviteEmailsArray = array_values(array_unique($inviteEmailsArray));
    $inviteEmailsClean = implode(',', $inviteEmailsArray);
}
// Duration in minutes (default 60)
$eventDurationMinutes = isset($_POST['eventDurationMinutes']) ? intval($_POST['eventDurationMinutes']) : 60;

header('Content-Type: application/json; charset=utf-8');

// Wrap main logic to catch unexpected errors and log them
try {

if (empty($eventDateTime) || empty($eventObjective)) {
    echo json_encode(['success' => false, 'message' => 'Date incomplete']);
    exit;
}

$eventDate = new DateTime($eventDateTime);
$today = new DateTime();
$today->setTime(0, 0, 0); // Ignoră ora pentru comparație

if ($eventDate < $today) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot face programări în trecut']);
    exit;
}

// Verificare sărbători
$eventDateStr = $eventDate->format('Y-m-d');
if (in_array($eventDateStr, $holidays)) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot face programări în zile de sărbătoare']);
    exit;
}

// Verificare weekend
$dayOfWeek = $eventDate->format('D'); // Mon, Tue, etc.
if (in_array($dayOfWeek, $skipdays)) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot face programări în weekend']);
    exit;
}

// Compute end datetime based on duration
$eventStart = new DateTime($eventDateTime);
$eventEnd = clone $eventStart;
$eventEnd->add(new DateInterval('PT' . max(1, $eventDurationMinutes) . 'M'));
$eventEndStr = $eventEnd->format('Y-m-d H:i:s');

// Canonical start/end objects for Graph/ICS
$dtStart = clone $eventStart;
$dtEnd = clone $eventEnd;

// Detect whether the table has the new start/end columns; fallback if necessary
$colStart = 'programare_data_inceput';
$colEnd = 'programare_data_sfarsit';
try {
    $res = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data_inceput'");
    if (!$res || mysqli_num_rows($res) == 0) {
        $colStart = 'programare_data';
    }
    $res2 = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data_sfarsit'");
    if (!$res2 || mysqli_num_rows($res2) == 0) {
        // If there's no dedicated end column, we'll omit it from queries
        $colEnd = null;
    }
} catch (Exception $e) {
    // keep defaults on error
    $colStart = 'programare_data_inceput';
    $colEnd = 'programare_data_sfarsit';
}

// Detect optional columns
$hasInviteEmail = false;
$hasDurata = false;
try {
    $r3 = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_invite_email'");
    if ($r3 && mysqli_num_rows($r3) > 0) $hasInviteEmail = true;
    $r4 = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_durata'");
    if ($r4 && mysqli_num_rows($r4) > 0) $hasDurata = true;
} catch (Exception $e) {
    // ignore
}

$clientInt = $eventClient ? intval($eventClient) : 0;
if ($eventId) {
    // Editare — build update dynamically depending on available columns
    $fields = array();
    $types = '';
    $params = array();

    $fields[] = "$colStart = ?";
    $types .= 's'; $params[] = $eventDateTime;

    if ($colEnd) {
        $fields[] = "$colEnd = ?";
        $types .= 's'; $params[] = $eventEndStr;
    }

    $fields[] = "programare_obiectiv = ?"; $types .= 's'; $params[] = $eventObjective;
    $fields[] = "programare_client = ?"; $types .= 'i'; $params[] = $clientInt;
    $fields[] = "programare_zona = ?"; $types .= 's'; $params[] = $eventZone;
    $fields[] = "programare_finalizata = ?"; $types .= 'i'; $params[] = $eventFinalized;
    $fields[] = "programare_tipvizita = ?"; $types .= 's'; $params[] = $eventTipVizita;
    $fields[] = "programare_detalii = ?"; $types .= 's'; $params[] = $eventDetalii;
    if ($hasInviteEmail) { $fields[] = "programare_invite_email = ?"; $types .= 's'; $params[] = $inviteEmailsClean; }
    if ($hasDurata) { $fields[] = "programare_durata = ?"; $types .= 'i'; $params[] = $eventDurationMinutes; }

    // WHERE
    $types .= 'i'; $params[] = $eventId;

    // Before preparing, ensure the columns exist in the actual DB schema (avoid Unknown column errors)
    $resCols = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='" . mysqli_real_escape_string($conn, $db_name) . "' AND TABLE_NAME='sales_programari'");
    $actualCols = array();
    if ($resCols) {
        while ($r = mysqli_fetch_assoc($resCols)) $actualCols[] = $r['COLUMN_NAME'];
    }

    // Filter $fields (update) to only include those columns that actually exist
    $filteredFields = array();
    $filteredParams = array();
    $filteredTypes = '';
    foreach ($fields as $idx => $f) {
        // extract column name before '=' for patterns like "col = ?" or direct column variables
        if (preg_match('/^\s*([^\s=]+)\s*=/', $f, $m)) {
            $col = $m[1];
        } else {
            // fallback - try to find explicit column in the string
            $col = trim(strtok($f, ' ='));
        }
        if (in_array($col, $actualCols)) {
            $filteredFields[] = $f;
            // map corresponding type/param: we need to recalc by iterating original params in order
            $filteredParams[] = $params[$idx];
            // types are appended in the same order as fields were added; recreate types conservatively by re-evaluating
            // We'll rebuild types later from $filteredParams using original knowledge where possible.
        }
    }

    // Rebuild types for filtered params: derive from original $types by mapping positions
    // original $types corresponds to $params sequence; we will keep a pointer
    $origTypes = $types;
    $newTypes = '';
    $typeIndex = 0;
    for ($i = 0; $i < strlen($origTypes); $i++) {
        // skip final 'i' appended for WHERE programare_id when present; we handle WHERE separately
        $newTypes .= $origTypes[$i];
        $typeIndex++;
    }

    // Build query with filtered fields
    $query = "UPDATE sales_programari SET " . implode(', ', $filteredFields) . " WHERE programare_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        // bind dynamically using filteredParams + the eventId at the end
        $bind_params = array();
        // Build types string for filtered params
        $types_for_bind = '';
        // We cannot reliably reconstruct types generically here; assume string for safety except ints known earlier
        foreach ($filteredParams as $pval) {
            if (is_int($pval)) $types_for_bind .= 'i'; else $types_for_bind .= 's';
        }
        $types_for_bind .= 'i'; // for WHERE id
        $bind_params[] = $types_for_bind;
        foreach ($filteredParams as $k => $v) $bind_params[] = &$filteredParams[$k];
        $bind_params[] = &$params[count($params)-1]; // eventId was appended as last param
        array_unshift($bind_params, $stmt);
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
    }
    
        // SERVER-SIDE OVERLAP CHECK (for edits)
        // Fetch existing appointments for that user on the same day and check for time overlap
        $eventDay = (new DateTime($eventDateTime))->format('Y-m-d');
        $dayStart = $eventDay . ' 00:00:00';
        $dayEnd = $eventDay . ' 23:59:59';
        $existingQ = "SELECT programare_id, " . $colStart . " as startcol" . ($colEnd ? ", " . $colEnd . " as endcol" : ", programare_durata") . " FROM sales_programari WHERE programare_user = ? AND DATE(" . $colStart . ") = ?";
        $exstmt = mysqli_prepare($conn, $existingQ);
        if ($exstmt) {
            mysqli_stmt_bind_param($exstmt, 'is', $uid, $eventDay);
            mysqli_stmt_execute($exstmt);
            $exres = mysqli_stmt_get_result($exstmt);
            while ($er = mysqli_fetch_array($exres, MYSQLI_ASSOC)) {
                $eid = intval($er['programare_id']);
                if ($eventId && $eid == intval($eventId)) continue; // skip self when editing
                $existingStart = $er['startcol'];
                $existingEnd = $er['endcol'] ?? null;
                if (!$existingEnd) {
                    $d = intval($er['programare_durata'] ?? 0);
                    if ($d>0) {
                        $dt = new DateTime($existingStart);
                        $dt->add(new DateInterval('PT' . $d . 'M'));
                        $existingEnd = $dt->format('Y-m-d H:i:s');
                    } else {
                        $dt = new DateTime($existingStart);
                        $dt->add(new DateInterval('PT60M'));
                        $existingEnd = $dt->format('Y-m-d H:i:s');
                    }
                }
                // check overlap: newStart < existingEnd AND newEnd > existingStart
                if ($eventDateTime < $existingEnd && $eventEndStr > $existingStart) {
                    echo json_encode(['success'=>false,'message'=>'Overlap with existing appointment at ' . $existingStart]);
                    exit;
                }
            }
            mysqli_stmt_close($exstmt);
        }
} else {
    // Adăugare — build insert dynamically depending on available columns
    $cols = array('programare_user', 'programare_client', $colStart);
    $placeholders = array('?', '?', '?');
    $types = 'iis';
    $params = array($uid, $clientInt, $eventDateTime);

    if ($colEnd) {
        $cols[] = $colEnd;
        $placeholders[] = '?';
        $types .= 's'; $params[] = $eventEndStr;
    }

    $cols[] = 'programare_obiectiv'; $placeholders[] = '?'; $types .= 's'; $params[] = $eventObjective;
    $cols[] = 'programare_finalizata'; $placeholders[] = '?'; $types .= 'i'; $params[] = $eventFinalized;
    $cols[] = 'programare_zona'; $placeholders[] = '?'; $types .= 's'; $params[] = $eventZone;
    $cols[] = 'programare_tipvizita'; $placeholders[] = '?'; $types .= 's'; $params[] = $eventTipVizita;
    $cols[] = 'programare_detalii'; $placeholders[] = '?'; $types .= 's'; $params[] = $eventDetalii;
    if ($hasInviteEmail) { $cols[] = 'programare_invite_email'; $placeholders[] = '?'; $types .= 's'; $params[] = $inviteEmailsClean; }
    if ($hasDurata) { $cols[] = 'programare_durata'; $placeholders[] = '?'; $types .= 'i'; $params[] = $eventDurationMinutes; }

    // Filter insert columns against actual schema
    $resCols = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='" . mysqli_real_escape_string($conn, $db_name) . "' AND TABLE_NAME='sales_programari'");
    $actualCols = array();
    if ($resCols) {
        while ($r = mysqli_fetch_assoc($resCols)) $actualCols[] = $r['COLUMN_NAME'];
    }
    $newCols = array();
    $newPlaceholders = array();
    $newParams = array();
    foreach ($cols as $idx => $c) {
        if (in_array($c, $actualCols)) {
            $newCols[] = $c;
            $newPlaceholders[] = $placeholders[$idx];
            $newParams[] = $params[$idx];
        }
    }
    $query = "INSERT INTO sales_programari (" . implode(', ', $newCols) . ") VALUES (" . implode(', ', $newPlaceholders) . ")";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        $bind_params = array();
        // construct types string from newParams
        $types_for_bind = '';
        foreach ($newParams as $pval) {
            if (is_int($pval)) $types_for_bind .= 'i'; else $types_for_bind .= 's';
        }
        $bind_params[] = $types_for_bind;
        foreach ($newParams as $k => $v) $bind_params[] = &$newParams[$k];
        array_unshift($bind_params, $stmt);
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
    }
}

// SERVER-SIDE OVERLAP CHECK (for inserts)
// Fetch existing appointments for that user on the same day and check for time overlap
$eventDay = (new DateTime($eventDateTime))->format('Y-m-d');
$existingQ = "SELECT programare_id, " . $colStart . " as startcol" . ($colEnd ? ", " . $colEnd . " as endcol" : ", programare_durata") . " FROM sales_programari WHERE programare_user = ? AND DATE(" . $colStart . ") = ?";
$exstmt = mysqli_prepare($conn, $existingQ);
if ($exstmt) {
    mysqli_stmt_bind_param($exstmt, 'is', $uid, $eventDay);
    mysqli_stmt_execute($exstmt);
    $exres = mysqli_stmt_get_result($exstmt);
    while ($er = mysqli_fetch_array($exres, MYSQLI_ASSOC)) {
        $existingStart = $er['startcol'];
        $existingEnd = $er['endcol'] ?? null;
        if (!$existingEnd) {
            $d = intval($er['programare_durata'] ?? 0);
            if ($d>0) {
                $dt = new DateTime($existingStart);
                $dt->add(new DateInterval('PT' . $d . 'M'));
                $existingEnd = $dt->format('Y-m-d H:i:s');
            } else {
                $dt = new DateTime($existingStart);
                $dt->add(new DateInterval('PT60M'));
                $existingEnd = $dt->format('Y-m-d H:i:s');
            }
        }
        if ($eventDateTime < $existingEnd && $eventEndStr > $existingStart) {
            echo json_encode(['success'=>false,'message'=>'Overlap with existing appointment at ' . $existingStart]);
            exit;
        }
    }
    mysqli_stmt_close($exstmt);
}

if (mysqli_stmt_execute($stmt)) {
    // If inserted, get the id (for invites)
    $insertedId = mysqli_insert_id($conn);

    // Handle sending .ics invite if requested (for new records or edits)
    if ($sendInvite) {
        // Get user email and encrypted password (utilizator_Email, utilizator_Parola, utilizator_Upgraded, prenume/nume)
        $userEmail = '';
        $userPassEncrypted = '';
        $userUpgraded = 1;
        $userFirstName = '';
        $userLastName = '';
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

        // Decrypt password if encrypted using legacy key store (same logic as in administrative/cs2excel.php)
        $mailerPassword = '';
        if (!empty($userPassEncrypted)) {
            $mailerPassword = $userPassEncrypted; // default - may be raw or encrypted
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

                                if ($decrypted !== false) {
                                    $mailerPassword = $decrypted;
                                } else {
                                    error_log("Failed to decrypt password for user: " . $userEmail);
                                    $mailerPassword = '';
                                }
                            } else {
                                error_log("Encrypted data too short for user: " . $userEmail);
                                $mailerPassword = '';
                            }
                        } else {
                            error_log("Encryption key not found for user: " . $userEmail);
                            $mailerPassword = '';
                        }
                    }
                } catch (Exception $e) {
                    error_log("Password decryption error: " . $e->getMessage());
                    $mailerPassword = '';
                }
            }
        }

        // If configured, try Microsoft Graph app-only creation first
        $graphCreated = false;
        if (isset($usemicrosoft) && $usemicrosoft == 1 && !empty($appid) && !empty($clientsecret) && !empty($tenantid) && !empty($userEmail)) {
            try {
                // Obtain token via client_credentials
                $tokenUrl = 'https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token';
                $fields = http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => $appid,
                    'client_secret' => $clientsecret,
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
                if ($tokenResp === false) {
                    @file_put_contents($logFile, date('c') . " - GRAPH TOKEN CURL ERROR: " . $tokenErr . "\n", FILE_APPEND);
                } else {
                    $tarr = json_decode($tokenResp, true);
                    if (isset($tarr['access_token'])) {
                        $accessToken = $tarr['access_token'];
                        // Build attendees list for Graph
                        $graphAttendees = [];
                        foreach ($attendees as $att) {
                            if (!empty($userEmail) && strcasecmp($att, $userEmail) === 0) continue; // don't add organizer
                            $graphAttendees[] = [
                                'emailAddress' => ['address' => $att, 'name' => $att],
                                'type' => 'required'
                            ];
                        }

                        $eventData = [
                            'subject' => $eventObjective,
                            'body' => [ 'contentType' => 'HTML', 'content' => nl2br(htmlspecialchars($eventDetalii)) ],
                            'start' => [ 'dateTime' => $dtStart->format('Y-m-d\TH:i:s'), 'timeZone' => 'Europe/Bucharest' ],
                            'end' => [ 'dateTime' => $dtEnd->format('Y-m-d\TH:i:s'), 'timeZone' => 'Europe/Bucharest' ],
                            'location' => [ 'displayName' => $eventZone ],
                            'attendees' => $graphAttendees
                        ];

                        $createUrl = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode($userEmail) . '/events';
                        $ch2 = curl_init();
                        curl_setopt($ch2, CURLOPT_URL, $createUrl);
                        curl_setopt($ch2, CURLOPT_POST, true);
                        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($eventData));
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                            'Authorization: Bearer ' . $accessToken,
                            'Content-Type: application/json'
                        ]);
                        $createResp = curl_exec($ch2);
                        $createErr = curl_error($ch2);
                        $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        if (PHP_VERSION_ID < 80500) { curl_close($ch2); }

                        if ($createResp === false) {
                            @file_put_contents($logFile, date('c') . " - GRAPH CREATE CURL ERROR: " . $createErr . "\n", FILE_APPEND);
                            @file_put_contents($logFile, date('c') . " - INVITE: falling back to SMTP for programare_id=" . ($insertedId ? $insertedId : $eventId) . "\n", FILE_APPEND);
                        } else {
                            if ($httpCode >= 200 && $httpCode < 300) {
                                $graphCreated = true;
                                @file_put_contents($logFile, date('c') . " - GRAPH EVENT CREATED: " . substr($createResp,0,200) . "\n", FILE_APPEND);
                                // log detailed graph response and event id if available
                                $createArr = json_decode($createResp, true);
                                $graphEventId = isset($createArr['id']) ? $createArr['id'] : null;
                                @file_put_contents($logFile, date('c') . " - INVITE METHOD: GRAPH; EVENT_ID: " . ($graphEventId ?? 'n/a') . "; RESP_TRUNC: " . substr($createResp,0,1000) . "\n", FILE_APPEND);
                                if (!empty($graphEventId)) {
                                    // ensure column exists
                                    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_graph_event_id'");
                                    if (mysqli_num_rows($colCheck) == 0) {
                                        @mysqli_query($conn, "ALTER TABLE sales_programari ADD COLUMN programare_graph_event_id VARCHAR(255) NULL");
                                    }
                                    $idToUpdate = $insertedId ? $insertedId : $eventId;
                                    $upd = mysqli_prepare($conn, "UPDATE sales_programari SET programare_graph_event_id = ? WHERE programare_id = ?");
                                    if ($upd) { mysqli_stmt_bind_param($upd, 'si', $graphEventId, $idToUpdate); mysqli_stmt_execute($upd); mysqli_stmt_close($upd); }
                                }
                                // mark in DB: update invite email (if column exists) and sent datetime; fall back to updating only sent datetime
                                $sentAt = date('Y-m-d H:i:s');
                                $idToUpdate = $insertedId ? $insertedId : $eventId;
                                if ($hasInviteEmail) {
                                    $updateStmt = mysqli_prepare($conn, "UPDATE sales_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?");
                                    if ($updateStmt) {
                                        mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate);
                                        mysqli_stmt_execute($updateStmt);
                                        mysqli_stmt_close($updateStmt);
                                    }
                                } else {
                                    $updateStmt = mysqli_prepare($conn, "UPDATE sales_programari SET programare_sent = ? WHERE programare_id = ?");
                                    if ($updateStmt) {
                                        mysqli_stmt_bind_param($updateStmt, 'si', $sentAt, $idToUpdate);
                                        mysqli_stmt_execute($updateStmt);
                                        mysqli_stmt_close($updateStmt);
                                    }
                                }
                            } else {
                                @file_put_contents($logFile, date('c') . " - GRAPH CREATE FAILED (HTTP $httpCode): " . $createResp . "\n", FILE_APPEND);
                                @file_put_contents($logFile, date('c') . " - INVITE: falling back to SMTP for programare_id=" . ($insertedId ? $insertedId : $eventId) . "\n", FILE_APPEND);
                            }
                        }
                    } else {
                        @file_put_contents($logFile, date('c') . " - GRAPH TOKEN RESPONSE NO ACCESS TOKEN: " . substr($tokenResp,0,200) . "\n", FILE_APPEND);
                    }
                }
            } catch (Exception $e) {
                @file_put_contents($logFile, date('c') . " - GRAPH EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        // If Graph succeeded, skip SMTP send. Otherwise fall back to ICS+SMTP send.
        if (!$graphCreated) {
            // Build ICS
            try {
            $dtStart = new DateTime($eventDateTime);
            // Use computed end from server-side duration calculation
            $dtEnd = new DateTime($eventEndStr);

            $uidCal = uniqid('prog_') . '@' . ($_SERVER['SERVER_NAME'] ?? 'masterapp');
            $dtstamp = gmdate('Ymd').'T'.gmdate('His').'Z';
            $dtstartStr = $dtStart->format('Ymd\THis');
            $dtendStr = $dtEnd->format('Ymd\THis');

            // Ensure UTF-8 and escape for iCalendar (RFC5545)
            $summary_utf = mb_convert_encoding($eventObjective ?? '', 'UTF-8', 'auto');
            $description_utf = mb_convert_encoding($eventDetalii ?? '', 'UTF-8', 'auto');
            $location_utf = mb_convert_encoding($eventZone ?? '', 'UTF-8', 'auto');

            $escape_ical = function($text) {
                // Escape backslashes first
                $text = str_replace('\\', '\\\\', $text);
                // Newlines -> literal \n
                $text = str_replace(array("\r\n", "\n", "\r"), '\\n', $text);
                // Escape commas and semicolons
                $text = str_replace(array(';', ','), array('\\;', '\\,'), $text);
                return $text;
            };

            $summary = $escape_ical($summary_utf);
            $description = $escape_ical($description_utf);
            $location = $escape_ical($location_utf);

            $ical = "BEGIN:VCALENDAR\r\n";
            $ical .= "VERSION:2.0\r\n";
            $ical .= "PRODID:-//" . ($siteCompanyShortSite ?? 'masterapp') . "//RO\r\n";
            $ical .= "METHOD:REQUEST\r\n";
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $uidCal . "\r\n";
            $ical .= "DTSTAMP:" . $dtstamp . "\r\n";
            $ical .= "DTSTART;TZID=Europe/Bucharest:" . $dtstartStr . "\r\n";
            $ical .= "DTEND;TZID=Europe/Bucharest:" . $dtendStr . "\r\n";
            $ical .= "SUMMARY:" . addcslashes($summary, "\r\n") . "\r\n";
            if (!empty($description)) $ical .= "DESCRIPTION:" . addcslashes($description, "\r\n") . "\r\n";
            if (!empty($location)) $ical .= "LOCATION:" . addcslashes($location, "\r\n") . "\r\n";
            if (!empty($userEmail)) {
                $ical .= "ORGANIZER:mailto:" . $userEmail . "\r\n";
                $orgCN = $escape_ical(trim($userFirstName . ' ' . $userLastName) ?: $userEmail);
                // Add organizer as an ATTENDEE with ROLE=CHAIR so clients treat them as organizer/chair
                $ical .= "ATTENDEE;CN=" . $orgCN . ";ROLE=CHAIR;PARTSTAT=ACCEPTED:mailto:" . $userEmail . "\r\n";
            }
            // Support multiple invite addresses (additional invitees). Organizer already added above.
            $attendees = array();
            if (!empty($inviteEmailsClean)) {
                foreach ($inviteEmailsArray as $p) {
                    $attendees[] = $p;
                }
            }
            // Deduplicate
            $attendees = array_values(array_unique($attendees));
            foreach ($attendees as $att) {
                // skip if same as organizer (we already added organizer above)
                if (!empty($userEmail) && strcasecmp($att, $userEmail) === 0) continue;
                $attCN = $escape_ical($att);
                $ical .= "ATTENDEE;CN=" . $attCN . ":mailto:" . $att . "\r\n";
            }
            $ical .= "END:VEVENT\r\nEND:VCALENDAR\r\n";

            // Send via PHPMailer with UTF-8 settings
            require_once __DIR__ . '/../vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // If we have user SMTP credentials, use them; otherwise skip sending and log
                if (empty($userEmail) || empty($mailerPassword)) {
                    error_log('Skipping invite send: missing user SMTP credentials for user ID ' . $uid);
                } else {
                    $mail->isSMTP();
                    $mail->Host = $SmtpServer; // server from global config
                    $mail->SMTPAuth = true;
                    $mail->Username = $userEmail; // use user email as SMTP user
                    $mail->Password = $mailerPassword; // decrypted user password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = $SmtpPort;
                    $fromName = trim(($userFirstName . ' ' . $userLastName) ?: ($siteCompanyShortSite ?? 'MasterApp'));
                    $mail->setFrom($userEmail, $fromName);
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->isHTML(false);

                    // Add recipients (invitees). Put organizer in CC so they don't get added as primary recipient twice.
                    foreach ($attendees as $rcpt) {
                        // skip if invitee equals organizer
                        if (!empty($userEmail) && strcasecmp($rcpt, $userEmail) === 0) continue;
                        $mail->addAddress($rcpt);
                    }
                    // Add organizer as primary recipient (To) so mail/calendar clients treat it as organizer
                    if (!empty($userEmail)) {
                        // ensure not added twice
                        $mail->addAddress($userEmail);
                    }

                    $mail->Subject = 'Invitație programare: ' . $summary_utf;
                    $mail->Body = "Aveți o programare:\n\n" . $summary_utf . "\n" . $description_utf . "\n\nData: " . $eventDateTime;

                    // Attach ICS (UTF-8) — write to a temp file and attach to improve compatibility with sent-folder handling
                    $tmpIcs = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'invitation_' . $uid . '_' . time() . '.ics';
                    @file_put_contents($tmpIcs, $ical);
                    if (file_exists($tmpIcs)) {
                        $mail->addAttachment($tmpIcs, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST');
                    } else {
                        // fallback to string attachment if temp file creation failed
                        $mail->addStringAttachment($ical, 'invitation.ics', 'base64', 'text/calendar; charset=UTF-8; method=REQUEST');
                    }

                    // Add headers that help mail clients treat this as a calendar message
                    $mail->addCustomHeader('Content-Class', 'urn:content-classes:calendarmessage');
                    $mail->addCustomHeader('X-MS-OLK-FORCEINSPECTOROPEN', 'TRUE');

                    // Provide inline iCal body for clients (PHPMailer supports $Ical)
                    try {
                        $mail->Ical = $ical;
                    } catch (Exception $e) {
                        // Ignore if not supported
                    }

                    $mail->send();

                    // Log SMTP send success with recipients
                    $recips = !empty($attendees) ? implode(',', $attendees) : '';
                    $recips .= (!empty($recips) && !empty($userEmail)) ? ',' . $userEmail : $userEmail;
                    @file_put_contents($logFile, date('c') . " - INVITE METHOD: SMTP; Mail sent to: " . ($recips ?: 'n/a') . "\n", FILE_APPEND);

                    // update DB flag and sent datetime: prefer storing invite emails if column exists
                    $sentAt = date('Y-m-d H:i:s');
                    $idToUpdate = $insertedId ? $insertedId : $eventId;
                    if ($hasInviteEmail) {
                        $updateStmt = mysqli_prepare($conn, "UPDATE sales_programari SET programare_invite_email = ?, programare_sent = ? WHERE programare_id = ?");
                        if ($updateStmt) {
                            mysqli_stmt_bind_param($updateStmt, 'ssi', $inviteEmailsClean, $sentAt, $idToUpdate);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                        }
                    } else {
                        $updateStmt = mysqli_prepare($conn, "UPDATE sales_programari SET programare_sent = ? WHERE programare_id = ?");
                        if ($updateStmt) {
                            mysqli_stmt_bind_param($updateStmt, 'si', $sentAt, $idToUpdate);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                        }
                    }
                    // remove temp ics file
                    if (!empty($tmpIcs) && file_exists($tmpIcs)) {
                        @unlink($tmpIcs);
                    }
                }
            } catch (Exception $e) {
                // Log mailer error including PHPMailer ErrorInfo if available
                $errMsg = 'Invite send error for user ID ' . $uid . ': ' . $e->getMessage();
                if (isset($mail) && property_exists($mail, 'ErrorInfo')) $errMsg .= ' PHPMailer-ErrorInfo: ' . $mail->ErrorInfo;
                @file_put_contents($logFile, date('c') . ' - ' . $errMsg . "\n", FILE_APPEND);
                // Do not fail the request because of email issues
            }
        } catch (Exception $e) {
            // ignore ICS errors
        }
    }

    echo json_encode(['success' => true]);
} else {
    $err = isset($conn) ? mysqli_error($conn) : 'no-connection';
    @file_put_contents($logFile, date('c') . " - DB/Exec error: $err\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $err]);
}

mysqli_stmt_close($stmt);
}
} catch (Throwable $e) {
    @file_put_contents($logFile, date('c') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>