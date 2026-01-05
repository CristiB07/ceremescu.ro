<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$strPageTitle="Administrare facturi abonamente";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$day = date('d');
$year = date('Y');
$month = date('m');
$dataemiterii=$year."-".$month."-".$day;

$data = date('Y-m-d', strtotime($dataemiterii));

 $sql="SELECT * FROM curs_valutar WHERE curs_valutar_zi='$data'";
	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
	$curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
	 $cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$mSQL = "INSERT INTO curs_valutar(";
	$mSQL = $mSQL . "curs_valutar_zi,";
	$mSQL = $mSQL . "curs_valutar_valoare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$data . "', ";
	$mSQL = $mSQL . "'" .$cursvalutar . "') ";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn,$query));
  }
	}
	else
	{
		 $cursvalutar=$rss["curs_valutar_valoare"];
	}	

//

?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
//check_inject();
// create fixed elements
$dataemiterii = $_POST["strEData3"] ."-". $_POST["strEData2"] ."-". $_POST["strEData1"] ."";

$data = date('Y-m-d', strtotime($dataemiterii));

 $sql="SELECT * FROM curs_valutar WHERE curs_valutar_zi='$data'";
	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
		
	 $curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
	 $cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$mSQL = "INSERT INTO curs_valutar(";
	$mSQL = $mSQL . "curs_valutar_zi,";
	$mSQL = $mSQL . "curs_valutar_valoare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$data . "', ";
	$mSQL = $mSQL . "'" .$cursvalutar . "') ";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn,$query));
  }
	}
	else
	{
		 $cursvalutar=$rss["curs_valutar_valoare"];
	}	

//
$closed=1;
$anulat=0;
$factura_client_tip_activitate='M';
///
if(!empty($_POST['invoice'])){
// Loop to store and display values of individual checked checkbox.

foreach($_POST['invoice'] as $selected){
	// Sanitizare input - validare că $selected este numeric
	$selected = filter_var($selected, FILTER_VALIDATE_INT);
	if ($selected === false || $selected <= 0) {
		echo '<div class="callout alert">ID client invalid: ' . htmlspecialchars($_POST['invoice'][$selected] ?? 'unknown') . '</div>';
		continue; // Sari peste această iterație
	}
	
	//set invoice number
		$query2="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
$result2=ezpub_query($conn,$query2);
$row2=ezpub_fetch_array($result2);
If (!isSet($row2["factura_numar"]))
{$numarfactura=1;}
else
{$numarfactura=(int)$row2["factura_numar"]+1;}

//gather data
$stmt = mysqli_prepare($conn, "SELECT distinct clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_CUI, clienti_date.Client_RO, clienti_date.Client_CIF, clienti_date.Client_Adresa, clienti_date.Client_Banca, clienti_date.Client_IBAN, 
clienti_date.Client_Judet, clienti_date.Client_Localitate, clienti_date.Client_RC,
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_client_contract, clienti_abonamente.abonament_client_sales, clienti_abonamente.abonament_client_unitate, clienti_abonamente.abonament_client_email, 
clienti_abonamente.abonament_client_zifacturare, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_termen, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_an, clienti_abonamente.abonament_client_frecventa, 
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, clienti_abonamente.abonament_client_BU, clienti_abonamente.abonament_client_anexa, clienti_abonamente.abonament_client_pdf
FROM clienti_abonamente, clienti_date
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND abonament_client_frecventa<>3 AND abonament_client_activ=0 AND abonament_client_frecventa<>0 AND clienti_date.ID_Client=?");
mysqli_stmt_bind_param($stmt, "i", $selected);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row=ezpub_fetch_array($result);
$numar=ezpub_num_rows($result);
mysqli_stmt_close($stmt);
$anexa=$row["abonament_client_anexa"];
if ($anexa=='1')
{$file=$row["abonament_client_pdf"];}

$termenfactura = date('Y-m-d', strtotime($dataemiterii . ' +'.$row["abonament_client_termen"].' day'));


If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}
elseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
	$cefacturez=$_POST["trimestrul_facturarii"];
}
else
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}

$stmt1 = mysqli_prepare($conn, "SELECT abonament_client_valoare, abonament_client_valuta FROM clienti_abonamente WHERE abonament_client_ID=? AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0 AND abonament_client_activ=0");
mysqli_stmt_bind_param($stmt1, "i", $selected);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
	$valoaretotalafactura=0;
