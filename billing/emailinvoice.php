<?php
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
include '../classes/convertor.class.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

// Validare parametri
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    header("location:$strSiteURL/billing/siteinvoices.php?message=ER");
    die;
}
$cID = (int)$_GET['cID'];

$type = isset($_GET['type']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['type']) : '';
$option = isset($_GET['option']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['option']) : '';

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

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$strPageTitle="Trimitere email";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$day = date('d');
$year = date('Y');
$month = date('m');
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  $_REQUEST['cID'] = $cID;
  $_REQUEST['type'] = $type;
  $_REQUEST['option'] = $option;
include './invoicetemplate.php';
	
$stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
If ($row["factura_tip"]=='0')
{
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
}
else
{
	$codenumarfactura=str_pad($row["factura_numar"], 8, '1', STR_PAD_LEFT);
}
If ($row["factura_client_pdf"]=='')
{

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 80,
	'margin_bottom' => 60,
	'margin_header' => 10,
	'margin_footer' => 50,
	'showBarcodeNumbers' => true
]);

$mpdf->SetTitle($strInvoice . " ". $siteInvoicingCode . " Nr. ".$codenumarfactura);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetSubject('Facturarea serviciilor ' . $siteCompanyLegalName );
$mpdf->SetKeywords('factură, factura, invoice');
$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $invoice_folder ."/Factura_". $siteInvoicingCode. $codenumarfactura .'.pdf','F');
$invoicename='Factura_'. $siteInvoicingCode. $codenumarfactura. '.pdf';

$stmt_upd = mysqli_prepare($conn, "UPDATE facturare_facturi SET factura_client_pdf='1', factura_client_pdf_generat=? WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt_upd, "si", $data, $cID);

if (!mysqli_stmt_execute($stmt_upd))
  {
  die('Error: ' . mysqli_stmt_error($stmt_upd));
  }
mysqli_stmt_close($stmt_upd);
echo "<div class=\"callout success\">Factura_". $siteInvoicingCode. $codenumarfactura  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=1&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
}

$stmt2 = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt2, "i", $cID);
mysqli_stmt_execute($stmt2);
$result22 = mysqli_stmt_get_result($stmt2);
$row22 = mysqli_fetch_array($result22, MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);

$emailbody="<html>";
$emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "</style>";
$emailbody=$emailbody . "</head><body>";
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$email_body_safe = isset($_POST["email_body"]) ? $_POST["email_body"] : '';
$emailbody=$emailbody . $email_body_safe;
$emailbody=$emailbody . "</body>";
$emailbody=$emailbody . "</html>";

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
$emailto=str_replace(' ', '', $_POST["email_client"]);
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
    $mail->AltBody = 'Acest mail conține factura'. $strSiteOwner .'. Mulțumim,'. $strSiteOwner;
//Attach an image file
$mail->addAttachment($hddpath ."/" . $invoice_folder ."/Factura_".$siteInvoicingCode. $codenumarfactura.'.pdf');

$uploaddir = $hddpath ."/" . $upload_folder;
 
    // Count total files
    $countfiles = count($_FILES['file']['name']);

    // Looping all files
    for($i=0;$i<$countfiles;$i++){
        $filename = $_FILES['file']['name'][$i];
		        // Upload file
        move_uploaded_file($_FILES['file']['tmp_name'][$i],$uploaddir."/".$filename);
		$mail->addAttachment($uploaddir."/".$filename);
 
    }
	

//send the message, check for errors
if (!$mail->send()) {
    echo '<div class=\"callout alert\">Mailer Error: ' . $mail->ErrorInfo . '</div>';
} else {
    echo "<div class=\"callout success\">" . $strMessageSent ." ". $strTo ." ". $emailto . "</div>";
}
}
else
{
$stmt3 = mysqli_prepare($conn, "SELECT * FROM facturare_facturi WHERE factura_ID=?");
mysqli_stmt_bind_param($stmt3, "i", $cID);
mysqli_stmt_execute($stmt3);
$result22 = mysqli_stmt_get_result($stmt3);
$row22 = mysqli_fetch_array($result22, MYSQLI_ASSOC);
mysqli_stmt_close($stmt3);

$codenumarfactura=str_pad($row22["factura_numar"], 8, '0', STR_PAD_LEFT);

$factura_client_ID = (int)$row22['factura_client_ID'];
$stmt4 = mysqli_prepare($conn, "SELECT SUM(factura_client_valoare_totala) AS valoare_sold FROM facturare_facturi WHERE factura_client_ID=? AND factura_client_achitat='0'");
mysqli_stmt_bind_param($stmt4, "i", $factura_client_ID);
mysqli_stmt_execute($stmt4);
$result33 = mysqli_stmt_get_result($stmt4);
$row33 = mysqli_fetch_array($result33, MYSQLI_ASSOC);
mysqli_stmt_close($stmt4);
$soldtotal=$row33["valoare_sold"];
$soldanterior=$soldtotal-$row22["factura_client_valoare_totala"];
$clientID = (int)$row22["factura_client_ID"];
$stmt5 = mysqli_prepare($conn, "SELECT Contract_Email_Facturare FROM clienti_contracte WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt5, "i", $clientID);
mysqli_stmt_execute($stmt5);
$result4 = mysqli_stmt_get_result($stmt5);
$row4 = mysqli_fetch_array($result4, MYSQLI_ASSOC);
mysqli_stmt_close($stmt5);

if (empty( $row4['Contract_Email_Facturare']))
{
echo "<div class=\"callout alert\">".$strNoEmailAddressesFound."</div>";
$emailaddress='';
}
else {
$emailaddress=$row4['Contract_Email_Facturare'];
}
?>
        <h1><?php echo $strSendInvoice ?></h1>
        <form method="POST"
            action="emailinvoice.php?cID=<?php echo htmlspecialchars($cID, ENT_QUOTES, 'UTF-8')?>&type=<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8')?>&option=<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8')?>"
            enctype="multipart/form-data">
            <div class="grid-x grid-padding-x ">
                <div class="medium-6 cell">
                    <label><?php echo $strEmail ?>
                        <input type="text" id="email" name="email_client" value="<?php echo $emailaddress ?>" />
                    </label>
                </div>
                <div class="medium-6 cell">
                    <label><?php echo $strAttachement ?>
                        <input type="file" name="file[]" id="file" multiple>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strMessage?>
                        <textarea name="email_body" class="simple-html-editor" data-upload-dir="billing" rows="5">
	  <p>Stimate client,</p>
	<p>Acest mail conține factura <?php echo $strSiteOwner?>. Ea a fost emisă pe <?php echo date("d.m.Y", strtotime($row22["factura_data_emiterii"]))?> și este în valoare de <?php echo
	 romanize($row22["factura_client_valoare_totala"]) ?> și are ca termen <?php echo date('d.m.Y',strtotime($row22["factura_client_termen"]))?>. La data emiteri facturii, soldul dumneavoastră este <?php echo romanize($soldanterior)?>.</p> 
	<p>Daca doriți sa modificați adresa de e-mail pentru primirea facturilor sau pentru informații și sesizări privind serviciile noastre, vă rugam să folosiți adresa <?php echo $siteCompanyEmail?> sau să ne apelați la <?php echo $siteCompanyPhones?>.</p>
	<p>Mulțumim,<br />
<?php echo $strSiteOwner?></p>
	  </textarea></label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 cell"><input type="submit" value="<?php echo $strSend ?>" name="Submit"
                        class="button success" />
                </div>
            </div>
    </div>
    </form>
</div>
</div>
<?php 
}
include '../bottom.php';
 ?>