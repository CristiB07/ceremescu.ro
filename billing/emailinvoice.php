<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$strPageTitle="Trimitere email";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['$code'];
$day = date('d');
$year = date('Y');
$month = date('m');
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	
$query="SELECT * FROM facturare_facturi WHERE factura_ID='$_GET[cID]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
If ($row["factura_tip"]=='0')
{
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
}
Else
{
	$codenumarfactura=str_pad($row["factura_numar"], 8, '1', STR_PAD_LEFT);
}

	If (!isSet($row["factura_client_pdf"])){
	
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

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 80,
	'margin_bottom' => 60,
	'margin_header' => 10,
	'margin_footer' => 50,
	'showBarcodeNumbers' => true
]);
$mpdf->SetTitle($strInvoice . " ". $codenumarfactura);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetSubject('Facturarea serviciilor ' . $siteCompanyLegalName );
$mpdf->SetKeywords('factură, factura, invoice');

$sumafacturii=$row['factura_client_valoare_totala'];
$barcodesuma=number_format(abs($sumafacturii),2,'','');
$barcodesuma=str_pad($barcodesuma, 8, '0', STR_PAD_LEFT);
$barcodenumarfactura=str_pad($row["factura_numar"], 6, '0', STR_PAD_LEFT);
$barcodedataemiterii=date("dmy", strtotime($row["factura_data_emiterii"]));
$barcodedatascadentei=date("dmy", strtotime($row["factura_client_termen"]));
$barcodeemitent=$siteCIF;
$barcode=$barcodeemitent.$barcodedataemiterii.$barcodedatascadentei.$barcodenumarfactura.$barcodesuma;

