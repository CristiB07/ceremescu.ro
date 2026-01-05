<?php
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
	if (!isSet($_SESSION['lang'])) {
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
	header("location:$strSiteURL/login/login.php?message=MLF");
	exit();
}

include '../settings.php';

include '../classes/common.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

// Validate month input
if (!isset($_POST['month']) || !is_numeric($_POST['month'])) {
    header("location:$strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
$month = intval($_POST['month']);
if ($month < 1 || $month > 12) {
    header("location:$strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
if ($month < 10) {
    $month = "0" . $month;
}

// Validate year input
if (!isset($_POST['year']) || !is_numeric($_POST['year'])) {
    header("location:$strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
$year = intval($_POST['year']);
if ($year < 2000 || $year > 2100) {
    header("location:$strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}

// Get user data with prepared statement
$stmt_user = $conn->prepare("SELECT * FROM date_utilizatori WHERE utilizator_ID=?");
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$rowu = $result_user->fetch_assoc();
$stmt_user->close();

if (!$rowu) {
    header("location:$strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
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

$Nume = $rowu["utilizator_Prenume"] . " " . $rowu["utilizator_Nume"];
    		//Create an option With the numeric value of the month
			
			$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
// count days and working days
$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$dd=0;
for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
	 $dd=$dd;
 }
 Else
 {
	 		$dd=$dd+1;
 }
 }


$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);

//

$fp = fopen($hddpath .'/' . $worksheets_folder .'/pontaj_'.$code.'_'.$month.'_'.$year.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?mso-application progid=\"Excel.Sheet\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"https://www.w3.org/TR/html401/\">
	<ss:Styles>
		<ss:Style ss:ID=\"A\">
			<ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\"  /> 
		</ss:Style>
	</ss:Styles>
	<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
        <Author>" . htmlspecialchars($Nume, ENT_XML1, 'UTF-8') . "</Author>
        <LastAuthor>" . htmlspecialchars($Nume, ENT_XML1, 'UTF-8') . "</LastAuthor>
        <Created>" . htmlspecialchars(date("d-m-Y H:i:s"), ENT_XML1, 'UTF-8') . "</Created>
        <Version>15.00</Version>
    </DocumentProperties>
<Worksheet ss:Name=\"Pontaj " . htmlspecialchars($monthname, ENT_XML1, 'UTF-8') . " \">
<Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
<Row>
<Cell><Data ss:Type=\"String\">Luna</Data></Cell>
<Cell><Data ss:Type=\"String\">Zile calendaristice</Data></Cell>
<Cell><Data ss:Type=\"String\">Zile lucrătoare</Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
</Row>
<Row>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($monthname, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($d, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($dd, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
</Row>
<Row>
<Cell><Data ss:Type=\"String\">Ziua din lună</Data></Cell>
<Cell><Data ss:Type=\"String\">Este lucrătoare</Data></Cell>
<Cell><Data ss:Type=\"String\">Liber/Concediu</Data></Cell>
<Cell><Data ss:Type=\"String\">Ore WFH</Data></Cell>
<Cell><Data ss:Type=\"String\">Ore teren</Data></Cell>
<Cell><Data ss:Type=\"String\">Ore birou</Data></Cell>
<Cell><Data ss:Type=\"String\">Altele</Data></Cell>
<Cell><Data ss:Type=\"String\">Observații</Data></Cell>
</Row>";
echo $header;
fwrite($fp, $header);
//end of adding column names
//start while loop to get data
for ( $i = 1; $i <= $d; $i ++) {
 $monthday = $i;
 $dayofmonth = $year . "-" . $month . "-" . $i;
 $namedayofthemonth = date('D', strtotime($dayofmonth));
 
 // Prepare statement for daily records
 $stmt_day = $conn->prepare("SELECT * FROM administrative_pontaje WHERE pontaj_user=? AND pontaj_an=? AND pontaj_luna=? AND pontaj_zi=?");
 $stmt_day->bind_param("sssi", $code, $year, $month, $i);
 $stmt_day->execute();
 $result_day = $stmt_day->get_result();
 $row = $result_day->fetch_assoc();
 $stmt_day->close();
 
 $schema_insert = "";
 $schema_insert .= "<Row>";
 $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($i, ENT_XML1, 'UTF-8') . "</Data></Cell>";
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">Zi nelucrătoare</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">-</Data></Cell>";
 }
 Else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">Zi lucrătoare</Data></Cell>";
	 $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_CO"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
  $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_ore_WFH"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
  $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_ore_T"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
  $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_ore_B"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
  $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_ore_A"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
  $schema_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["pontaj_observatii"] ?? '', ENT_XML1, 'UTF-8') . "</Data></Cell>";
 }
 $schema_insert .= "</Row>";
 fwrite($fp, $schema_insert);
}
$schema_close = "";
$schema_close.="</Table>
</Worksheet>
</Workbook>";
echo $schema_close;
fwrite($fp, $schema_close);

fclose($fp);

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


$emailbody = "<html>";
$emailbody .= "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody .= "<link href='" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody .= "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; COLOR: " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . "}";
$emailbody .= "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}";
$emailbody .= "td {font-size: 1em; font-family: 'Open Sans',sans-serif; COLOR: " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . "; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}";
$emailbody .= "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";  font-weight: normal;}";
$emailbody .= "table {border-collapse:collapse; border: 1px solid " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";}";
$emailbody .= "</style>";
$emailbody .= "</head><body>";
$emailbody .= "<a href=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "\"><img src=\"" . htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8') . "/img/logo.png\" title=\"" . htmlspecialchars($strSiteOwner, ENT_QUOTES, 'UTF-8') . "\" width=\"150\" height=\"auto\"/></a>";
$emailbody .= "<p>Bună ziua,</p>";
$emailbody .= "<p>Atașat este pontajul pentru luna " . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . ",</p>";
$emailbody .= "<p>Mulțumesc,<br />" . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . ",</p>";
$emailbody .= "</body>";
$emailbody .= "</html>";

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
$mail->Subject = 'Pontaj ' . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . ' luna ' . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8');
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține pontajul. Mulțumesc, ' . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8');
//Attach an image file
$mail->addAttachment($hddpath .'/' . $worksheets_folder .'/pontaj_'.$code.'_'.$month.'_'.$year.'.xml');

//send the message, check for errors
if (!$mail->send()) {
    echo '<div class="callout alert">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
	header("location:$strSiteURL" . "/administrative/personalworkingdays.php?message=Error");
	exit();
} else {
    echo "<div class=\"callout success\">" . htmlspecialchars($strMessageSent, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($User, ENT_QUOTES, 'UTF-8') . "</div>";
	header("location:$strSiteURL" . "/administrative/personalworkingdays.php?message=Success");
	exit();
}

?>