While ($row1=ezpub_fetch_array($result1)){
$valoareproduse=$row1["abonament_client_valoare"];
if ($row1["abonament_client_valuta"]==1)
{
	$pret=$valoareproduse*$cursvalutar;
}
else
{
	$pret=$valoareproduse;
}
If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
}
elseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
}
$valoare=$bucati*$pret;
$valoaretotalafactura=$valoaretotalafactura+$valoare;
}
mysqli_stmt_close($stmt1);

$articolTVA=$valoaretotalafactura*$vatrat;
$articoltotal=$valoaretotalafactura+$articolTVA;
$factura_tip_activitate="M";
$factura_tip="0";

// Prepared statement pentru INSERT factură
$stmt_factura = mysqli_prepare($conn, "INSERT INTO facturare_facturi(
	factura_numar, factura_data_emiterii, factura_client_ID, factura_client_denumire, 
	factura_client_CUI, factura_client_RO, factura_client_CIF, factura_client_RC, 
	factura_cod_factura, factura_client_adresa, factura_client_termen, 
	factura_client_valoare_totala, factura_client_valoare, factura_client_valoare_tva, 
	factura_client_alocat, factura_client_achitat, factura_client_tip_activitate, 
	factura_tip, factura_client_judet, factura_client_localitate, 
	factura_client_inchisa, factura_client_anulat, factura_client_curs_valutar, 
	factura_client_contract, factura_client_IBAN, factura_client_BU, 
	factura_client_sales, factura_client_an, factura_client_banca)
VALUES(?, ?, ?, ?, ?, ?, ?, ?, '380', ?, ?, ?, ?, ?, ?, '0', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt_factura, "isisssssssdddsssssiidsssiss", 
	$numarfactura, 
	$dataemiterii, 
	$row["ID_Client"], 
	$row["Client_Denumire"], 
	$row["Client_CUI"], 
	$row["Client_RO"], 
	$row["Client_CIF"], 
	$row["Client_RC"], 
	$row["Client_Adresa"], 
	$termenfactura, 
	$articoltotal, 
	$valoaretotalafactura, 
	$articolTVA, 
	$row["abonament_client_aloc"], 
	$factura_tip_activitate, 
	$factura_tip, 
	$row["Client_Judet"], 
	$row["Client_Localitate"], 
	$closed, 
	$anulat, 
	$cursvalutar, 
	$row["abonament_client_contract"], 
	$row["Client_IBAN"], 
	$row["abonament_client_BU"], 
	$row["abonament_client_sales"], 
	$row["abonament_client_an"], 
	$row["Client_Banca"]
);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt_factura))
  {
  die('Error: ' . mysqli_stmt_error($stmt_factura));
  }
else{
$clientID=$row["ID_Client"];
$clientemail=$row["abonament_client_email"];
$invoiceID=mysqli_insert_id($conn);
mysqli_stmt_close($stmt_factura);

if ($numar>1) {
	$query2="SELECT clienti_abonamente.abonament_client_unitate, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_contract, clienti_abonamente.abonament_client_valoare, 
	clienti_abonamente.abonament_client_valuta, clienti_abonamente.abonament_client_frecventa
FROM clienti_abonamente
WHERE clienti_abonamente.abonament_client_ID=$selected AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0 AND abonament_client_activ=0";

$result2=ezpub_query($conn,$query2);

While ($row2=ezpub_fetch_array($result2)){
$valoareproduse=$row2["abonament_client_valoare"];
//insert invoice items
If ($row2["abonament_client_frecventa"]==1)
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
	$pretarticol=$valoareproduse;
}
elseIf ($row2["abonament_client_frecventa"]==2)
{
	$bucati=3;
	$cefacturez=$_POST["trimestrul_facturarii"];
}
else
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}
$descrierearticol=$row2["abonament_client_detalii"] ." - prestări servicii conform contract ". $row2["abonament_client_contract"] ." pentru ".$cefacturez;
if ($row2["abonament_client_valuta"]==1)
{
	$pret=$valoareproduse*$cursvalutar;
}
else
{
	$pret=$valoareproduse;
}
$valoare=$bucati*$pret;
$articolTVA=$valoare*$vatrat;
$articoltotal=$valoare+$articolTVA;

// Prepared statement pentru INSERT articol factură (multiple)
$stmt_articol = mysqli_prepare($conn, "INSERT INTO facturare_articole_facturi(
	factura_ID, articol_descriere, articol_unitate, articol_bucati, articol_pret, 
	articol_valoare, articol_procent_TVA, articol_total, articol_TVA)
VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt_articol, "issiidddd", 
	$invoiceID, $descrierearticol, $row2["abonament_client_unitate"], $bucati, 
	$pret, $valoare, $vatcote, $articoltotal, $articolTVA);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt_articol))
  {
  die('Error: ' . mysqli_stmt_error($stmt_articol));
  }
