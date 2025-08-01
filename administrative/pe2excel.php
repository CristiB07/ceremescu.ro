<?php
//update 29.07.2025
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
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
include '../settings.php';

include '../classes/common.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

if ((isset( $_POST['month'])) && !empty( $_POST['month'])){
$month=$_POST['month'];}
Else
{echo "<div class=\"callout alert\">$strThereWasAnError</div>";
die;
}
if ((isset( $_POST['year'])) && !empty( $_POST['year'])){
$year=$_POST['year'];}
Else
{echo "<div class=\"callout alert\">$strThereWasAnError</div>";
die;}

$sqlu=" SELECT * from date_utilizatori Where utilizator_ID='$uid'";
$resultu=ezpub_query($conn,$sqlu);
$rowu = ezpub_fetch_array($resultu);
$User=$rowu["utilizator_Email"];
$Pass=$rowu["utilizator_Parola"];
$Nume=$rowu["utilizator_Prenume"] ." ". $rowu["utilizator_Nume"];
    		//Create an option With the numeric value of the month
			
			$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);

$query="SELECT * from administrative_deconturi where decont_user='$code' and Year(decont_luna)='$year' and Month(decont_luna)='$month' Order By decont_data ASC";
$result=ezpub_query($conn,$query);

$fp = fopen($hddpath .'/' . $pe_folder .'/decont_'.$code.'_'.$month.'_'.$year.'.xml', "w");
$header="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?mso-application progid=\"Excel.Sheet\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"https://www.w3.org/TR/html401/\">
	<ss:Styles>
		<ss:Style ss:ID=\"A\">
			<ss:Font ss:FontName=\"Open Sans\" ss:Size=\"12\" ss:Color=\"Red\"  /> 
		</ss:Style>
	</ss:Styles>
	<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">
        <Author>". $Nume . "</Author>
        <LastAuthor>". $Nume. "</LastAuthor>
        <Created>". date("d-m-Y H:i:s")."</Created>
        <Version>15.00</Version>
    </DocumentProperties>
<Worksheet ss:Name=\"Decont " . $monthname ." \">
<Table>
<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>
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
while($row = ezpub_fetch_array($result))
{
$schema_insert = "";
$schema_insert .= "<Row>";
$schema_insert.="<Cell><Data ss:Type=\"String\">". $row["decont_descriere"] . "</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">". $row["decont_document"] . "</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">".date("d.m.Y",strtotime($row["decont_data"])) . "</Data></Cell>";
if ($row["decont_achitat_card"]==0) {
$schema_insert.="<Cell><Data ss:Type=\"String\">Da</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">".romanize($row["decont_suma"]) . "</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">0</Data></Cell>";
}
ElseIf ($row["decont_achitat_card"]==1)
{
$schema_insert.="<Cell><Data ss:Type=\"String\">Nu</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">".romanize($row["decont_suma"]) . "</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">".romanize($row["decont_suma"]) . "</Data></Cell>";
}
Elseif ($row["decont_achitat_card"]==3) {
$schema_insert.="<Cell><Data ss:Type=\"String\">Da - card benzină</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">".romanize($row["decont_suma"]) . "</Data></Cell>";
$schema_insert.="<Cell><Data ss:Type=\"String\">0</Data></Cell>";
}
$schema_insert.="</Row>";

fwrite($fp, $schema_insert);
}
$sql2=" SELECT sum(decont_suma) as rest from administrative_deconturi where decont_user='$code' and Year(decont_luna)='$year' and Month(decont_luna)='$month' AND decont_achitat_card=1";
$result2=ezpub_query($conn,$sql2);
$row2 = ezpub_fetch_array($result2);
$rest=$row2["rest"];
$sql3=" SELECT sum(decont_suma) as total from administrative_deconturi where decont_user='$code' and Year(decont_luna)='$year' and Month(decont_luna)='$month'";
$result3=ezpub_query($conn,$sql3);
$row3 = ezpub_fetch_array($result3);
$total=$row3["total"];

$schema_total = "<Row>";
$schema_total.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
$schema_total.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
$schema_total.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
$schema_total.="<Cell><Data ss:Type=\"String\"></Data></Cell>";
$schema_total.="<Cell><Data ss:Type=\"String\">".romanize($total) . "</Data></Cell>";
$schema_total.="<Cell><Data ss:Type=\"String\">".romanize($rest) . "</Data></Cell>";
$schema_total.="</Row>
</Table>
</Worksheet>
</Workbook>";

fwrite($fp, $schema_total);

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
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . "<p>Bună ziua,</p>";
$emailbody=$emailbody . "<p>Atașat este decontul pentru luna ". $monthname.",</p>";
$emailbody=$emailbody . "<p>Mulțumesc,<br />". $Nume."</p>";
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
$mail->Subject = 'Decont '.$Nume.' luna '.$monthname;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține decontul. Mulțumesc, '. $Nume;
//Attach a file
$mail->addAttachment($hddpath .'/' . $pe_folder .'/decont_'.$code.'_'.$month.'_'.$year.'.xml');

//send the message, check for errors
if (!$mail->send()) {
    echo '<div class=\"callout alert\">Mailer Error: ' . $mail->ErrorInfo . '</div>';
	header("location:$strSiteURL". "/administrative/personalexpenses.php?message=Error");
} 
else
	{
    echo "<div class=\"callout success\">" . $strMessageSent ." ". $Nume ." ". $User . "</div>";
	header("location:$strSiteURL". "/administrative/personalexpenses.php?message=Success");
}
?>