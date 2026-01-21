<?php 
//updated 29.07.2025
include '../settings.php';
include '../classes/common.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../vendor/autoload.php';

header('P3P: CP="CAO PSA OUR"');
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	exit();
}

// Check if user is admin
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] != 'ADMIN') {
	header("location:$strSiteURL/index.php?message=unauthorized");
	exit();
}

$uid = intval($_SESSION['uid']);
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
{
	include '../lang/language_EN.php';
}

?>
<!doctype html>

<head>
    <!--Start Header-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--Font Awsome-->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css" />

    <script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
    <style>
    #loadingOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    #loadingOverlay.active {
        display: flex;
    }
    .spinner {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .loading-text {
        color: white;
        margin-top: 20px;
        font-size: 18px;
        font-weight: bold;
    }
    </style>
</head>
<body>
<div id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Se creează backup-ul bazei de date...<br>Vă rugăm așteptați...</div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
$startTime = microtime(true);
// Show loading on page load since backup starts immediately
echo '<script>document.getElementById("loadingOverlay").classList.add("active");</script>';
flush();

# get all tables
$result = ezpub_query($conn, "SHOW TABLES");
$tables = array();

while ($row = ezpub_fetch_row($result)) {
    $tables[] = $row[0];
}

# Get tables data 
$sqlScript = "";
foreach ($tables as $table) {
    // Sanitize table name - allow only alphanumeric and underscore
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        continue; // Skip invalid table names
    }
    
    $query = "SHOW CREATE TABLE `$table`";
    $result = ezpub_query($conn, $query);
    $row = ezpub_fetch_row($result);
     
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";
     
     
    $query = "SELECT * FROM $table";
    $result = ezpub_query($conn, $query);
     
    $columnCount = ezpub_num_fields($result);
     
    while ($row = ezpub_fetch_row($result)) {
        $sqlScript .= "INSERT INTO `$table` VALUES(";
        for ($j = 0; $j < $columnCount; $j ++) {
            if (isset($row[$j])) {
                // Properly escape the data for SQL
                $sqlScript .= '"' . addslashes($row[$j]) . '"';
            } else {
                $sqlScript .= 'NULL';
            }

            if ($j < ($columnCount - 1)) {
                $sqlScript .= ',';
            }

        }
        $sqlScript .= ");\n";
    }
     
    $sqlScript .= "\n"; 
}

// Sanitize database name to prevent path traversal
$safe_db_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $db_name);
$filename = $safe_db_name . '_backup_' . time() . '.sql';

// Validate that paths don't contain traversal attempts
if (strpos($hddpath, '..') !== false || strpos($dbbackup_folder, '..') !== false) {
    die('Invalid path detected');
}

//save file
$filename=$db_name . '_backup_'.time().'.sql';
$mysql_file = fopen($hddpath ."/" . $dbbackup_folder ."/". $filename, 'w+');
fwrite($mysql_file ,$sqlScript );
fclose($mysql_file );
//echo $filename;
//$source='../dbbackup/'.$filename;

// gzCompressFile($source, $level = 9);

$archivename=$db_name . '_backup_'.time();
$zip = new ZipArchive;
    $zip->open($hddpath ."/" . $dbbackup_folder ."/".$archivename.".zip",ZipArchive::CREATE|ZipArchive::OVERWRITE);
    $zip->addFile($hddpath ."/" . $dbbackup_folder ."/".$filename, $filename);
    $zip->close();
    
unlink($hddpath ."/" . $dbbackup_folder ."/".$filename); 

$dir = $hddpath ."/" . $dbbackup_folder ."/";

$sqlu = "SELECT utilizator_Email, utilizator_Parola, utilizator_Prenume, utilizator_Nume, utilizator_Upgraded FROM date_utilizatori WHERE utilizator_ID = ?";
$stmt = $conn->prepare($sqlu);
$stmt->bind_param("i", $uid);
$stmt->execute();
$resultu = $stmt->get_result();
$rowu = $resultu->fetch_assoc();
$stmt->close();

if (!$rowu) {
	die('User not found');
}

$User = htmlspecialchars($rowu["utilizator_Email"], ENT_QUOTES, 'UTF-8');

// Decrypt password if encrypted
$Pass = $rowu["utilizator_Parola"];
if (intval($rowu['utilizator_Upgraded']) === 0) {
	try {
		$email_hash = hash('sha256', $rowu['utilizator_Email']);
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
				$ciphertext = substr($encrypted_data, 16);
				$decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
				
				if ($decrypted !== false) {
					$Pass = $decrypted;
				} else {
					error_log("Failed to decrypt password for user ID: " . $uid);
					$Pass = '';
				}
			} else {
				error_log("Encrypted data too short for user ID: " . $uid);
				$Pass = '';
			}
		} else {
			error_log("Encryption key not found for user ID: " . $uid);
			$Pass = '';
		}
	} catch (Exception $e) {
		error_log("Password decryption error: " . $e->getMessage());
		$Pass = '';
	}
}

$Nume = htmlspecialchars($rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"], ENT_QUOTES, 'UTF-8');

// Delete backups older than 7 days
$deleted = delete_older_than($dir, 3600*24*7);

$txt = "A fost creat fișerul $archivename.zip. Au fost șterse " . count($deleted) . " fișiere:\n" .
    implode("\n", $deleted);
$emailbody="<html>";
$emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; COLOR: " . $color ."}";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; COLOR: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "</style>";
$emailbody=$emailbody . "</head><body>";
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . "<p>Bună ziua,</p>";
$emailbody=$emailbody . "<p>Atașat este backupul bazei de date  ". $db_name.": ". $archivename.".zip.</p>";
$emailbody=$emailbody . "<p>Mulțumesc,<br />". $Nume.",</p>";

//Create a new PHPMailer instance
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
$mail->addAddress('cristian.banu@consaltis.ro', 'Cristian BANU');
$mail->SMTPOptions = array(
    'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
    )
);

//Set the subject line
$mail->Subject = 'Backup baza de date ' . $db_name;
//convert HTML into a basic plain-text alternative body
$mail->isHTML(true);                                  // Set email format to HTML
$mail->Body    = $emailbody;
$mail->AltBody = 'Acest mail conține backupul bazei de date, '. $db_name;
//Attach an image file
$mail->addAttachment($hddpath ."/" . $dbbackup_folder ."/".$archivename.'.zip');
//send the message, check for errors
if (!$mail->send()) {
    echo '<script>document.getElementById("loadingOverlay").classList.remove("active");</script>';
    echo '<div class="callout alert">Backup finalizat. ' . htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') . '. Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
} else {
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    echo '<script>document.getElementById("loadingOverlay").classList.remove("active");</script>';
    echo '<div class="callout success">Backup finalizat în ' . $duration . ' secunde. ' . htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') . '. ' . htmlspecialchars($strMessageSent, ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($User, ENT_QUOTES, 'UTF-8') . '</div>';
}
?>
    </div>
</div>
</body>
</html>