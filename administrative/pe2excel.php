<?php
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
	if (!isSet($_SESSION['$lang'])) {
	$_SESSION['lang']="RO";
	$lang=$_SESSION['lang'];
}
Else
{
	$lang=$_SESSION['lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}	
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

include '../settings.php';

include '../classes/common.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

// Validate month parameter
if (!isset($_POST['month']) || !is_numeric($_POST['month']) || $_POST['month'] < 1 || $_POST['month'] > 12) {
    die('<div class="callout alert">Invalid month parameter</div>');
}

$month = intval($_POST['month']);

// Validate year parameter
if (!isset($_POST['year']) || !is_numeric($_POST['year']) || $_POST['year'] < 2000 || $_POST['year'] > 2100) {
    die('<div class="callout alert">Invalid year parameter</div>');
}

$year = intval($_POST['year']);

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM date_utilizatori WHERE utilizator_ID=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$resultu = $stmt->get_result();
$rowu = $resultu->fetch_assoc();
$stmt->close();

if (!$rowu) {
    die('<div class="callout alert">User not found</div>');
}

$User = $rowu["utilizator_Email"];
$Pass = $rowu["utilizator_Parola"];

// Decrypt password if encrypted
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
                $ciphertext = substr($encrypted_data, 16);
                $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
                
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

$Nume = $rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];
    		//Create an option With the numeric value of the month
			
			$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM administrative_deconturi WHERE decont_user=? AND YEAR(decont_luna)=? AND MONTH(decont_luna)=? ORDER BY decont_data ASC");
$stmt->bind_param("sii", $code, $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$fp = fopen($hddpath .'/' . $pe_folder .'/decont_'.$code.'_'.$month.'_'.$year.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?mso-application progid=\"Excel.Sheet\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
    xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"https://www.w3.org/TR/html401/\">
    <ss:Styles>
        <ss:Style ss:ID=\"A\">
            <ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\" />
        </ss:Style>
    </ss:Styles>
    <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
        <Author>". htmlspecialchars($Nume, ENT_XML1, 'UTF-8') . "</Author>
        <LastAuthor>". htmlspecialchars($Nume, ENT_XML1, 'UTF-8'). "</LastAuthor>
        <Created>". date("d-m-Y H:i:s")."</Created>
        <Version>15.00</Version>
    </DocumentProperties>
    <Worksheet ss:Name=\"Decont " . htmlspecialchars($monthname, ENT_XML1, 'UTF-8') ." \">
        <Table>
            <Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\" />
            <Row>
                <Cell><Data ss:Type=\"String\">Descriere</Data></Cell>
                <Cell><Data ss:Type=\"String\">Document</Data></Cell>
                <Cell><Data ss:Type=\"String\">Data document</Data></Cell>
                <Cell><Data ss:Type=\"String\">Achitat card</Data></Cell>
                <Cell><Data ss:Type=\"String\">Suma</Data></Cell>
                <Cell><Data ss:Type=\"String\">Rest</Data></Cell>
            </Row>";

            fwrite($fp, $header);
            //end of adding column names

            //start while loop to get data
            while($row = $result->fetch_assoc())
            {
            $schema_insert = "";
            $schema_insert .= "<Row>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["decont_descriere"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["decont_document"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(date("d.m.Y",strtotime($row["decont_data"])), ENT_XML1, 'UTF-8') ."</Data></Cell>";
                if ($row["decont_achitat_card"]==0) {
                $schema_insert .= "<Cell><Data ss:Type=\"String\">Da</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["decont_suma"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">0</Data></Cell>";
                }
                ElseIf ($row["decont_achitat_card"]==1)
                {
                $schema_insert .= "<Cell><Data ss:Type=\"String\">Nu</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["decont_suma"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["decont_suma"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                }
                Elseif ($row["decont_achitat_card"]==3) {
                $schema_insert .= "<Cell><Data ss:Type=\"String\">Da - card benzină</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["decont_suma"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_insert .= "<Cell><Data ss:Type=\"String\">0</Data></Cell>";
                }
                $schema_insert .= "</Row>";

            fwrite($fp, $schema_insert);
            }
            $stmt->close();
            
            $stmt_rest = $conn->prepare("SELECT SUM(decont_suma) as rest FROM administrative_deconturi WHERE decont_user=? AND YEAR(decont_luna)=? AND MONTH(decont_luna)=? AND decont_achitat_card=1");
            $stmt_rest->bind_param("sii", $code, $year, $month);
            $stmt_rest->execute();
            $result_rest = $stmt_rest->get_result();
            $row2 = $result_rest->fetch_assoc();
            $rest = $row2["rest"];
            $stmt_rest->close();
            
            $stmt_total = $conn->prepare("SELECT SUM(decont_suma) as total FROM administrative_deconturi WHERE decont_user=? AND YEAR(decont_luna)=? AND MONTH(decont_luna)=?");
            $stmt_total->bind_param("sii", $code, $year, $month);
            $stmt_total->execute();
            $result_total = $stmt_total->get_result();
            $row3 = $result_total->fetch_assoc();
            $total = $row3["total"];
            $stmt_total->close();

            $schema_total = "<Row>";
                $schema_total .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                $schema_total .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                $schema_total .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                $schema_total .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
                $schema_total .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($total), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_total .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($rest), ENT_XML1, 'UTF-8') . "</Data></Cell>";
                $schema_total .= "</Row>
        </Table>
    </Worksheet>
</Workbook>";

fwrite($fp, $schema_total);

fclose($fp);

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


$emailbody="<html>";
$emailbody=$emailbody . "

<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
    $emailbody=$emailbody . "
    <link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet'
        type='text/css'>";
    $emailbody=$emailbody . "
    <link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet'
        type='text/css'>";
    $emailbody=$emailbody . "<style>
    body {
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left: 10px;
        margin-right: 10px;
        font-size: 1.1em;
        font-family: 'Open Sans', sans-serif;
        padding: 0px;
        COLOR: " . $color ."
    }

    ";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: ". $color .";}";
    $emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; COLOR: ". $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid ". $color .";}";
    $emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: ". $color .";  font-weight: normal;}";
    $emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid ". $color .";}";
    $emailbody=$emailbody . "</style>";
    $emailbody=$emailbody . "</head><body>";
    $emailbody=$emailbody . "<a href=\"".htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8')."\"><img src=\"".htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8')."/img/logo.png\" title=\"".htmlspecialchars($strSiteOwner, ENT_QUOTES, 'UTF-8')."\" width=\"150\" height=\"auto\" /></a>";
    $emailbody=$emailbody . "<p>Bună ziua,</p>";
    $emailbody=$emailbody . "<p>Atașat este decontul pentru luna ". htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') .",</p>";
    $emailbody=$emailbody . "<p>Mulțumesc,<br />". htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') ."</p>";
    $emailbody=$emailbody . "</body>";
$emailbody=$emailbody. "</html>";

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
$mail->Subject = 'Decont '.htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8').' luna '.htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8');
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->isHTML(true); // Set email format to HTML
$mail->Body = $emailbody;
$mail->AltBody = 'Acest mail conține decontul. Mulțumesc, '. htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8');
//Attach a file
$mail->addAttachment($hddpath .'/' . $pe_folder .'/decont_'.$code.'_'.$month.'_'.$year.'.xml');

//send the message, check for errors
if (!$mail->send()) {
echo '<div class="callout alert">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
header("location:$strSiteURL". "/administrative/personalexpenses.php?message=Error");
exit();
}
else
{
echo "<div class=\"callout success\">" . $strMessageSent ." ". htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') ." ". htmlspecialchars($User, ENT_QUOTES, 'UTF-8') . "</div>";
header("location:$strSiteURL". "/administrative/personalexpenses.php?message=Success");
exit();
}

?>