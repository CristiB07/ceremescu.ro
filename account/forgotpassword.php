   <?php
   session_start(); 
require_once '../settings.php';
require_once '../classes/common.php';
$strPageTitle="Am uitat parola";
include '../header.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require '../vendor/autoload.php';

if (empty($_SESSION['_token'])) {
  $_SESSION['_token'] = bin2hex(random_bytes(32));
$_SESSION["token_expire"] = time() + 1800; // 30 minutes = 1800 secs
}
$csrf_error = "";
$token = $_SESSION['_token'];
$token_expire = $_SESSION["token_expire"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    $myhash = isset($_POST['hash']) ? $_POST['hash'] : '';
    if ($myhash != $_SESSION['_token']) {
        $csrf_error = "Invalid CSRF token";
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Validate and sanitize email
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    $email = filter_var(trim($_POST['username']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $emailquery = $stmt->get_result();
    $emailcount = $emailquery->num_rows;
    if ($emailcount==1) {
            $emailrow = $emailquery->fetch_assoc();
            $emailto = $emailrow['account_email'];
            $emailtoname = htmlspecialchars($emailrow['account_first_name'] . ' ' . $emailrow['account_last_name'], ENT_QUOTES, 'UTF-8');
            $reset_hash = bin2hex(random_bytes(16));
            $reset_expire = date("Y-m-d H:i:s", time() + 3600); // 1 hour from now
            $randnum = rand(1111111111,9999999999);
            $secret=generateRandomString(10);
            
            // Use prepared statement for update
            $stmt_update = $conn->prepare("UPDATE site_accounts SET account_active='0', account_reset_hash=?, account_secret=?, account_activation=?, account_reset_expire=? WHERE account_email=?");
            $stmt_update->bind_param("ssiss", $reset_hash, $secret, $randnum, $reset_expire, $email);
            $stmt_update->execute();
            $stmt_update->close();
            $stmt->close();
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
            $emailbody=$emailbody . "<p>Stimate " .htmlspecialchars($emailrow["account_first_name"], ENT_QUOTES, 'UTF-8'). " ".htmlspecialchars($emailrow["account_last_name"], ENT_QUOTES, 'UTF-8'). ",<br>";
            $emailbody=$emailbody . "Ca urmare a solicitării făcute de dumneavoastră pe site-ul ". $siteCompanyWebsite." aveți mai jos instrucțiunile pentru resetarea parolei. </p>";
            $emailbody=$emailbody . "<p><strong>Vă rugăm să accesați următorul link:</strong><br />
<a href=\"" . $siteCompanyWebsite . "/account/changepassword.php?hash=" . $secret . "&reset=" . $reset_hash . "\" class=\"button\">" . $strClickToChange . "</a></p>
<p>Codul dumneavoastră de confirmare este: <strong>" . $randnum . "</strong></p>
<p>Acest cod este valabil o oră de la primirea acestui email.</p>
<p>Dacă nu ați solicitat schimbarea parolei, vă rugăm să ignorați acest mesaj.</p>
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
$mail->Subject = 'Solicitare schimbare parolă cont ' . $siteCompanyWebsite;
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
}
else {
    $stmt->close();
}

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"index.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>";

include '../bottom.php';
exit();

}// end post
else {
    ?><div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell text-center">
        <form method="POST" action="forgotpassword.php">
            <fieldset>
                <legend>
                    <h2><?php echo $strLoginForm ?> Account</h2>
                </legend>
                    <div class="grid-x grid-margin-x">
                    <div class="large-4 medium-4 small-4 cell">

                    </div>
                    <div class="large-4 medium-4 small-4 cell text-center">
                        <div class="callout secondary">
                            <label>
                                <h3><?php echo $strUserName ?></h3>
                                <input type="text" id="username" name="username"
                                    placeholder="<?php echo $strUserName ?>" />
                                <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                            </label>
                            <p><input type="submit" class="button" value="<?php echo $strRequestPassword ?>" /></p>
                            </p>
                        </div>
                    </div>
                    <div class="large-4 medium-4 small-4 cell">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
<?php
}
include '../bottom.php';
?>