mysqli_stmt_close($stmt_articol);
}}
else
{
$descrierearticol=$row["abonament_client_detalii"] ." - prestări servicii conform contract ". $row["abonament_client_contract"] ." pentru ".$cefacturez;
//insert invoice items

// Prepared statement pentru INSERT articol factură (single)
$stmt_articol_single = mysqli_prepare($conn, "INSERT INTO facturare_articole_facturi(
	factura_ID, articol_descriere, articol_unitate, articol_bucati, articol_pret, 
	articol_valoare, articol_procent_TVA, articol_total, articol_TVA)
VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt_articol_single, "issiidddd", 
	$invoiceID, $descrierearticol, $row["abonament_client_unitate"], $bucati, 
	$pret, $valoare, $vatcote, $articoltotal, $articolTVA);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt_articol_single))
  {
  die('Error: ' . mysqli_stmt_error($stmt_articol_single));
  }
mysqli_stmt_close($stmt_articol_single);	
}//insert invoice items - end
  //
  //generate pdf
  
  require_once __DIR__ . '/../vendor/autoload.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/fonts',
    ]),
    'fontdata' => $fontData + [
        'OpenSans' => [
            'R' => 'OpenSans-Regular.ttf',
            'B' => 'OpenSans-Bold.ttf',
            'I' => 'OpenSans-Italic.ttf',
            'BI' => 'OpenSans-BoldItalic.ttf',
        ]
    ],
    'default_font' => 'OpenSans'
]);
$d = date("d-m-Y ");
$data = date("Y-m-d ");
$s = date('d-m-Y', strtotime($d . ' +10 day'));

$query22="SELECT * FROM facturare_facturi WHERE factura_ID='$invoiceID'";
$result22=ezpub_query($conn,$query22);
$row22=ezpub_fetch_array($result22);

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 80,
	'margin_bottom' => 60,
	'margin_header' => 10,
	'margin_footer' => 50,
	'showBarcodeNumbers' => true
]);
$mpdf->SetTitle($strInvoice . " ". $siteInvoicingCode . " Nr. 0000".$row22["factura_numar"]);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetSubject('Facturarea serviciilor ' . $siteCompanyLegalName );
$mpdf->SetKeywords('factură, factura, invoice');

$_GET['cID']= $invoiceID;
$_GET['type']= '1';
$_GET['option']= 'print';
include './invoicetemplate.php';

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $invoice_folder ."/Factura_". $siteInvoicingCode. $codenumarfactura .'.pdf','F');
$invoicename='Factura_'. $siteInvoicingCode. $codenumarfactura. '.pdf';

// Prepared statement pentru UPDATE factură PDF
$stmt_update_pdf = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_pdf='1', factura_client_pdf_generat=? WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_update_pdf, "si", $data, $invoiceID);

if (!mysqli_stmt_execute($stmt_update_pdf))
  {
  die('Error: ' . mysqli_stmt_error($stmt_update_pdf));
  }
mysqli_stmt_close($stmt_update_pdf);
 
 echo "<div class=\"callout success\">Factura_". $siteInvoicingCode. $codenumarfactura  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=1&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
 ///send email

