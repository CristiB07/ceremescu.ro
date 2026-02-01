<?php
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
	if (!isSet($_SESSION['lang'])) {
	$_SESSION['lang']="RO";
	$lang=$_SESSION['lang'];
}
else
{
	$lang=$_SESSION['lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
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
if ($month < 10) {
    $month = "0" . $month;
}

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
//
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

$carplate = $rowu["utilizator_Carplate"];
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
 else
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
// Use prepared statement
$stmt = $conn->prepare("SELECT SUM(fp_km) AS valoaretotala FROM administrative_foi_parcurs WHERE fp_luna=? AND fp_aloc=?");
$stmt->bind_param("ss", $month, $code);
$stmt->execute();
$result1 = $stmt->get_result();
$rs1 = $result1->fetch_assoc();
$stmt->close();
$kilometritotal = $rs1["valoaretotala"]; 
	
// Use prepared statement
$stmt = $conn->prepare("SELECT fp_km_init FROM administrative_foi_parcurs WHERE fp_luna=? AND fp_an=? AND fp_aloc=? ORDER BY fp_zi ASC LIMIT 1");
$stmt->bind_param("sis", $month, $year, $code);
$stmt->execute();
$result2 = $stmt->get_result();
$rs2 = $result2->fetch_assoc();
$stmt->close();
$kilometriinit = $rs2["fp_km_init"];
// Use prepared statement
$stmt = $conn->prepare("SELECT fp_km_final FROM administrative_foi_parcurs WHERE fp_luna=? AND fp_an=? AND fp_aloc=? ORDER BY fp_zi DESC LIMIT 1");
$stmt->bind_param("sis", $month, $year, $code);
$stmt->execute();
$result3 = $stmt->get_result();
$rs3 = $result3->fetch_assoc();
$stmt->close();
$kilometrifinal = $rs3["fp_km_final"]; 

$realmonth = intval($_POST['month']);

// Use prepared statement
$stmt = $conn->prepare("SELECT SUM(alimentare_litri) AS litri FROM administrative_alimentari WHERE MONTH(alimentare_data)=? AND YEAR(alimentare_data)=? AND alimentare_aloc=?");
$stmt->bind_param("iis", $realmonth, $year, $code);
$stmt->execute();
$result = $stmt->get_result();
$rs = $result->fetch_assoc();
$stmt->close();
$litri = romanize($rs["litri"]); 	
// Use prepared statement
$stmt = $conn->prepare("SELECT SUM(alimentare_valoare) AS costtotal FROM administrative_alimentari WHERE MONTH(alimentare_data)=? AND YEAR(alimentare_data)=? AND alimentare_aloc=?");
$stmt->bind_param("iis", $realmonth, $year, $code);
$stmt->execute();
$result6 = $stmt->get_result();
$rs6 = $result6->fetch_assoc();
$stmt->close();
$costtotal = romanize($rs6["costtotal"]); 	
	
$fp = fopen($hddpath .'/' . $carsheets_folder .'/foaie_parcurs_'.$code.'_'.$carplate.'_'.$month.'_'.$year.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?mso-application progid=\"Excel.Sheet\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"https://www.w3.org/TR/html401/\">
	<ss:Styles>
		<ss:Style ss:ID=\"A\">
			<ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\"  /> 
		</ss:Style>
	</ss:Styles>
	<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
        <Author>". htmlspecialchars($Nume, ENT_XML1, 'UTF-8') . "</Author>
        <LastAuthor>". htmlspecialchars($Nume, ENT_XML1, 'UTF-8'). "</LastAuthor>
        <Created>". date("d-m-Y H:i:s")."</Created>
        <Version>15.00</Version>
    </DocumentProperties>
<Worksheet ss:Name=\"Foaie de parcurs " . htmlspecialchars($monthname, ENT_XML1, 'UTF-8') ." \">
<Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
<Row>
<Cell><Data ss:Type=\"String\">Luna</Data></Cell>
<Cell><Data ss:Type=\"String\">Zile calendaristice</Data></Cell>
<Cell><Data ss:Type=\"String\">Zile lucrătoare</Data></Cell>
<Cell><Data ss:Type=\"String\">Auto nr.</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri inițiali</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri finali</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri total</Data></Cell>
<Cell><Data ss:Type=\"String\">Litri</Data></Cell>
<Cell><Data ss:Type=\"String\">Cost total</Data></Cell>
</Row>
<Row>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($monthname, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($d, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($dd, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($carplate, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($kilometriinit, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($kilometrifinal, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($kilometritotal, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($litri, ENT_XML1, 'UTF-8') . "</Data></Cell>
<Cell><Data ss:Type=\"String\">" . htmlspecialchars($costtotal, ENT_XML1, 'UTF-8') . "</Data></Cell>
</Row>
<Row>
<Cell><Data ss:Type=\"String\">Data alimentării</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri la alimentare</Data></Cell>
<Cell><Data ss:Type=\"String\">Litri</Data></Cell>
<Cell><Data ss:Type=\"String\">Valoare</Data></Cell>
<Cell><Data ss:Type=\"String\">Achitat cu</Data></Cell>
<Cell><Data ss:Type=\"String\">Bon fiscal</Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
<Cell><Data ss:Type=\"String\"></Data></Cell>
</Row>
";
echo $header;
fwrite($fp, $header);
//adaugă alimentări

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM administrative_alimentari WHERE MONTH(alimentare_data)=? AND YEAR(alimentare_data)=? AND alimentare_aloc=?");
$stmt->bind_param("iis", $realmonth, $year, $code);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc())
{
$alimentare_insert = "";
$alimentare_insert .= "<Row>";	
$alimentare_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(date('d.m.Y',strtotime($row["alimentare_data"])), ENT_XML1, 'UTF-8') . "</Data></Cell>";
$alimentare_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["alimentare_km"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
$alimentare_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["alimentare_litri"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
$alimentare_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars(romanize($row["alimentare_valoare"]), ENT_XML1, 'UTF-8') . "</Data></Cell>";
If ($row["alimentare_platit"]==0)
{
	$alimentare_insert.="<Cell><Data ss:Type=\"String\">Achitat cardul firmei</Data></Cell>";
}
else
{
	$alimentare_insert.="<Cell><Data ss:Type=\"String\">Achitat cardul de benzină</Data></Cell>";
}
$alimentare_insert .= "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($row["alimentare_bf"], ENT_XML1, 'UTF-8') . "</Data></Cell>";
$alimentare_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$alimentare_insert .= "<Cell><Data ss:Type=\"String\"></Data></Cell>";
$alimentare_insert .= "</Row>";
fwrite($fp, $alimentare_insert);
}
$stmt->close();

//end of adding column names
//start while loop to get data
$fpheader="
<Row>
<Cell><Data ss:Type=\"String\">Ziua din lună</Data></Cell>
<Cell><Data ss:Type=\"String\">Este lucrătoare</Data></Cell>
<Cell><Data ss:Type=\"String\">Plecat</Data></Cell>
<Cell><Data ss:Type=\"String\">Sosit</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri inițiali</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri parcurși</Data></Cell>
<Cell><Data ss:Type=\"String\">Kilometri final</Data></Cell>
<Cell><Data ss:Type=\"String\">Observații</Data></Cell>
</Row>
";
echo $fpheader;
fwrite($fp, $fpheader);

for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 // Use prepared statement
 $stmt = $conn->prepare("SELECT * FROM administrative_foi_parcurs WHERE fp_aloc=? AND fp_an=? AND fp_luna=? AND fp_zi=?");
 $stmt->bind_param("sisi", $code, $year, $month, $i);
 $stmt->execute();
 $result = $stmt->get_result();
 $row = $result->fetch_assoc();
 $stmt->close();
 $schema_insert = "";
$schema_insert .= "<Row>";
$schema_insert.="<Cell><Data ss:Type=\"String\">". $i . "</Data></Cell>";
 
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
	 $schema_insert.="<Cell><Data ss:Type=\"String\">Zi nelucrătoare</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
	 $schema_insert.="<Cell><Data ss:Type=\"String\">-</Data></Cell>";
 }
 else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
	 $schema_insert.="<Cell><Data ss:Type=\"String\">Zi lucrătoare</Data></Cell>";
	  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_plecare"], ENT_XML1, 'UTF-8')."</Data></Cell>";
  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_sosire"], ENT_XML1, 'UTF-8')."</Data></Cell>";
  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_km_init"], ENT_XML1, 'UTF-8')."</Data></Cell>";
  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_km"], ENT_XML1, 'UTF-8')."</Data></Cell>";
  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_km_final"], ENT_XML1, 'UTF-8')."</Data></Cell>";
  $schema_insert.="<Cell><Data ss:Type=\"String\">".htmlspecialchars($row["fp_detalii"], ENT_XML1, 'UTF-8')."</Data></Cell>";
 }
$schema_insert.="</Row>";
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
$emailbody=$emailbody . "<a href=\"".htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8')."\"><img src=\"".htmlspecialchars($siteCompanyWebsite, ENT_QUOTES, 'UTF-8')."/img/logo.png\" title=\"".htmlspecialchars($strSiteOwner, ENT_QUOTES, 'UTF-8')."\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . "<p>Bună ziua,</p>";
$emailbody=$emailbody . "<p>Atașat este foaia de parcurs pentru luna  ". htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') .",</p>";
$emailbody=$emailbody . "<p>Mulțumesc,<br />". htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') .",</p>";
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
$mail->Subject = 'Foaie de parcurs '.htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8').' luna '.htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') .' - auto '.htmlspecialchars($carplate, ENT_QUOTES, 'UTF-8');
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține foaia de parcurs. Mulțumesc, '. $Nume;
//Attach a file
$mail->addAttachment($hddpath .'/' . $carsheets_folder .'/foaie_parcurs_'.$code.'_'.$carplate.'_'.$month.'_'.$year.'.xml');

//send the message, check for errors
if (!$mail->send()) {
    echo '<div class="callout alert">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES, 'UTF-8') . '</div>';
	header("location:$strSiteURL". "/administrative/personalcarsheets.php?message=Error");
	exit();
} else {
    echo "<div class=\"callout success\">" . $strMessageSent ." ". htmlspecialchars($Nume, ENT_QUOTES, 'UTF-8') ." ". htmlspecialchars($User, ENT_QUOTES, 'UTF-8') . "</div>";
	header("location:$strSiteURL". "/administrative/personalcarsheets.php?message=Success");
	exit();
}
?>