$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px;font-size: 12px;font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 0px;}";
$HTMLBody=$HTMLBody . "td {font-size: 10px; font-family: 'Open Sans',sans-serif; color: #000000; padding: 3px;  font-weight: normal;}";
$HTMLBody=$HTMLBody . "th {font-size: 12px; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color ."; padding: 3px; font-weight: normal;}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . "table,IMG,A {BORDER: 0px;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse;}";
$HTMLBody=$HTMLBody . ".barcode {padding: 1.5mm; margin: 0;	vertical-align: top; color: " . $color ."; } .barcodecell {text-align: center;	vertical-align: middle;	padding: 0;}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<htmlpageheader name=\"myheader\">";
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\" height=\"100%\"><tr><td width=\"50%\">";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"../img/logo.jpg\" title=\"$strSiteOwner\" width=\"300\" /></a></td>";
$HTMLBody=$HTMLBody . "<td valign=\"bottom\" width=\"50%\" >";
If ($row["factura_tip"]=='0')
{
$HTMLBody=$HTMLBody . "<h1>Factura $siteInvoicingCode Nr. $codenumarfactura</h1>";
}
Else
	{
$HTMLBody=$HTMLBody . "<h1>Proforma $siteInvoicingCode Nr. $codenumarfactura</h1>";
}
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row["factura_data_emiterii"]))."</h3>";
$HTMLBody=$HTMLBody . "<h3>Data scadenței: ".date("d.m.Y", strtotime($row["factura_client_termen"]))."</h3>";
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr><tr><td colspan=\"2\"><h3>&nbsp;</h3></td></tr></table>";
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
$HTMLBody=$HTMLBody . "<h4>".$row["factura_client_denumire"]."</h4>CUI: ".$row["factura_client_CUI"]." <br />Nr. Înreg. Reg. Com: ".$row["factura_client_RC"]."<br />
Adresă: ".$row["factura_client_adresa"].".<br />
Localitate: ".$row["factura_client_localitate"]."<br />
Județ: ".$row["factura_client_judet"]."<br />
IBAN: ".$row["factura_client_IBAN"]."<br />".$row["factura_client_banca"]."<br />
Contract: ".$row["factura_client_contract"];
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "</htmlpageheader>";
$HTMLBody=$HTMLBody . "<htmlpagefooter name=\"myfooter\">";
$HTMLBody=$HTMLBody . "<div style=\"position: absolute; left: 20mm; bottom: 5mm; right: 10mm; \"><p align=\"right\">pagina {PAGENO}/{nb}</p>";
$HTMLBody=$HTMLBody . "<div style=\"background-color: " . $color ."; padding: 10px;\">";
$HTMLBody=$HTMLBody . "<font color=\"#ffffff\" size=\"3\">Vă rugăm să achitați această factură până la data ".date('d.m.Y',strtotime($row["factura_client_termen"])).". <br />
Pentru orice întrebări legate de factură, ne puteți contacta la $siteCompanyPhones ori prin email la $siteCompanyEmail. <br />
Conform art. 319 alin. 29 din Legea 227/2015 privind Codul Fiscal, semnarea și ștampilarea facturilor nu constituie elemente obligatorii pe care trebuie să le conțină factura.<br />
Vă mulţumim pentru utilizarea serviciilor noastre!<br /> Pentru înregistrarea facturii în sistemele automate de gestiune contabilă, codul de bare este de tip C128 și este format din codul fiscal al ".$siteCompanyLegalName." (8 caractere), data emiterii în format zzllaa, data scadenței în format zzllaa, număr factură (8 caractere), suma de plată cu 6 cifre și 2 zecimale. </font>";
$HTMLBody=$HTMLBody . "</div><br />";
$HTMLBody=$HTMLBody . "<table width=\"100%\"><tr><td class=\"barcodecell\"><barcode code=\"$barcode\" type=\"C128C\" class=\"barcode\"  size=\"1.0\" height=\"0.8\"/><div style=\"font-family: ocrb; color: ".$color.";\">$barcode</div></td></tr></table>";
$HTMLBody=$HTMLBody . "</div>
</htmlpagefooter><sethtmlpageheader name=\"myheader\" value=\"on\" show-this-page=\"1\" />";
$HTMLBody=$HTMLBody ."<sethtmlpagefooter name=\"myfooter\" value=\"on\" />";
$HTMLBody=$HTMLBody . "<p align=\"right\">Cota TVA $siteVATMain</p>";
$HTMLBody=$HTMLBody . "<table align=\"center\" width=\"100%\">";
$HTMLBody=$HTMLBody . "<thead><tr>";
$HTMLBody=$HTMLBody . "<th width=\"5%\" align=\"left\">Nr. art</th>";
$HTMLBody=$HTMLBody . "<th width=\"50%\" align=\"left\">Produs</th>";
$HTMLBody=$HTMLBody . "<th width=\"5%\" align=\"center\">U.M.</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Cantitate</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Preț <br />(lei)</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Valoare</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Cota TVA</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">TVA</th>";
$HTMLBody=$HTMLBody . "</tr></thead>";
$query2="SELECT * FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
$result2=ezpub_query($conn,$query2);
$count=0;
While ($row2=ezpub_fetch_array($result2)){
	$count=$count+1;
$HTMLBody=$HTMLBody . "<tr>
<td align=\"left\">". $count ."</td>
<td align=\"left\">". $row2["articol_descriere"] ."</td>
<td align=\"center\">". $row2["articol_unitate"] ."</td>
<td align=\"right\">". $row2["articol_bucati"] ."</td>
<td align=\"right\">". romanize($row2["articol_pret"]) ."</td>
<td align=\"right\">". romanize($row2["articol_valoare"]) ."</td>
<td align=\"right\">". $row2["articol_procent_TVA"] ."</td>
<td align=\"right\">". romanize($row2["articol_TVA"]) ."</td>
</tr>";
}
$HTMLBody=$HTMLBody . "<tr><td colspan=\"8\"></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"6\"><strong>Total fără TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row["factura_client_valoare"]) ."</strong></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"6\"><strong>Total TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row["factura_client_valoare_tva"]) ."</strong></td></tr>";
$tpquery="SELECT DISTINCT articol_procent_TVA FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";
        $tpresult=ezpub_query($conn,$tpquery);
      While  ($tprow=ezpub_fetch_array($tpresult)){
        $subtotalq="SELECT SUM(articol_TVA) AS subtotal FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID] AND articol_procent_TVA='$tprow[articol_procent_TVA]'";
        $subtotalr=ezpub_query($conn,$subtotalq);
        $rowsb=ezpub_fetch_array($subtotalr);
$HTMLBody=$HTMLBody . "<tr><td colspan=\"6\"><strong>Total TVA cota ".$tprow["articol_procent_TVA"]."%</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($rowsb["subtotal"]) ."</strong></td></tr>";
      }
$HTMLBody=$HTMLBody . "<tr bgcolor=\"" . $color ."\"><td colspan=\"6\"><font color=\"#ffffff\" size=\"4\"><strong>Total de plată</strong></font></td><td colspan=\"2\" align=\"right\"><font color=\"#ffffff\" size=\"5\"><strong>". romanize($row["factura_client_valoare_totala"]) ." lei</strong></font></td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<h5>Curs valutar: 1€= ".$row["factura_client_curs_valutar"]." lei</h5>";
$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$invoice=$HTMLBody;

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $invoice_folder ."/Factura_". $siteInvoicingCode. $codenumarfactura .'.pdf','F');
$invoicename='Factura_'. $siteInvoicingCode. $codenumarfactura. '.pdf';

$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $_GET["cID"] . ";";
$query22= "UPDATE facturare_facturi SET facturare_facturi.factura_client_pdf='1' ," ;
$query22= $query22 . "facturare_facturi.factura_client_pdf_generat='" .$data . "' " ;
$query22= $query22 . $strWhereClause;

if (!ezpub_query($conn,$query22))
  {
  echo $query22;
  die('Error: ' . ezpub_error($conn,$query22));
  }