$query33="SELECT SUM(factura_client_valoare_totala) AS valoare_sold FROM facturare_facturi WHERE factura_client_ID='$clientID' AND factura_client_achitat='0'";
$result33=ezpub_query($conn,$query33);
$row33=ezpub_fetch_array($result33);
$soldtotal=$row33["valoare_sold"];
$soldanterior=$soldtotal-$row22["factura_client_valoare_totala"];
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
$emailbody=$emailbody . "<p>Stimate client,</p>
<p>Acest e-mail este generat automat și conține factura emisă de $siteCompanyLegalName. </p>
<p>Facturile se încarcă în sistemul efactura, acesta este doar un mail de informare pentru a facilita vizualizarea datelor.</p>
<p>Daca doriți sa modificați adresa de e-mail pentru primirea facturilor sau pentru informații și sesizări privind serviciile noastre, vă rugam să folosiți adresa $siteCompanyEmail sau să ne apelați la $siteCompanyPhones.
</p>
<p><strong>Sumar factură:</strong></p>
<table align=\"center\" width=\"85%\">
<thead>
<tr><th>Valoare factură curentă</th><th>Scadență</th><th>Sold la data emiterii</th><th>Total de plătit</th></tr>
</thead>
<tr><td align=\"right\">". romanize($row22["factura_client_valoare_totala"]) ."</td><td align=\"right\">". date('d.m.Y',strtotime($row22["factura_client_termen"])). "</td><td align=\"right\">". romanize($soldanterior)."</td><td align=\"right\">".romanize($soldtotal)."</td></tr>
</table>
<p>Mulțumim,<br />
$strSiteOwner<br />
$iconFacebook &nbsp;&nbsp;&nbsp;
$iconLinkedin 
</p>";
$emailbody=$emailbody . "</body>";
$emailbody=$emailbody. "</html>";
/**
 * now sending the mail.
 */


require '../vendor/autoload.php';

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
$mail->Username = $SmtpUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpPass;
//Set who the message is to be sent from
$mail->setFrom($siteCompanyEmail, $strSiteOwner);
//Set an alternative reply-to address
$mail->addReplyTo($siteCompanyEmail, $strSiteOwner);
//Set who the message is to be sent to
$mail->ConfirmReadingTo = $siteCompanyEmail;

$mail->SMTPOptions = array(
    'ssl' => array(
    'verify_peer' => false,
    'verify_peer_name' => false,
    'allow_self_signed' => true
    )
);
//Set who the message is to be sent to
$emailto=str_replace(' ', '', $clientemail);
$array = explode(';', $emailto); //
foreach($array as $value) //loop over values
{
$mail->addAddress($value);
}
//var_dump(PHPMailer::validateAddress('$emailto'));
//Set the subject line
$mail->Subject = 'Factura ' . $strSiteOwner;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $emailbody;
    $mail->AltBody = 'Acest mail conține factura $siteOwner. Ea a fost emisă pe '. date("d.m.Y", strtotime($row22["factura_data_emiterii"])).' și este în valoare de '
	. romanize($row22["factura_client_valoare_totala"]) .' și are ca termen '. date('d.m.Y',strtotime($row22["factura_client_termen"])).'. La data emiteri facturii, soldul dumneavoastră este '. romanize($soldanterior).'. Mulțumim, $siteOwner.';
//Attach an image file
$codenumarfactura=str_pad($row22["factura_numar"], 8, '0', STR_PAD_LEFT);

$mail->addAttachment($hddpath ."/" . $invoice_folder ."/Factura_".$siteInvoicingCode. $codenumarfactura.'.pdf');

if ($anexa=='1')
{
	$mail->addAttachment($hddpath ."/" . $annexes_folder .'/' . $file);
}
//send the message, check for errors
if (!$mail->send()) {
    echo '<div class=\"callout alert\">Mailer Error: ' . $mail->ErrorInfo . '</div>';
} else {
      echo "<div class=\"callout success\">" . $strMessageSent ." ". $strTo ." ". $emailto . "</div>";
}
  
// Flush output pentru a afișa progresul în timp real
if (ob_get_level() > 0) {
    ob_flush();
}
flush();

// Pauză scurtă între facturi pentru a evita supraîncărcarea serverului
sleep(1);

 }/// Închide foreach($_POST['invoice'] as $selected) - linia 106
} // Închide else din if (!ezpub_query($conn,$mSQL)) - linia 248
} // Închide if(!empty($_POST['invoice'])) - linia 103
} // Închide if ($_SERVER['REQUEST_METHOD'] == 'POST') - linia 66
else {
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitebulkinvoices.php?id=1" class="button">1</a><a href="sitebulkinvoices.php?id=15"
                        class="button warning">15</a><a href="sitebulkinvoices.php"
                        class="button success"><?php echo $strAll?></a></p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <form method="post" id="users" Action="sitebulkinvoices.php">
                    <div class="grid-x grid-margin-x">
                        <div class="large-6 medium-6 small-6 cell">
                            <input name="luna_facturarii" type="text" placeholder=<?php echo $strInvoiceMonth ?>
                                value="" />
                        </div>
                        <div class="large-6 medium-6 small-6 cell">
                            <input name="trimestrul_facturarii" type="text" placeholder=<?php echo $strInvoiceQuarter ?>
                                value="" />
                        </div>
                    </div>

                    <div class="grid-x grid-padding-x ">
                        <div class="large-4 medium-4 cell">
                            <label> <?php echo $strDay?>
                                <select name="strEData1">
                                    <option value="00" selected>--</option>
                                    <?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		if ($day==$d){
    		echo "<option selected value=\"$d\">$d</option>";}
			else {echo "<option value=\"$d\">$d</option>";}
			} 
