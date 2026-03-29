<?php
// Administrare concedii — angajați pot introduce/edita/șterge (cu restricții), ADMIN vizualizează pentru toți
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin'])){
    header("location:$strSiteURL/login/index.php?message=MLF");
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$uid = (int)$_SESSION['uid'];
$role = $_SESSION['clearence'] ?? '';
$strPageTitle = $strHolidays ?? 'Concedii';
include '../dashboard/header.php';

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

// Load current user (for SMTP credentials / sender name)
$stmt_user = $conn->prepare("SELECT * FROM date_utilizatori WHERE utilizator_ID = ?");
$stmt_user->bind_param('i', $uid);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$rowu = $result_user->fetch_assoc();
$stmt_user->close();

// Default fallbacks
$User = $siteCompanyEmail;
$Pass = '';
$Nume = $siteCompanyShortSite;

if ($rowu) {
    $User = $rowu['utilizator_Email'];
    $Pass = $rowu['utilizator_Parola'];
    // Decrypt password if encrypted (utilizator_Upgraded = 0 means encrypted)
    if (intval($rowu['utilizator_Upgraded']) === 0) {
        try {
            $email_hash = hash('sha256', $User);
            $stmt_key = $conn->prepare("SELECT cheie_secundara FROM date_utilizatori_chei WHERE cheie_primara = ?");
            $stmt_key->bind_param("s", $email_hash);
            $stmt_key->execute();
            $result_key = $stmt_key->get_result();
            $row_key = $result_key->fetch_assoc();
            $stmt_key->close();
            if ($row_key && !empty($row_key['cheie_secundara'])) {
                $encryption_key = hex2bin($row_key['cheie_secundara']);
                $encrypted_data = base64_decode($Pass);
                if (strlen($encrypted_data) >= 16) {
                    $iv = substr($encrypted_data, 0, 16);
                    $encrypted_password = substr($encrypted_data, 16);
                    $decrypted = openssl_decrypt($encrypted_password, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
                    if ($decrypted !== false) {
                        $Pass = $decrypted;
                    } else {
                        error_log("Failed to decrypt password for user: " . $User);
                        $Pass = '';
                    }
                } else {
                    error_log("Encrypted data too short for user: " . $User);
                    $Pass = '';
                }
            } else {
                error_log("Encryption key not found for user: " . $User);
                $Pass = '';
            }
        } catch (Exception $e) {
            error_log("Password decryption error: " . $e->getMessage());
            $Pass = '';
        }
    }
    $Nume = $rowu['utilizator_Prenume'] . ' ' . $rowu['utilizator_Nume'];
}

// helpers
function count_working_days_between($start, $end, $holidays, $skipdays) {
    $startTs = strtotime($start);
    $endTs = strtotime($end);
    if ($endTs < $startTs) return 0;
    $days = 0;
    for ($t = $startTs; $t <= $endTs; $t += 86400) {
        $d = date('Y-m-d', $t);
        $dow = date('D', $t);
        if (in_array($d, $holidays) || in_array($dow, $skipdays)) continue;
        $days++;
    }
    return $days;
}
// Create/Update/Delete actions
if (isset($_GET['mode']) && $_GET['mode'] === 'delete') {
    // CSRF + id + permission checks
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) die('<div class="callout alert">Invalid CSRF token</div>');
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) die('<div class="callout alert">Invalid ID</div>');
    $cID = (int)$_GET['cID'];

    // load record
    $stmt = $conn->prepare("SELECT * FROM administrative_concedii WHERE concediu_id = ?");
    $stmt->bind_param('i', $cID);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if (!$row) { header("location: siteholidays.php"); exit(); }

    // permission: owner can delete only if start > today or role ADMIN
    $today = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($row['concediu_data_inceput']));
    if ($row['concediu_angajat'] != $uid && $role !== 'ADMIN') {
        die('<div class="callout alert">Unauthorized</div>');
    }
    if ($role !== 'ADMIN' && $startDate <= $today) {
        die('<div class="callout alert">Concediile începute nu pot fi anulate (doar ADMIN)</div>');
    }

    $stmt = $conn->prepare("DELETE FROM administrative_concedii WHERE concediu_id = ?");
    $stmt->bind_param('i', $cID);
    $stmt->execute();
    $stmt->close();

    // get employee info (recipient)
    $stmt_emp = $conn->prepare("SELECT utilizator_Prenume, utilizator_Nume, utilizator_Email FROM date_utilizatori WHERE utilizator_ID = ?");
    $stmt_emp->bind_param('i', $row['concediu_angajat']);
    $stmt_emp->execute();
    $emp = $stmt_emp->get_result()->fetch_assoc();
    $stmt_emp->close();

    // send email (anulare concediu) to the employee + CCs
    $emailbody = "<html>";
    $emailbody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
    $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
    $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
    $emailbody .= "<style>body {margin:10px;font-size:1.1em;font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}h1,h2,h3,h4,h5{font-family:'Open Sans',sans-serif;font-weight:bold;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}p{margin:0 0 10px 0;}</style>";
    $emailbody .= "</head><body>";
    $emailbody .= "<a href=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "\"><img src=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/img/logo.png\" title=\"" . htmlspecialchars($strSiteOwner, ENT_QUOTES, 'UTF-8') . "\" width=\"150\" height=\"auto\"/></a>";
    $emailbody .= "<p>Bună ziua,</p>";
    $emailbody .= "<p>Vă rog să anulați concediul pentru perioada " . htmlspecialchars(date('d.m.Y', strtotime($row['concediu_data_inceput'])), ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars(date('d.m.Y', strtotime($row['concediu_data_sfarsit'])), ENT_QUOTES, 'UTF-8') . ".</p>";
    $emailbody .= "<p>Notă: " . htmlspecialchars($row['concediu_nota'], ENT_QUOTES, 'UTF-8') . "</p>";
    $emailbody .= "<p>Mulțumesc,<br />" . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . "</p>";
    $emailbody .= "</body>";
    $emailbody .= "</html>";

//Create a new PHPMailer instance
$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = $SmtpServer;
$mail->Port = $SmtpPort;
$mail->SMTPAuth = true;
$mail->Username = $User;
$mail->Password = $Pass;
//Set who the message is to be sent from (use logged-in user or fallback)
$mail->setFrom($User, $Nume);
$mail->addReplyTo($User, $Nume);
// recipients: Claudia, Cristian, and employee (if available)
if (!empty($emp['utilizator_Email'])) $mail->addAddress($emp['utilizator_Email']);
$mail->addAddress('claudia.banu@consaltis.ro', 'Claudia BANU');
$mail->AddCC('cristian.banu@consaltis.ro', 'Cristian BANU');
$mail->SMTPOptions = array(
    'ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true)
);
$mail->Subject = $subject;
$mail->isHTML(true);
$mail->Body = $emailbody;
@$mail->send();

    echo "<div class=\"callout success\">$strRecordDeleted</div>";
    echo "<script>setTimeout(function(){ window.location='siteholidays.php'; },1200);</script>";
    include '../bottom.php';
    exit();
}