echo "<div class=\"callout success\">Factura_". $siteInvoicingCode. $codenumarfactura  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=1&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
}
$query22="SELECT * FROM facturare_facturi WHERE factura_ID='$_GET[cID]'";
$result22=ezpub_query($conn,$query22);
$row22=ezpub_fetch_array($result22);

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
$emailbody=$emailbody . $_POST["email_body"];
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
Else
{
 ?>
 			  <script src='../js/tinymce/tinymce.min.js'></script>
<script language="JavaScript" type="text/JavaScript">
tinymce.init({
  selector: "textarea.myTextEditor",
  menubar: false,
  image_advtab: false,
   plugins: [
    'advlist autolink lists link imagetools charmap print preview anchor',
    'searchreplace visualblocks code fullscreen preview',
    'insertdatetime media table contextmenu paste code pagebreak'
  ],
  toolbar: 'insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link preview code pagebreak',
  content_css: [
    '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
    '//www.tinymce.com/css/codepen.min.css'],
	 image_title: true, 
  // enable automatic uploads of images represented by blob or data URIs
  paste_data_images: false,
  automatic_uploads: false,
  // URL of our upload handler (for more details check: https://www.tinymce.com/docs/configure/file-image-upload/#images_upload_url)
  images_upload_url: 'postAcceptor.php',
    images_upload_base_path: '',
  images_upload_credentials: true,
  file_picker_types: 'file image media',
  
 file_picker_callback: function(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image*');
    
    // Note: In modern browsers input[type="file"] is functional without 
    // even adding it to the DOM, but that might not be the case in some older
    // or quirky browsers like IE, so you might want to add it to the DOM
    // just in case, and visually hide it. And do not forget do remove it
    // once you do not need it anymore.

    input.onchange = function() {
      var file = this.files[0];
      
      var reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = function () {
        // Note: Now we need to register the blob in TinyMCEs image blob
        // registry. In the next release this part hopefully won't be
        // necessary, as we are looking to handle it internally.
        var id = 'blobid' + (new Date()).getTime();
        var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        var base64 = reader.result.split(',')[1];
        var blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        // call the callback and populate the Title field with the file name
        cb(blobInfo.blobUri(), { title: file.name });
      };
    };
    
    input.click();
  }
});

</script>
<?php
$query22="SELECT * FROM facturare_facturi WHERE factura_ID='$_GET[cID]'";
$result22=ezpub_query($conn,$query22);
$row22=ezpub_fetch_array($result22);
$codenumarfactura=str_pad($row22["factura_numar"], 8, '0', STR_PAD_LEFT);
$query33="SELECT SUM(factura_client_valoare_totala) AS valoare_sold FROM facturare_facturi WHERE factura_client_ID='$row22[factura_client_ID]' AND factura_client_achitat='0'";
$result33=ezpub_query($conn,$query33);
$row33=ezpub_fetch_array($result33);
$soldtotal=$row33["valoare_sold"];
$soldanterior=$soldtotal-$row22["factura_client_valoare_totala"];
$clientID=$row22["factura_client_ID"];
$query4="SELECT Contract_Email_Facturare FROM date_contracte_clienti WHERE ID_Client='$clientID'";
$result4=ezpub_query($conn,$query4);
$row4=ezpub_fetch_array($result4);

if (empty( $row4['Contract_Email_Facturare']))
{
echo "<div class=\"callout alert\">".$strNoEmailAddressesFound."</div>";
$emailaddress='';
}
Else {
$emailaddress=$row4['Contract_Email_Facturare'];
}
?>
   <h1><?php echo $strSendInvoice ?></h1>
<form method="POST" action="emailinvoice.php?cID=<?php echo $_GET["cID"]?>" enctype="multipart/form-data">
    <div class="grid-x grid-padding-x ">
	      <div class="medium-6 cell">
        <label><?php echo $strEmail ?>
          <input type="text" id="email" name="email_client" value="<?php echo $emailaddress ?>"/>
        </label>
      </div>	      
	  <div class="medium-6 cell">
        <label><?php echo $strAttachement ?>
    <input type="file" name="file[]" id="file" multiple>
    <input type='submit' name='submit' value='Upload'>
        </label>
      </div>
      </div>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
	  <label><?php echo $strMessage?></label>
	  <textarea name="email_body" id="myTextEditor" class="myTextEditor" rows="5">
	  <p>Stimate client,</p>
	<p>Acest mail conține factura <?php echo $strSiteOwner?>. Ea a fost emisă pe <?php echo date("d.m.Y", strtotime($row22["factura_data_emiterii"]))?> și este în valoare de <?php echo
	 romanize($row22["factura_client_valoare_totala"]) ?> și are ca termen <?php echo date('d.m.Y',strtotime($row22["factura_client_termen"]))?>. La data emiteri facturii, soldul dumneavoastră este <?php echo romanize($soldanterior)?>.</p> 
	<p>Daca doriți sa modificați adresa de e-mail pentru primirea facturilor sau pentru informații și sesizări privind serviciile noastre, vă rugam să folosiți adresa <?php echo $siteCompanyEmail?> sau să ne apelați la <?php echo $siteCompanyPhones?>.</p>
	<p>Mulțumim,<br />
<?php echo $strSiteOwner?></p>
	  </textarea>
		</div>
		</div>
	 		 <div class="grid-x grid-padding-x">
              <div class="large-12 cell"><input Type="submit" Value="<?php echo $strSend ?>" name="Submit" class="button success" />
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