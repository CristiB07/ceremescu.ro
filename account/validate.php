<?php
//updated 15.05.2025
session_start(); 
include '../settings.php';
require_once '../classes/common.php';
$strPageTitle="Intrare cont";
if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
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
    <title><?php echo $strSiteName ?>: <?php echo $strPageTitle ?></title>
    <meta name="rating" content="General" />
    <meta name="author" content="Consaltis Consultanţă şi Audit" />
    <meta name="language" content="romanian, RO" />
    <meta name="revisit-after" content="7 days" />
    <meta name="robots" content="noindex">
    <meta http-equiv="expires" content="never" />
    <link rel="shortcut icon" type="image/favicon" href="<?php echo $strSiteURL ?>/favicon.ico" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Insert this within your head tag and after foundation.css -->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname?>.css" />
    <link rel="shortcut icon" type="image/favicon" href="favicon.ico" />

    <!-- IE Fix for HTML5 Tags -->
    <!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../vendor/autoload.php';
$success = false;
$myhash=$_POST['hash'];
if ($myhash != $_SESSION['_token']) {
	$csrf_error = "Invalid CSRF token";
	
}
else {
	$csrf_error = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($csrf_error)) {

// Validate and sanitize input
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("location:$strSiteURL/account/login.php?message=ER");
    exit();
}

// username and password sent from form
$myusername = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
$mypassword = $_POST['password'];

// Validate email format
if (!filter_var($myusername, FILTER_VALIDATE_EMAIL)) {
    header("location:$strSiteURL/account/login.php?message=ER");
    exit();
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_email=?");
$stmt->bind_param("s", $myusername);
$stmt->execute();
$result = $stmt->get_result();

// ezpub_num_row is counting table row
$count = $result->num_rows;
// If result matched $myusername and $mypassword, table row must be 1 row
if($count==1){
$row = $result->fetch_assoc();
if (password_verify(trim($mypassword), $row['account_password'])) {
  
  // Verificare dacă există redirect în GET sau POST
  $redirect = '';
  if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
      $redirect = $_GET['redirect'];
  } elseif (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
      $redirect = $_POST['redirect'];
  }
  
  // Dacă există redirect, autentificare directă fără validare
  if (!empty($redirect)) {
      // Validare și sanitizare redirect URL
      $redirect = filter_var($redirect, FILTER_SANITIZE_URL);
      
      // Verificare că redirectul este intern (nu permite URL-uri externe)
      $parsed_url = parse_url($redirect);
      if (isset($parsed_url['host']) && $parsed_url['host'] !== parse_url($strSiteURL, PHP_URL_HOST)) {
          // URL extern detectat, redirecționare la pagină sigură
          header("location:$strSiteURL/account/login.php?message=ER");
          exit();
      }
      
      // Generare sesiuni necesare
      $_SESSION['userlogedin'] = "Yes";
      $_SESSION['uid'] = $row['account_id'];
      $_SESSION['clearence'] = $row['account_role'];
      $_SESSION['function'] = $row['account_function'];
      $_SESSION['account_email'] = $row['account_email'];
      $_SESSION['account_name'] = $row['account_first_name'] . ' ' . $row['account_last_name'];
      
      // Redirecționare către pagina dorită
      $redirect_url = $strSiteURL . '/' . ltrim($redirect, '/');
      header("Location: " . $redirect_url);
      exit();
  }
  
  // Fluxul normal cu validare prin email
  // Use prepared statement
  $stmt_email = $conn->prepare("SELECT * FROM site_accounts WHERE account_email=?");
  $stmt_email->bind_param("s", $myusername);
  $stmt_email->execute();
  $emailquery = $stmt_email->get_result();
  $emailcount = $emailquery->num_rows;
    if ($emailcount==1) {
            $emailrow = $emailquery->fetch_assoc();
            $emailto = $emailrow['account_email'];
            $emailtoname = $emailrow['account_first_name'] . ' ' . $emailrow['account_last_name'];
            $reset_hash = bin2hex(random_bytes(16));
            $reset_expire = date("Y-m-d H:i:s", time() + 3600); // 1 hour from now
            $randnum = rand(1111111111,9999999999);
            $secret=generateRandomString(10);
            
            // Use prepared statement for update
            $stmt_update = $conn->prepare("UPDATE site_accounts SET account_reset_hash=?, account_secret=?, account_activation=?, account_reset_expire=? WHERE account_email=?");
            $stmt_update->bind_param("ssiss", $reset_hash, $secret, $randnum, $reset_expire, $myusername);
            $stmt_update->execute();
            $stmt_update->close();
            $emailbody = "";
            $emailbody="<html>";
            $emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
            $emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
            $emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
            $emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
            $emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
            $emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
            $emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
            $emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
            $emailbody=$emailbody . ".button {background-color: " . $color . "; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; }";
            $emailbody=$emailbody . "</style>";
            $emailbody=$emailbody . "</head><body>";
            $emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
            $emailbody=$emailbody . "<p>Stimate " .$emailrow["account_first_name"]. " ".$emailrow["account_last_name"]. ",<br>";
            $emailbody=$emailbody . "Ca urmare a solicitării făcute de dumneavoastră pe site-ul ". $siteCompanyWebsite." aveți mai jos codul pentru acces în aplicație. </p>";
            $emailbody=$emailbody . "<p><strong>Vă rugăm să accesați următorul link:</strong><br />
									<a href=\"" . $siteCompanyWebsite . "/account/validatelogin.php?hash=" . $secret . "&reset=" . $reset_hash . "\" class=\"button\">" . $strClickToAccess . "</a></p>
<p>Codul dumneavoastră de confirmare este: <strong>" . $randnum . "</strong></p>
<p>Acest cod este valabil o oră de la primirea acestui email.</p>
<p>Dacă nu ați solicitat accesul, vă rugăm să ignorați acest mesaj dar vă sfătuim să vă schimbați parola de acces.</p>
";
            $emailbody=$emailbody . "<p>
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";

            $emailbody=$emailbody . "</body>";
            $emailbody=$emailbody . "</html>";
            $body=$emailbody;


//Create a new PHPMailer instance
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = ON (for production use)
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
$mail->Username = $SmtpUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpPass;
//Set who the message is to be sent from
//Set who the message is to be sent from
$mail->setFrom($SmtpUser, $strSiteOwner);
//Set an alternative reply-to address
$mail->addReplyTo($SmtpUser, $strSiteOwner);
//Set who the message is to be sent to
$mail->addAddress($emailto, $emailtoname);
$mail->addAddress($SmtpUser, $strSiteOwner);
//Set the subject line
$mail->Subject = 'Solicitare acces cont ' . $siteCompanyWebsite;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//Attach an image file

//send the message, check for errors
if (!$mail->send()) {
    echo "<div class=\"callout alert\">Mailer Error: " . $mail->ErrorInfo ."</div>";
} else {
    echo "<div class=\"callout success\">".$strMessageSentSuccessfully."</div>";
}
$stmt_email->close();
}
}
else {
    $stmt->close();
    echo "No user match";
header("location:$strSiteURL". "/account/login.php?message=WP");
exit();
}}
else {
    $stmt->close();
    echo "No user match";
header("location:$strSiteURL". "/account/login.php?message=WP");
exit();
}}
else {
	//he just try to get here directly or something is wrong
header("location:$strSiteURL". "/account/login.php?message=ER");
exit();
}
include '../bottom.php';
?>