?>
                                </select> </label>
                        </div>
                        <div class="large-4 medium-4 cell">
                            <label> <?php echo $strMonth?>
                                <select name="strEData2">
                                    <option value="00" selected>--</option>
                                    <?php for ( $m = 1; $m <= 12; $m ++) {
    		
    		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			if ($month==$m){
    			echo "<option selected value=\"$m\">$monthname</option>";}
				else
				{echo "<option value=\"$m\">$monthname</option>";}
				} 
			?>
                                </select> </label>
                        </div>
                        <div class="large-4 medium-4 cell">
                            <label> <?php echo $strYear?>
                                <select name="strEData3">
                                    <option value="0000" selected>--</option>
                                    <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    			if ($year==$y){
    	echo "<option selected value=\"$y\">$y</option>";}
		else{
		echo "<option value=\"$y\">$y</option>";
		}
		} 
			?>
                                </select></label>
                        </div>
                    </div>
                    <?php
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, 
MIN(clienti_abonamente.abonament_client_zifacturare) as abonament_client_zifacturare, 
MIN(clienti_abonamente.abonament_client_detalii) as abonament_client_detalii, 
MIN(clienti_abonamente.abonament_client_frecventa) as abonament_client_frecventa,
MIN(clienti_abonamente.abonament_client_ID) as abonament_client_ID,
MIN(clienti_abonamente.abonament_client_email) as abonament_client_email
FROM clienti_abonamente, clienti_date
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND abonament_client_activ=0 AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0";
if ((isset( $_GET['id'])) && !empty( $_GET['id'])){
$id=$_GET['id'];
$query=$query . " AND abonament_client_zifacturare='$id'";}
$query=$query . " GROUP BY clienti_date.ID_Client, clienti_date.Client_Denumire ORDER BY clienti_date.Client_Denumire ASC";

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
	?>
                    <script>
                    function toggle(source) {
                        checkboxes = document.getElementsByName('invoice[]');
                        for (var i = 0, n = checkboxes.length; i < n; i++) {
                            checkboxes[i].checked = source.checked;
                        }
                    }
                    </script>
                    <table width="100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" onClick="toggle(this)" /> <?php echo $strSelect?></th>
                                <th><?php echo $strClient?></th>
                                <th><?php echo $strObject?></th>
                                <th>Email facturare</th>
                                <th><?php echo $strSum?></th>
                            </tr>
                        </thead>
                        <?php	While ($row=ezpub_fetch_array($result)){
	
If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
	}
elseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
	}
else
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}

$query1="SELECT abonament_client_valoare, abonament_client_valuta FROM clienti_abonamente WHERE abonament_client_ID=$row[abonament_client_ID] AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0 AND abonament_client_activ=0";
$result1=ezpub_query($conn,$query1);
	$valoaretotalafactura=0;
While ($row1=ezpub_fetch_array($result1)){
$valoareproduse=$row1["abonament_client_valoare"];
if ($row1["abonament_client_valuta"]==1)
{
	$pret=$valoareproduse*$cursvalutar;
}
else
{
	$pret=$valoareproduse;
}
$valoare=$bucati*$pret;
$valoaretotalafactura=$valoaretotalafactura+$valoare;

}

$articolTVA=$valoaretotalafactura*$vatrat;
$articoltotal=$valoaretotalafactura+$articolTVA;

    		echo"<tr>
			<td><input type=\"checkbox\" id=\"$row[ID_Client]\" name=\"invoice[]\" value=\"$row[ID_Client]\"></td>
			<td>$row[Client_Denumire]</td>
			<td>$row[abonament_client_detalii]</td>
			<td>$row[abonament_client_email]</td>
			<td align=\"right\">";
			echo romanize($articoltotal);
			echo "</td>
			</tr>";

}
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></tfoot></table>";
}

?>
            </div>
        </div>

        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" Value="<?php echo $strAdd?>"
                    name="Submit" class="button success">
            </div>
        </div>
        </form>
        <?php }?>
    </div>
</div>
<?php
include '../bottom.php';
?>