// Approve — set `concediu_aprobat = 1` (only MANAGER function or ADMIN role)
if (isset($_GET['mode']) && $_GET['mode'] === 'approve') {
    // CSRF + id + permission checks (same pattern as delete)
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) die('<div class="callout alert">Invalid CSRF token</div>');
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) die('<div class="callout alert">Invalid ID</div>');
    $cID = (int)$_GET['cID'];
    $func = $_SESSION['function'] ?? '';
    if ($role !== 'ADMIN' && $func !== 'MANAGER') {
        die('<div class="callout alert">Unauthorized</div>');
    }

    // load the leave so we know the employee and details (used for email)
    $stmt_r = $conn->prepare("SELECT concediu_angajat, concediu_data_inceput, concediu_data_sfarsit, concediu_nota FROM administrative_concedii WHERE concediu_id = ?");
    $stmt_r->bind_param('i', $cID);
    $stmt_r->execute();
    $leave = $stmt_r->get_result()->fetch_assoc();
    $stmt_r->close();

    if ($leave) {
        // perform approval update
        $stmt = $conn->prepare("UPDATE administrative_concedii SET concediu_aprobat = 1 WHERE concediu_id = ?");
        $stmt->bind_param('i', $cID);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        // send confirmation email from approver ($uid) to the employee (concediu_angajat)
        if ($ok && $affected > 0) {
            // get employee details
            $empId = (int)$leave['concediu_angajat'];
            $stmt_e = $conn->prepare("SELECT utilizator_Prenume, utilizator_Nume, utilizator_Email FROM date_utilizatori WHERE utilizator_ID = ?");
            $stmt_e->bind_param('i', $empId);
            $stmt_e->execute();
            $emp = $stmt_e->get_result()->fetch_assoc();
            $stmt_e->close();

            if (!empty($emp['utilizator_Email'])) {
                $approverName = $rowu['utilizator_Prenume'] . ' ' . $rowu['utilizator_Nume'];
                $subject = 'Concediu aprobat';
                $emailbody = "<html>";
                $emailbody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
                $emailbody .= "<style>body {margin:10px;font-size:1.05em;font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}p{margin:0 0 10px 0;}</style>";
                $emailbody .= "</head><body>";
                $emailbody .= "<p>Bună ziua " . htmlspecialchars($emp['utilizator_Prenume'] . ' ' . $emp['utilizator_Nume'], ENT_QUOTES, 'UTF-8') . ",</p>";
                $emailbody .= "<p>Concediul pentru perioada <strong>" . htmlspecialchars(date('d.m.Y', strtotime($leave['concediu_data_inceput'])), ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars(date('d.m.Y', strtotime($leave['concediu_data_sfarsit'])), ENT_QUOTES, 'UTF-8') . "</strong> a fost aprobat de " . htmlspecialchars($approverName, ENT_QUOTES, 'UTF-8') . ".</p>";
                if (!empty($leave['concediu_nota'])) {
                    $emailbody .= "<p>Notă: " . htmlspecialchars($leave['concediu_nota'], ENT_QUOTES, 'UTF-8') . "</p>";
                }
                $emailbody .= "<p>Mulțumim,<br/>" . htmlspecialchars($approverName, ENT_QUOTES, 'UTF-8') . "</p>";
                $emailbody .= "</body></html>";

                // send mail using approver's SMTP credentials (same pattern used elsewhere)
                $mail = new PHPMailer();
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->Host = $SmtpServer;
                $mail->Port = $SmtpPort;
                $mail->SMTPAuth = true;
                $mail->Username = $User; // approver's email (set at top from session)
                $mail->Password = $Pass;
                $mail->setFrom($User, $approverName);
                $mail->addReplyTo($User, $approverName);
                $mail->addAddress($emp['utilizator_Email']);
                // keep existing management CCs for visibility
                $mail->AddCC('cristian.banu@consaltis.ro', 'Cristian BANU');
                $mail->addAddress('claudia.banu@consaltis.ro', 'Claudia BANU');
                $mail->SMTPOptions = array('ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true));
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body = $emailbody;
                @ $mail->send();
            }
        }

        echo "<div class=\"callout success\">Concediul a fost aprobat</div>";
        echo "<script>setTimeout(function(){ window.location='siteholidays.php'; },1200);</script>";
        include '../bottom.php';
        exit();
    } else {
        die('<div class="callout alert">Înregistrare inexistentă</div>');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_inject();
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('<div class="callout alert">Invalid CSRF token</div>');

    $start = $_POST['concediu_data_inceput'] ?? '';
    $end = $_POST['concediu_data_sfarsit'] ?? '';
    $nota = trim($_POST['concediu_nota'] ?? '');

    // normalize dates -> DATETIME
    $startDt = date('Y-m-d 00:00:00', strtotime($start));
    $endDt = date('Y-m-d 23:59:59', strtotime($end));

    if (empty($start) || empty($end) || strtotime($end) < strtotime($start)) {
        echo "<div class=\"callout alert\">Date invalide</div>"; include '../bottom.php'; exit();
    }

    // new
    if (isset($_GET['mode']) && $_GET['mode'] === 'new') {
        // allow ADMIN to create for another employee via optional form field
        $targetUser = $uid;
        if ($role === 'ADMIN' && isset($_POST['concediu_angajat']) && is_numeric($_POST['concediu_angajat'])) {
            $targetUser = (int)$_POST['concediu_angajat'];
        }

        $stmt = $conn->prepare("INSERT INTO administrative_concedii (concediu_data_inceput, concediu_data_sfarsit, concediu_nota, concediu_angajat) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $startDt, $endDt, $nota, $targetUser);
        $stmt->execute();
        $stmt->close();

        // send email (cerere concediu) to the target employee + CCs
        $stmt_u = $conn->prepare("SELECT utilizator_Prenume, utilizator_Nume, utilizator_Email FROM date_utilizatori WHERE utilizator_ID = ?");
        $stmt_u->bind_param('i', $targetUser);
        $stmt_u->execute();
        $ru = $stmt_u->get_result()->fetch_assoc();
        $stmt_u->close();

        $subject = 'Cerere concediu';
        $Nume = $ru['utilizator_Prenume'] . ' ' . $ru['utilizator_Nume'];
        $emailbody = "<html>";
        $emailbody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
        $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
        $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
        $emailbody .= "<style>body {margin:10px;font-size:1.1em;font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}h1,h2{font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}p{margin:0 0 10px 0;}</style>";
        $emailbody .= "</head><body>";
        $emailbody .= "<a href=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "\"><img src=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/img/logo.png\" width=\"150\"/></a>";
        $emailbody .= "<p>Bună ziua,</p>";
        $emailbody .= "<p>Vă rog să-mi aprobați concediul pentru perioada " . htmlspecialchars(date('d.m.Y', strtotime($startDt)), ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars(date('d.m.Y', strtotime($endDt)), ENT_QUOTES, 'UTF-8') . ".</p>";
        $emailbody .= "<p>Notă: " . htmlspecialchars($nota, ENT_QUOTES, 'UTF-8') . "</p>";
        $emailbody .= "<p>Mulțumesc,<br />" . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . "</p>";
        $emailbody .= "</body>";
        $emailbody .= "</html>";

     
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server
$mail->Host = $SmtpServer;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $SmtpPort;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = $User;
//Password to use for SMTP authentication
$mail->Password = $Pass;
//Set who the message is to be sent from
$mail->setFrom($User, $Nume);
//Set an alternative reply-to address
$mail->addReplyTo($User, $Nume);
//Set who the message is to be sent to
$mail->ConfirmReadingTo = $User;
$mail->addAddress('claudia.banu@consaltis.ro', 'Claudia BANU');
$mail->AddCC('cristian.banu@consaltis.ro', 'Cristian BANU');
$mail->SMTPOptions = array(
    'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
    )
);

//Set the subject line
$mail->Subject = $subject;
$mail->isHTML(true);
$mail->Body = $emailbody;
@$mail->send();
        echo "<div class=\"callout success\">Cerere introdusă</div>";
        echo "<script>setTimeout(function(){ window.location='siteholidays.php'; },1200);</script>";
        include '../bottom.php';
        exit();
    }

    // edit
    if (isset($_GET['mode']) && $_GET['mode'] === 'edit') {
        if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) { echo '<div class="callout alert">' . htmlspecialchars($strInvalidID, ENT_QUOTES, 'UTF-8') . '</div>'; include '../bottom.php'; exit(); }
        $cID = (int)$_GET['cID'];
        // load record
        $stmt = $conn->prepare("SELECT * FROM administrative_concedii WHERE concediu_id = ?");
        $stmt->bind_param('i', $cID);
        $stmt->execute();
        $rec = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$rec) { echo '<div class="callout alert">' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</div>'; include '../bottom.php'; exit(); }

        // allow edit only before start date unless ADMIN
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime($rec['concediu_data_inceput']));
        if ($role !== 'ADMIN' && $rec['concediu_angajat'] != $uid) { die('<div class="callout alert">Unauthorized</div>'); }
        if ($role !== 'ADMIN' && $startDate <= $today) { die('<div class="callout alert">Concediul a început — nu poate fi modificat</div>'); }

        // Reset approval whenever a record is modified
        $stmt = $conn->prepare("UPDATE administrative_concedii SET concediu_data_inceput = ?, concediu_data_sfarsit = ?, concediu_nota = ?, concediu_aprobat = NULL WHERE concediu_id = ?");
        $stmt->bind_param('sssi', $startDt, $endDt, $nota, $cID);
        $stmt->execute();
        $stmt->close();

        // notify (modificare)
        $stmt_u = $conn->prepare("SELECT utilizator_Prenume, utilizator_Nume, utilizator_Email FROM date_utilizatori WHERE utilizator_ID = ?");
        $stmt_u->bind_param('i', $rec['concediu_angajat']);
        $stmt_u->execute();
        $ru = $stmt_u->get_result()->fetch_assoc();
        $stmt_u->close();

        $subject = 'Cerere modificare concediu';
        $Nume = $ru['utilizator_Prenume'] . ' ' . $ru['utilizator_Nume'];
        $emailbody = "<html>";
        $emailbody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
        $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
        $emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
        $emailbody .= "<style>body {margin:10px;font-size:1.1em;font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}h1,h2{font-family:'Open Sans',sans-serif;color:" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}p{margin:0 0 10px 0;}</style>";
        $emailbody .= "</head><body>";
        $emailbody .= "<a href=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "\"><img src=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/img/logo.png\" width=\"150\"/></a>";
        $emailbody .= "<p>Bună ziua,</p>";
        $emailbody .= "<p>Vă rog să luați în considerare modificarea concediului pentru perioada " . htmlspecialchars(date('d.m.Y', strtotime($startDt)), ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars(date('d.m.Y', strtotime($endDt)), ENT_QUOTES, 'UTF-8') . ".</p>";
        $emailbody .= "<p>Notă: " . htmlspecialchars($nota, ENT_QUOTES, 'UTF-8') . "</p>";
        $emailbody .= "<p>Mulțumesc,<br />" . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . "</p>";
        $emailbody .= "</body>";
        $emailbody .= "</html>";
      $mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server
$mail->Host = $SmtpServer;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $SmtpPort;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = $User;
//Password to use for SMTP authentication
$mail->Password = $Pass;
//Set who the message is to be sent from
$mail->setFrom($User, $Nume);
//Set an alternative reply-to address
$mail->addReplyTo($User, $Nume);
//Set who the message is to be sent to
$mail->ConfirmReadingTo = $User;
$mail->addAddress('claudia.banu@consaltis.ro', 'Claudia BANU');
$mail->AddCC('cristian.banu@consaltis.ro', 'Cristian BANU');
$mail->SMTPOptions = array(
    'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
    )
);

//Set the subject line
$mail->Subject = $subject;
$mail->isHTML(true);
$mail->Body = $emailbody;
@$mail->send();
        echo "<div class=\"callout success\">Modificare salvată</div>";
        echo "<script>setTimeout(function(){ window.location='siteholidays.php'; },1200);</script>";
        include '../bottom.php';
        exit();
    }
}

