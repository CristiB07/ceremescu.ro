<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';
$url="/shop/";
$strKeywords="Comandă proceduri ";
$strDescription="Pagina finalizare a comenzii documente eProceduri";
$strPageTitle="Trimite comanda numărul " . $_GET['oID'];
include '../header.php';
if (!isSet($_SESSION['$buyer'])) {
header("location:$strSiteURL". "404.php");
}
Else {

$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";

$buyer=$_SESSION['$buyer'];
echo "<div class=\"row\">
<div class=\"large-12 columns\">";
echo "<h1>$strPageTitle</h1>";

If (isSet($_GET['oID']) AND is_numeric($_GET['oID'])) {
$oID=$_GET['oID'];}
Else{
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; 
include ('../bottom.php');
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
//new client
If (isSet($_GET['action']) AND $_GET['action']=="new") {
	$cui=$_POST["factura_client_RO"] . $_POST["factura_client_CIF"];

	$mSQL = "INSERT INTO magazin_firme(";
	$mSQL = $mSQL . "firma_nume,";
	$mSQL = $mSQL . "firma_ro,";
	$mSQL = $mSQL . "firma_CIF,";
	$mSQL = $mSQL . "firma_reg,";
	$mSQL = $mSQL . "firma_adresa,";
	$mSQL = $mSQL . "firma_oras,";
	$mSQL = $mSQL . "firma_judet,";
	$mSQL = $mSQL . "firma_banca,";
	$mSQL = $mSQL . "firma_IBAN)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_denumire"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_RO"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_CIF"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_RC"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$_POST["factura_client_adresa"]) . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_localitate"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_judet"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_banca"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_IBAN"] ."')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
//insert new user
$companyID=ezpub_inserted_id($conn);

		if ($_POST["factura_client_CIF"]<>'') {
		$studentinvoice=$_POST["factura_client_CIF"];}
		Else {
		$studentinvoice=0;
		}
	

	$mSQL = "INSERT INTO magazin_cumparatori(";
	$mSQL = $mSQL . "cumparator_prenume,";
	$mSQL = $mSQL . "cumparator_nume,";
	$mSQL = $mSQL . "cumparator_adresa,";
	$mSQL = $mSQL . "cumparator_email,";
	$mSQL = $mSQL . "cumparator_telefon,";
	$mSQL = $mSQL . "cumparator_oras,";
	$mSQL = $mSQL . "cumparator_judet,";
	$mSQL = $mSQL . "cumparator_factura,";
	$mSQL = $mSQL . "cumparator_firma)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["cumparator_prenume"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_nume"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_adresa"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_email"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_telefon"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_oras"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["cumparator_judet"] . "', ";
	$mSQL = $mSQL . "'" .$studentinvoice . "', ";
	$mSQL = $mSQL . "'" .$_POST["factura_client_CIF"] . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
	  echo "<div class=\"callout alert\">$strThereWasAnError. Error: " . ezpub_error($conn)." </div></div></div>"; 
include ('../bottom.php');
  die;
  }
Else{
$userID=ezpub_inserted_id($conn);}

$whereclause=" WHERE firma_ID=$companyID";
$updateq="UPDATE magazin_firme SET firma_cumparatorID='$userID' " . $whereclause;
ezpub_query($conn,$updateq);

$query="SELECT * FROM magazin_comenzi where comanda_ID=$oID";
$result=ezpub_query($conn,$query);

$whereclause=" WHERE comanda_ID=$oID";
$updateq="UPDATE magazin_comenzi SET comanda_status=1 " . $whereclause;
$updateq="UPDATE magazin_comenzi SET comanda_utilizator='$userID' " . $whereclause;
ezpub_query($conn,$updateq);
} //ends new client

Else //whatever is wrong
{
	header("location:$strSiteURL". "404.php");	
}

//Cumpărător
If ($_POST["factura_client_CIF"]=='') {
$cumparator="<h4>". $_POST["cumparator_nume"] . " ". $_POST["cumparator_prenume"] . "</h4><br />";
$cumparator=$cumparator . "<strong>Adresa:</strong> ". $_POST["cumparator_adresa"] . "<br />";
}
Else {
	
$cumparator="<h4>". $_POST["factura_client_denumire"] . "</h4><br />";
$cumparator=$cumparator . "CUI: ". $cui . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ". $_POST["factura_client_RC"] . "<br />";
$cumparator=$cumparator . "Adresa: ". $_POST["factura_client_adresa"] . "<br />";
$cumparator=$cumparator . "Localitatea: ". $_POST["factura_client_localitate"] . "<br />";
$cumparator=$cumparator . "Județ: ". $_POST["factura_client_judet"] . "<br />";
$cumparator=$cumparator . "Banca: ". $_POST["factura_client_banca"] . "<br />";
$cumparator=$cumparator . "IBAN: ". $_POST["factura_client_IBAN"] . "<br />";
}

$emailto=$_POST['cumparator_email'];
$emailtoname=$_POST["cumparator_prenume"]. " " . $_POST["cumparator_nume"];


$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$HTMLBody=$HTMLBody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$HTMLBody=$HTMLBody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";

$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"$siteCompanyWebsite/img/logo.png\" title=\"$strSiteName\" width=\"300px\" height=\"auto\"/></a>";
$HTMLBody=$HTMLBody . "<p>Stimate " . $_POST["cumparator_prenume"]. " " . $_POST["cumparator_nume"].  ",<br>";
$HTMLBody=$HTMLBody . "Acesta este un mesaj de confirmare a comenzii făcute de dumneavoastră pe site-ul $strSiteName. Mai jos aveți factura proforma. </p>";
$HTMLBody=$HTMLBody . "<H2>Factura proforma ".$siteInvoicingCode . $oID . "</H2>";
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\">";
$HTMLBody=$HTMLBody . "<tr valign=\"top\"><td width=\"50%\" valign=\"top\"><strong>Furnizor</strong>";
$HTMLBody=$HTMLBody . "<h4>$siteCompanyLegalName</h4>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
Tel.: $siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />";
foreach ($siteBankAccounts as $account) {
  $HTMLBody=$HTMLBody . "<font color=\"" . $color ."\">$account</font><br />";
}
$HTMLBody=$HTMLBody . "<h5>$siteVATStatus</h5> ";
$HTMLBody=$HTMLBody . "</td><td width=\"50%\" valign=\"top\"><strong>Cumpărător</strong>";
$HTMLBody=$HTMLBody .  $cumparator;
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<br/>";
$HTMLBody=$HTMLBody . "<table align=\"center\" width=\"100%\">";
$HTMLBody=$HTMLBody . "<thead><tr>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Produs";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Valoare";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Cantitate";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "Total";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "<th>";
$HTMLBody=$HTMLBody . "TVA";
$HTMLBody=$HTMLBody . "</th>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</thead>";

$itemq="SELECT * FROM magazin_articole where articol_idcomanda=$oID";
$resulti=ezpub_query($conn,$itemq);
$ordertotal=0;
While ($rowi=ezpub_fetch_array($resulti)) {
$queryp="SELECT * FROM magazin_produse WHERE produs_id='$rowi[articol_produs]'";
$resultp=ezpub_query($conn,$queryp);
$row=ezpub_fetch_array($resultp);
If ($row["produs_dpret"]!=='0.0000')
{
$unitprice=$row['produs_dpret'];
}
Else
{
	$unitprice=$row['produs_pret'];
}
$vatrat=$row["produs_tva"]/100;
$vatprc=$vatrat+1;
$quantity=$rowi['articol_cantitate'];
$totalprice=$unitprice*$quantity;
$ordertotal=$ordertotal+$totalprice;
$VAT=$totalprice*$vatrat;
$totalVAT=$ordertotal*$vatrat;
$finalprice=$ordertotal*$vatprc;
$HTMLBody=$HTMLBody . "<tr><td>$row[produs_nume]</td><td align=\"right\">".romanize($unitprice)."</td><td align=\"right\">$quantity</td><td align=\"right\">".romanize($totalprice)."</td><td>".romanize($VAT)."</td></tr>";
}
$totalinterim=$ordertotal*$vatprc;
$totalVAT=$ordertotal*$vatrat;
$totalorder=$ordertotal;
if ($paidtransport=="1" )
{
If ($totalinterim<=$transportlimit){
	$transportVAT=$transportprice*$transportvatrat;
$HTMLBody=$HTMLBody . "<tr><td colspan=\"3\">$strTransport</td><td align=\"right\">".romanize($transportprice)."</td><td>".romanize($transportVAT)."</td></tr>";
$totalorder=$ordertotal+$transportprice;
$orderVAT=$ordertotal*$vatrat;
$totalVAT=$orderVAT+$transportVAT;
}}
$finalprice=$totalorder+$totalVAT;
$HTMLBody=$HTMLBody . "<tr><td colspan=\"3\">$strTotal</td><td align=\"right\">".romanize($totalorder)."</td><td>".romanize($totalVAT)."</td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"3\">$strTotal</td><td colspan=\"2\" align=\"right\">".romanize($finalprice)."</td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<p>Produsele comandate vor fi livrate după confirmarea plății.</p> 
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";

$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$body=$HTMLBody;

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
$mail->Subject = 'Factura proforma '.$siteInvoicingCode . $oID ;
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
    echo "<div class=\"callout success\">".$strOrderSentSuccessfully."</div>";
}

$data= date('Y-m-d H:i:s');
$whereclause="WHERE comanda_ID='$oID';";
$updateq="UPDATE magazin_comenzi SET comanda_total='$ordertotal', comanda_status='1', comanda_inchisa='$data' " . $whereclause;
ezpub_query($conn,$updateq);
include '../bottom.php';
} // ends if post//

Else
{
header("location:$strSiteURL". "404.php");	
} //ends not post
} //ends if session
?>