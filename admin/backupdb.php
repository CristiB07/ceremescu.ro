<?php 
//updated 29.07.2025
include '../settings.php';
include '../classes/common.php';
header('P3P: CP="CAO PSA OUR"');
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
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
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css"/>

<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
 </head>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
# get all tables
$result = ezpub_query($conn, "SHOW TABLES");
$tables = array();

while ($row = ezpub_fetch_row($result)) {
    $tables[] = $row[0];
}

# Get tables data 
$sqlScript = "";
foreach ($tables as $table) {
    $query = "SHOW CREATE TABLE $table";
    $result = ezpub_query($conn, $query);
    $row = ezpub_fetch_row($result);
     
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";
     
     
    $query = "SELECT * FROM $table";
    $result = ezpub_query($conn, $query);
     
    $columnCount = ezpub_num_fields($result);
     
    for ($i = 0; $i < $columnCount; $i ++) {
        while ($row = ezpub_fetch_row($result)) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $columnCount; $j ++) {
                $row[$j] = $row[$j];
                 
                $sqlScript .= (isset($row[$j])) ? '"' . $row[$j] . '"' : '""';

                if ($j < ($columnCount - 1)) {
                    $sqlScript .= ',';
                }

            }
            $sqlScript .= ");\n";
        }
    }
     
    $sqlScript .= "\n"; 
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

$sqlu=" SELECT * from date_utilizatori Where utilizator_ID='$uid'";
$resultu=ezpub_query($conn,$sqlu);
$rowu = ezpub_fetch_array($resultu);
$User=$rowu["utilizator_Email"];
$Pass=$rowu["utilizator_Parola"];
$Nume=$rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];

// Delete backups older than 7 days
$deleted = delete_older_than($dir, 3600*24*7);

$txt = "A fost creat fișerul $archivename.zip. Au fost șterse " . count($deleted) . " fișiere:\n" .
    implode("\n", $deleted);

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


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
$mail->Subject = 'Backup bază de date CRM';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține backupul bazei de date, '. $db_name;
//Attach an image file
$mail->addAttachment($hddpath ."/" . $dbbackup_folder ."/".$archivename.'.zip');
//send the message, check for errors
if (!$mail->send()) {
    echo '<div class=\"callout alert\">Backup finalizat. $txt. Mailer Error: ' . $mail->ErrorInfo . '</div>';
} else {
    echo "<div class=\"callout success\">Backup finalizat. $txt. " . $strMessageSent ." ". $Nume ." ". $User . "</div>";
}
?>