// UI: if ADMIN or function=MANAGER show manager view (filter by year/user), else show user view
echo "<div class=\"grid-x grid-padding-x\"><div class=\"large-12 cell\"><h1>" . htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8') . "</h1>";

$func = $_SESSION['function'] ?? '';
if ($role === 'ADMIN' || $func === 'MANAGER') {
    // Manager/ADMIN — list all with year + optional user filter
    $filterYear = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');
    echo "<form method=\"get\" action=\"siteholidays.php\"><label>An: <select name=\"year\">";
    for ($y = date('Y')-2; $y <= date('Y')+1; $y++) {
        $sel = $y == $filterYear ? 'selected' : '';
        echo "<option value=\"$y\" $sel>$y</option>";
    }
    echo "</select> ";

    // user filter (helpful for MANAGER)
    $filterUser = isset($_GET['user']) && is_numeric($_GET['user']) ? (int)$_GET['user'] : 0;
    echo "<label>User: <select name=\"user\"><option value=\"0\">Toți</option>";
    $ustmt = $conn->prepare("SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori ORDER BY utilizator_Nume ASC");
    $ustmt->execute();
    $ures = $ustmt->get_result();
    while ($uu = $ures->fetch_assoc()) {
        $selu = ($filterUser && $filterUser == $uu['utilizator_ID']) ? 'selected' : '';
        echo '<option value="' . (int)$uu['utilizator_ID'] . '" ' . $selu . '>' . htmlspecialchars($uu['utilizator_Prenume'] . ' ' . $uu['utilizator_Nume'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
    $ustmt->close();
    echo "</select></label><input type=\"submit\" value=\"Filtrează\" class=\"button\"></form><hr>";

    // --- add form (visible to everyone in manager/admin view) ---
    echo '<h3>Introduce concediu</h3>';
    echo '<form method="post" action="siteholidays.php?mode=new">';
    echo '<div class="grid-x grid-padding-x"><div class="large-3 medium-3 cell">';
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
    // ADMIN may choose employee; others add for themselves
    if ($role === 'ADMIN') {
        echo '<label>Angajat: <select name="concediu_angajat">';
        $stmt_users = $conn->prepare("SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori ORDER BY utilizator_Nume ASC");
        $stmt_users->execute();
        $res_users = $stmt_users->get_result();
        while ($u = $res_users->fetch_assoc()) {
            echo '<option value="' . (int)$u['utilizator_ID'] . '">' . htmlspecialchars($u['utilizator_Prenume'] . ' ' . $u['utilizator_Nume'], ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $stmt_users->close();
        echo '</select></label></div>';
    }
    echo '<div class="large-2 medium-2 cell"><label>Data început: <input type="date" name="concediu_data_inceput" required></label></div>';
    echo '<div class="large-2 medium-2 cell"><label>Data sfârșit: <input type="date" name="concediu_data_sfarsit" required></label></div>';
    echo '<div class="large-3 medium-3 cell"><label>Notă: <input type="text" name="concediu_nota" maxlength="245"></label></div>';
    echo '<div class="large-2 medium-2 cell"><label>&nbsp;</label><input type="submit" class="button success" value="Trimite cerere" /></div></div></form><hr />';

    // fetch list with optional user filter
    $filterYear = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : date('Y');
    if ($filterUser > 0) {
        $stmt = $conn->prepare("SELECT a.*, u.utilizator_Prenume, u.utilizator_Nume FROM administrative_concedii a LEFT JOIN date_utilizatori u ON u.utilizator_ID = a.concediu_angajat WHERE (YEAR(a.concediu_data_inceput) = ? OR YEAR(a.concediu_data_sfarsit) = ?) AND a.concediu_angajat = ? ORDER BY a.concediu_data_inceput DESC");
        $stmt->bind_param('iii', $filterYear, $filterYear, $filterUser);
    } else {
        $stmt = $conn->prepare("SELECT a.*, u.utilizator_Prenume, u.utilizator_Nume FROM administrative_concedii a LEFT JOIN date_utilizatori u ON u.utilizator_ID = a.concediu_angajat WHERE (YEAR(a.concediu_data_inceput) = ? OR YEAR(a.concediu_data_sfarsit) = ?) ORDER BY a.concediu_data_inceput DESC");
        $stmt->bind_param('ii', $filterYear, $filterYear);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    echo '<table class="stack"><thead><tr><th>Angajat</th><th>Început</th><th>Sfârșit</th><th>Zile</th><th>Notă</th><th>Stare</th><th>Acțiuni</th></tr></thead><tbody>'; 
    while ($r = $res->fetch_assoc()) {
        $days = count_working_days_between($r['concediu_data_inceput'], $r['concediu_data_sfarsit'], $holidays, $skipdays);
        $startDate = date('Y-m-d', strtotime($r['concediu_data_inceput']));
        // determine if current session can edit/delete this row
        if ($role === 'ADMIN') {
            $editable = true;
        } else {
            $editable = ($r['concediu_angajat'] == $uid && $startDate > date('Y-m-d'));
        }
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['utilizator_Prenume'] . ' ' . $r['utilizator_Nume'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars(date('d.m.Y', strtotime($r['concediu_data_inceput'])), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars(date('d.m.Y', strtotime($r['concediu_data_sfarsit'])), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($days, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($r['concediu_nota'], ENT_QUOTES, 'UTF-8') . '</td>';
        $statusLabel = is_null($r['concediu_aprobat']) ? 'În curs de aprobare' : ($r['concediu_aprobat'] == 1 ? 'Aprobat' : '—');
        echo '<td>' . htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>';
        if ($editable) {
            echo '<a class="button" href="siteholidays.php?mode=edit&cID=' . (int)$r['concediu_id'] . '">Modifică</a> ';
            echo '<a class="alert button" href="siteholidays.php?mode=delete&cID=' . (int)$r['concediu_id'] . '&csrf_token=' . urlencode($_SESSION['csrf_token']) . '" onclick="return confirm(\'Confirmați?\')">Șterge</a>';
        } else {
            echo '&nbsp;';
        }
        // show approve button for MANAGER/ADMIN when not yet approved
        if (($role === 'ADMIN' || $func === 'MANAGER') && is_null($r['concediu_aprobat'])) {
            echo ' <a class="success button" href="siteholidays.php?mode=approve&cID=' . (int)$r['concediu_id'] . '&csrf_token=' . urlencode($_SESSION['csrf_token']) . '" onclick="return confirm(\'Aprobați acest concediu?\')">Aprobă</a>';
        }
        echo '</td>';
        echo '</tr>'; 
    }
    echo '</tbody></table>';
    $stmt->close();

} else {
    // Employee view — list own leaves + form
    // compute used days in current year
    $yearNow = date('Y');
    $stmt = $conn->prepare("SELECT * FROM administrative_concedii WHERE concediu_angajat = ? ORDER BY concediu_data_inceput DESC");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();

    $usedDays = 0;
    $leaves = [];
    while ($r = $res->fetch_assoc()) {
        $days = count_working_days_between($r['concediu_data_inceput'], $r['concediu_data_sfarsit'], $holidays, $skipdays);
        // only count days within current year
        $sy = (int)date('Y', strtotime($r['concediu_data_inceput']));
        $ey = (int)date('Y', strtotime($r['concediu_data_sfarsit']));
        if ($sy <= $yearNow && $ey >= $yearNow) {
            // compute overlap within $yearNow
            $from = max(strtotime($r['concediu_data_inceput']), strtotime($yearNow . '-01-01'));
            $to = min(strtotime($r['concediu_data_sfarsit']), strtotime($yearNow . '-12-31'));
            $usedDays += count_working_days_between(date('Y-m-d', $from), date('Y-m-d', $to), $holidays, $skipdays);
        }
        // store for workingdays autofill (per month/day)
        $leaves[] = $r;
    }
    $stmt->close();

    $remaining = (int)$legalholydays - $usedDays;
    if ($remaining > 3) echo "<div class=\"callout success\">Aveți $remaining zile rămase din $legalholydays.</div>";
    elseif ($remaining >= 0) echo "<div class=\"callout warning\">Aveți $remaining zile rămase din $legalholydays.</div>";
    else echo "<div class=\"callout alert\">Aveți " . abs($remaining) . " zile peste zilele de concediu stabilite.</div>";

    // new form
    echo '<h3>Introduce concediu</h3>';
    echo '<form method="post" action="siteholidays.php?mode=new">';
    echo '<div class="grid-x grid-padding-x"><div class="large-2 medium-2 cell">';
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '" />';
    echo '<label>Data început: <input type="date" name="concediu_data_inceput" required></label></div> ';
    echo '<div class="large-2 medium-2 cell"><label>Data sfârșit: <input type="date" name="concediu_data_sfarsit" required></label></div> ';
    echo '<div class="large-4 medium-4 cell"><label>Notă: <input type="text" name="concediu_nota" maxlength="245"></label></div> ';
    echo '<div class="large-2 medium-2 cell"><label>&nbsp;</label><input type="submit" class="button success" value="Trimite cerere" /></div></div></form><hr />';

    // list own leaves with edit/delete where allowed
    echo '<h3>Lista concediilor mele</h3>';
    echo '<table class="stack"><thead><tr><th>Început</th><th>Sfârșit</th><th>Zile</th><th>Notă</th><th>Stare</th><th>Acțiuni</th></tr></thead><tbody>'; 
    foreach ($leaves as $r) {
        $days = count_working_days_between($r['concediu_data_inceput'], $r['concediu_data_sfarsit'], $holidays, $skipdays);
        $startDate = date('Y-m-d', strtotime($r['concediu_data_inceput']));
        $editable = ($role === 'ADMIN' || $startDate > date('Y-m-d'));
        echo '<tr>';
        echo '<td>' . htmlspecialchars(date('d.m.Y', strtotime($r['concediu_data_inceput'])), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars(date('d.m.Y', strtotime($r['concediu_data_sfarsit'])), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($days, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($r['concediu_nota'], ENT_QUOTES, 'UTF-8') . '</td>';
        $statusLabel = is_null($r['concediu_aprobat']) ? 'În curs de aprobare' : ($r['concediu_aprobat'] == 1 ? 'Aprobat' : '—');
        echo '<td>' . htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>';
        if ($editable) {
            echo '<a class="button" href="siteholidays.php?mode=edit&cID=' . (int)$r['concediu_id'] . '">Modifică</a> ';
            echo '<a class="alert button" href="siteholidays.php?mode=delete&cID=' . (int)$r['concediu_id'] . '&csrf_token=' . urlencode($_SESSION['csrf_token']) . '" onclick="return confirm(\'Confirmați?\')">Șterge</a>';
        } else {
            echo 'Început deja';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

echo "</div></div>";

// Auto-fill and validate start/end date fields for leave forms
echo '<script>\ndocument.addEventListener("DOMContentLoaded", function() {\n  function enforceDateLogic(startEl, endEl) {\n    if (!startEl || !endEl) return;\n    function syncStartToEnd() {\n      try { endEl.min = startEl.value; } catch(e) {}\n      if (!endEl.value || endEl.value < startEl.value) {\n        endEl.value = startEl.value;\n        try { endEl.focus(); if (typeof endEl.showPicker === \'function\') endEl.showPicker(); } catch(e) {}\n      }\n    }\n    startEl.addEventListener("input", syncStartToEnd);\n    startEl.addEventListener("change", syncStartToEnd);\n    startEl.addEventListener("blur", syncStartToEnd);\n    endEl.addEventListener("change", function() { if (endEl.value < startEl.value) endEl.value = startEl.value; });\n    var form = startEl.form || endEl.form;\n    if (form) {\n      form.addEventListener("submit", function(e) {\n        if (!startEl.value || !endEl.value || endEl.value < startEl.value) {\n          e.preventDefault();\n          alert("Data de sfârșit nu poate fi anterioară datei de început.");\n          endEl.focus();\n        }\n      });\n    }\n    if (startEl.value) syncStartToEnd();\n  }\n\n  function attachAll() {\n    document.querySelectorAll("input[name=\"concediu_data_inceput\"]").forEach(function(startEl) {\n      var form = startEl.form;\n      var endEl = form ? form.querySelector("input[name=\"concediu_data_sfarsit\"]") : null;\n      if (endEl && !startEl._dateLogicAttached) { enforceDateLogic(startEl, endEl); startEl._dateLogicAttached = true; }\n    });\n  }\n\n  attachAll();\n  var mo = new MutationObserver(function() { attachAll(); });\n  mo.observe(document.body, { childList: true, subtree: true });\n});\n</script>';

// Safari/Edge fallback: delegated change/input handler to copy start -> end after picker selection
echo '<script>document.addEventListener("change", function(e){var t=e.target;if(!t||!t.matches||!t.matches("input[name=\"concediu_data_inceput\"]")) return;var start=t;var form=start.form;var end=form?form.querySelector("input[name=\"concediu_data_sfarsit\"]"):null;if(end){setTimeout(function(){try{end.min=start.value;}catch(e){} if(!end.value||end.value<start.value) end.value=start.value;},80);}});document.addEventListener("input", function(e){var t=e.target;if(!t||!t.matches||!t.matches("input[name=\"concediu_data_inceput\"]")) return;var start=t;var form=start.form;var end=form?form.querySelector("input[name=\"concediu_data_sfarsit\"]"):null;if(end){try{end.min=start.value;}catch(e){} if(!end.value||end.value<start.value) end.value=start.value;}});</script>';

include '../bottom.php';
?>