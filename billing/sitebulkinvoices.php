<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
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

 $sql="SELECT * FROM curs_valutar WHERE curs_valutar_﻿zi='$data'";
	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
	$curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
	 $cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$mSQL = "INSERT INTO curs_valutar(";
	$mSQL = $mSQL . "curs_valutar_﻿zi,";
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

 $sql="SELECT * FROM curs_valutar WHERE curs_valutar_﻿zi='$data'";
	$curs=ezpub_query($conn,$sql);
	$rss=ezpub_fetch_array($curs);
	If (!isSet($rss["curs_valutar_valoare"])){
		
	 $curs=new CursBNR("https://www.bnr.ro/nbrfxrates.xml");
	 $cursvalutar=$curs->getExchangeRate("EUR");
	
	
	$mSQL = "INSERT INTO curs_valutar(";
	$mSQL = $mSQL . "curs_valutar_﻿zi,";
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
	//set invoice number
		$query2="Select factura_numar FROM facturare_facturi WHERE factura_client_inchisa='1' AND factura_tip='0' ORDER BY CAST(factura_numar AS unsigned) DESC";
$result2=ezpub_query($conn,$query2);
$row2=ezpub_fetch_array($result2);
If (!isSet($row2["factura_numar"]))
{$numarfactura=1;}
Else
{$numarfactura=(int)$row2["factura_numar"]+1;}

//gather data
$query="SELECT distinct clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_CUI, clienti_date.Client_RO, clienti_date.Client_CIF, clienti_date.Client_Adresa, clienti_date.Client_Banca, clienti_date.Client_IBAN, 
clienti_date.Client_Judet, clienti_date.Client_Localitate, clienti_date.Client_RC,
clienti_abonamente.abonament_client_ID, clienti_abonamente.abonament_client_contract, clienti_abonamente.abonament_client_sales, clienti_abonamente.abonament_client_unitate, clienti_abonamente.abonament_client_email, 
clienti_abonamente.abonament_client_zifacturare, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_termen, clienti_abonamente.abonament_client_aloc, clienti_abonamente.abonament_client_an, clienti_abonamente.abonament_client_frecventa, 
clienti_abonamente.abonament_client_valoare, clienti_abonamente.abonament_client_valuta, clienti_abonamente.abonament_client_BU, clienti_abonamente.abonament_client_anexa, clienti_abonamente.abonament_client_pdf
FROM clienti_abonamente, clienti_date
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND abonament_client_frecventa<>3 AND abonament_client_activ=0 AND abonament_client_frecventa<>0 AND clienti_date.ID_Client=$selected";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$numar=ezpub_num_rows($result,$query);
$anexa=$row["abonament_client_anexa"];
if ($anexa=='1')
{$file=$row["abonament_client_pdf"];}

$termenfactura = date('Y-m-d', strtotime($dataemiterii . ' +'.$row["abonament_client_termen"].' day'));


If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}
ElseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
	$cefacturez=$_POST["trimestrul_facturarii"];
}
Else
{
	$bucati=1;
	$cefacturez=$_POST["luna_facturarii"];
}

$query1="SELECT abonament_client_valoare, abonament_client_valuta FROM clienti_abonamente WHERE abonament_client_ID=$selected AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0 AND abonament_client_activ=0";
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
If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
}
ElseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
}
$valoare=$bucati*$pret;
$valoaretotalafactura=$valoaretotalafactura+$valoare;
echo $valoaretotalafactura;
}

$articolTVA=$valoaretotalafactura*$vatrat;
$articoltotal=$valoaretotalafactura+$articolTVA;
$factura_tip_activitate="M";
$factura_tip="0";

$mSQL = "INSERT INTO facturare_facturi(";
	$mSQL = $mSQL . "factura_numar,";
	$mSQL = $mSQL . "factura_data_emiterii,";
	$mSQL = $mSQL . "factura_client_ID,";
	$mSQL = $mSQL . "factura_client_denumire,";
	$mSQL = $mSQL . "factura_client_CUI,";
	$mSQL = $mSQL . "factura_client_RO,";
	$mSQL = $mSQL . "factura_client_CIF,";
	$mSQL = $mSQL . "factura_client_RC,";
	$mSQL = $mSQL . "factura_cod_factura,";
	$mSQL = $mSQL . "factura_client_adresa,";
	$mSQL = $mSQL . "factura_client_termen,";
	$mSQL = $mSQL . "factura_client_valoare_totala,";
	$mSQL = $mSQL . "factura_client_valoare,";
	$mSQL = $mSQL . "factura_client_valoare_tva,";
	$mSQL = $mSQL . "factura_client_alocat,";
	$mSQL = $mSQL . "factura_client_achitat,";
	$mSQL = $mSQL . "factura_client_tip_activitate,";
	$mSQL = $mSQL . "factura_tip,";
	$mSQL = $mSQL . "factura_client_judet,";
	$mSQL = $mSQL . "factura_client_localitate,";
	$mSQL = $mSQL . "factura_client_inchisa,";
	$mSQL = $mSQL . "factura_client_anulat,";
	$mSQL = $mSQL . "factura_client_curs_valutar,";
	$mSQL = $mSQL . "factura_client_contract,";
	$mSQL = $mSQL . "factura_client_IBAN,";
	$mSQL = $mSQL . "factura_client_BU,";
	$mSQL = $mSQL . "factura_client_sales,";
	$mSQL = $mSQL . "factura_client_an,";
	$mSQL = $mSQL . "factura_client_banca)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$numarfactura . "', ";
	$mSQL = $mSQL . "'" .$dataemiterii . "', ";
	$mSQL = $mSQL . "'" .$row["ID_Client"] . "', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$row["Client_Denumire"]) . "', ";
	$mSQL = $mSQL . "'" .$row["Client_CUI"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_RO"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_CIF"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_RC"] . "', ";
	$mSQL = $mSQL . "'380', ";
	$mSQL = $mSQL . "'" .str_replace("'","&#39;",$row["Client_Adresa"]) . "', ";
	$mSQL = $mSQL . "'" .$termenfactura . "', ";
	$mSQL = $mSQL . "'" .$articoltotal . "', ";
	$mSQL = $mSQL . "'" .$valoaretotalafactura . "', ";
	$mSQL = $mSQL . "'" .$articolTVA . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_aloc"] . "', ";
	$mSQL = $mSQL . "'0', ";
	$mSQL = $mSQL . "'" .$factura_tip_activitate . "', ";
	$mSQL = $mSQL . "'" .$factura_tip . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Judet"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Localitate"] . "', ";
	$mSQL = $mSQL . "'" .$closed . "', ";
	$mSQL = $mSQL . "'" .$anulat . "', ";
	$mSQL = $mSQL . "'" .$cursvalutar . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_contract"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_IBAN"] . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_BU"] . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_sales"] . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_an"] . "', ";
	$mSQL = $mSQL . "'" .$row["Client_Banca"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{

$invoiceID=ezpub_inserted_id($conn);

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
ElseIf ($row2["abonament_client_frecventa"]==2)
{
	$bucati=3;
	$cefacturez=$_POST["trimestrul_facturarii"];
}
Else
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

$mSQL = "INSERT INTO facturare_articole_facturi(";
	$mSQL = $mSQL . "factura_ID,";
	$mSQL = $mSQL . "articol_descriere,";
	$mSQL = $mSQL . "articol_unitate,";
	$mSQL = $mSQL . "articol_bucati,";
	$mSQL = $mSQL . "articol_pret,";
	$mSQL = $mSQL . "articol_valoare,";
	$mSQL = $mSQL . "articol_procent_TVA,";
	$mSQL = $mSQL . "articol_total,";
	$mSQL = $mSQL . "articol_TVA)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$invoiceID . "', ";
	$mSQL = $mSQL . "'" .$descrierearticol . "', ";
	$mSQL = $mSQL . "'" .$row2["abonament_client_unitate"] . "', ";
	$mSQL = $mSQL . "'" .$bucati . "', ";
	$mSQL = $mSQL . "'" .$pret . "', ";
	$mSQL = $mSQL . "'" .$valoare . "', ";
	$mSQL = $mSQL . "'" .$vatcote . "', ";
	$mSQL = $mSQL . "'" .$articoltotal . "', ";
	$mSQL = $mSQL . "'" .$articolTVA . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
}}
Else
{
$descrierearticol=$row["abonament_client_detalii"] ." - prestări servicii conform contract ". $row["abonament_client_contract"] ." pentru ".$cefacturez;
//insert invoice items

$mSQL = "INSERT INTO facturare_articole_facturi(";
	$mSQL = $mSQL . "factura_ID,";
	$mSQL = $mSQL . "articol_descriere,";
	$mSQL = $mSQL . "articol_unitate,";
	$mSQL = $mSQL . "articol_bucati,";
	$mSQL = $mSQL . "articol_pret,";
	$mSQL = $mSQL . "articol_valoare,";
	$mSQL = $mSQL . "articol_procent_TVA,";
	$mSQL = $mSQL . "articol_total,";
	$mSQL = $mSQL . "articol_TVA)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$invoiceID . "', ";
	$mSQL = $mSQL . "'" .$descrierearticol . "', ";
	$mSQL = $mSQL . "'" .$row["abonament_client_unitate"] . "', ";
	$mSQL = $mSQL . "'" .$bucati . "', ";
	$mSQL = $mSQL . "'" .$pret . "', ";
	$mSQL = $mSQL . "'" .$valoare . "', ";
	$mSQL = $mSQL . "'" .$vatcote . "', ";
	$mSQL = $mSQL . "'" .$articoltotal . "', ";
	$mSQL = $mSQL . "'" .$articolTVA . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }	
}
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

$sumafacturii=$row22['factura_client_valoare_totala'];
$barcodesuma=number_format(abs($sumafacturii),2,'','');
$barcodesuma=str_pad($barcodesuma, 8, '0', STR_PAD_LEFT);
$barcodenumarfactura=str_pad($row22["factura_numar"], 6, '0', STR_PAD_LEFT);
$codenumarfactura=str_pad($row22["factura_numar"], 8, '0', STR_PAD_LEFT);
$barcodedataemiterii=date("dmy", strtotime($row22["factura_data_emiterii"]));
$barcodedatascadentei=date("dmy", strtotime($row22["factura_client_termen"]));
$barcodeemitent=$siteCIF;
$barcode=$barcodeemitent.$barcodedataemiterii.$barcodedatascadentei.$barcodenumarfactura.$barcodesuma;


$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px;font-size: 12px; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 0px;}";
$HTMLBody=$HTMLBody . "td {font-size: 10px; font-family: 'Open Sans',sans-serif; COLOR: #000000; padding: 3px;  font-weight: normal;}";
$HTMLBody=$HTMLBody . "th {font-size: 12px; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . $color ."; padding: 3px; font-weight: normal;}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . ".barcode {padding: 1.5mm; margin: 0;	vertical-align: top; color: " . $color ."; } .barcodecell {text-align: center;	vertical-align: middle;	padding: 0;}";
$HTMLBody=$HTMLBody . "table,IMG,A {BORDER: 0px;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse;}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<htmlpageheader name=\"myheader\">";
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\" height=\"100%\"><tr><td width=\"50%\">";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"../img/logo.jpg\" title=\"$strSiteOwner\" width=\"300\" /></a></td>";
$HTMLBody=$HTMLBody . "<td valign=\"bottom\" width=\"50%\" >";
$HTMLBody=$HTMLBody . "<h1>Factura $siteInvoicingCode Nr. $codenumarfactura</h1>";
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row22["factura_data_emiterii"]))."</h3>";
$HTMLBody=$HTMLBody . "<h3>Data scadenței: ".date("d.m.Y", strtotime($row22["factura_client_termen"]))."</h3>";
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
$HTMLBody=$HTMLBody . "<h4>".$row22["factura_client_denumire"]."</h4>CUI: ".$row22["factura_client_RO"]." ".$row22["factura_client_CIF"] ." <br />Nr. Înreg. Reg. Com: ".$row22["factura_client_RC"]."<br />
Adresă: ".$row22["factura_client_adresa"].".<br />
Localitate: ".$row22["factura_client_localitate"]."<br />
Județ: ".$row22["factura_client_judet"]."<br />
IBAN: ".$row22["factura_client_IBAN"]."<br />".$row22["factura_client_banca"]."<br />
Contract: ".$row22["factura_client_contract"];
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "</htmlpageheader>";
$HTMLBody=$HTMLBody . "
<htmlpagefooter name=\"myfooter\">";
	$HTMLBody=$HTMLBody . "<div style=\"position: absolute; left: 20mm; bottom: 5mm; right: 10mm; \"><p align=\"right\">pagina {PAGENO}/{nb}</p>";
		$HTMLBody=$HTMLBody . "<div style=\"background-color: " . $color ."; padding: 10px;\">";
$HTMLBody=$HTMLBody . "<font color=\"#ffffff\" size=\"3\">Acest document are doar rol informativ. Factura fiscală a fost încărcată în sistemul efactura și o puteți descărca din SPV. Vă rugăm să achitați această factură până la data ".date('d.m.Y',strtotime($row22["factura_client_termen"])).". <br />
Pentru orice întrebări legate de factură, ne puteți contacta la $siteCompanyPhones ori prin email la $siteCompanyEmail. <br />
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
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"center\">U.M.</th>";
$HTMLBody=$HTMLBody . "<th width=\"5%\" align=\"right\">Cantitate</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Preț <br />(lei)</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Valoare</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">Cota TVA</th>";
$HTMLBody=$HTMLBody . "<th width=\"10%\" align=\"right\">TVA</th>";
$HTMLBody=$HTMLBody . "</tr></thead>";
$query222="SELECT * FROM facturare_articole_facturi WHERE factura_ID=$invoiceID";
$result222=ezpub_query($conn,$query222);
$count=0;
While ($row222=ezpub_fetch_array($result222)){
	$count=$count+1;
$HTMLBody=$HTMLBody . "<tr>
<td align=\"left\">". $count ."</td>
<td align=\"left\">". $row222["articol_descriere"] ."</td>
<td align=\"center\">". $row222["articol_unitate"] ."</td>
<td align=\"right\">". $row222["articol_bucati"] ."</td>
<td align=\"right\">". romanize($row222["articol_pret"]) ."</td>
<td align=\"right\">". romanize($row222["articol_valoare"]) ."</td>
<td align=\"right\">". $row222["articol_procent_TVA"] ."</td>
<td align=\"right\">". romanize($row222["articol_TVA"]) ."</td>
</tr>";
}
$HTMLBody=$HTMLBody . "<tr><td colspan=\"8\"></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"6\"><strong>Total fără TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row22["factura_client_valoare"]) ."</strong></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"6\"><strong>Total TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row22["factura_client_valoare_tva"]) ."</strong></td></tr>";
$HTMLBody=$HTMLBody . "<tr bgcolor=\"" . $color ."\"><td colspan=\"5\"><font color=\"#ffffff\" size=\"4\"><strong>Total de plată</strong></font></td><td colspan=\"2\" align=\"right\"><font color=\"#ffffff\" size=\"5\"><strong>". romanize($row22["factura_client_valoare_totala"]) ." lei</strong></font></td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<h5>Curs valutar: 1€= ".$row22["factura_client_curs_valutar"]." lei</h5>";
$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$invoice=$HTMLBody;

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $invoice_folder ."/Factura_". $siteInvoicingCode. $codenumarfactura .'.pdf','F');
$invoicename='Factura_'. $siteInvoicingCode. $codenumarfactura. '.pdf';

$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $invoiceID . ";";
$query11= "UPDATE facturare_facturi SET facturare_facturi.factura_client_pdf='1' ," ;
$query11= $query11 . "facturare_facturi.factura_client_pdf_generat='" .$data . "' " ;
$query11= $query11 . $strWhereClause;

if (!ezpub_query($conn,$query11))
  {
  die('Error: ' . ezpub_error($conn));
  }
 Else 
 {
 echo "<div class=\"callout success\">Factura_". $siteInvoicingCode. $codenumarfactura  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=1&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
 ///send email
  
$query33="SELECT SUM(factura_client_valoare_totala) AS valoare_sold FROM facturare_facturi WHERE factura_client_ID='$row[ID_Client]' AND factura_client_achitat='0'";
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
$emailto=str_replace(' ', '', $row["abonament_client_email"]);
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

echo $anexa;
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
  
 }///
}
}
}
}
Else {
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="sitebulkinvoices.php?id=1" class="button">1</a><a href="sitebulkinvoices.php?id=15" class="button warning">15</a><a href="sitebulkinvoices.php" class="button success"><?php echo $strAll?></a></p>
</div>
</div>
 <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<form Method="post" id="users" Action="sitebulkinvoices.php">
<div class="grid-x grid-margin-x">
			  <div class="large-6 medium-6 small-6 cell">
<input name="luna_facturarii" type="text"  placeholder=<?php echo $strInvoiceMonth ?> value=""  />
</div>
			  <div class="large-6 medium-6 small-6 cell">
<input name="trimestrul_facturarii" type="text"  placeholder=<?php echo $strInvoiceQuarter ?> value=""  />
</div>
</div>

    <div class="grid-x grid-padding-x ">
              <div class="large-4 medium-4 cell">
			 <label> <?php echo $strDay?></label>
      <select name="strEData1">
	  <option value="00" selected>--</option>
<?php
// Loop through 1 To max days In a month
    	for ( $d = 1; $d <= 31; $d ++) {
    		
    		// create option With numeric value of day
    		if ($day==$d){
    		echo "<OPTION selected value=\"$d\">$d</OPTION>";}
			else {echo "<OPTION value=\"$d\">$d</OPTION>";}
			} 
?>
        </select> 
		</div>
		 <div class="large-4 medium-4 cell">
		 <label> <?php echo $strMonth?></label>
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
    			echo "<OPTION selected value=\"$m\">$monthname</OPTION>";}
				Else
				{echo "<OPTION value=\"$m\">$monthname</OPTION>";}
				} 
			?>
        </select> 
		</div>
		 <div class="large-4 medium-4 cell">
		 <label> <?php echo $strYear?></label>
		<select name="strEData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    			if ($year==$y){
    	echo "<OPTION selected value=\"$y\">$y</OPTION>";}
		Else{
		echo "<OPTION value=\"$y\">$y</OPTION>";
		}
		} 
			?>
        </select>
		</div>
		</div>
<?php
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, 
clienti_abonamente.abonament_client_zifacturare, clienti_abonamente.abonament_client_detalii, clienti_abonamente.abonament_client_frecventa,clienti_abonamente.abonament_client_ID
FROM clienti_abonamente, clienti_date
WHERE clienti_date.ID_Client=clienti_abonamente.abonament_client_ID AND abonament_client_activ=0 AND abonament_client_frecventa<>3 AND abonament_client_frecventa<>0";
if ((isset( $_GET['id'])) && !empty( $_GET['id'])){
$id=$_GET['id'];
$query=$query . " AND abonament_client_zifacturare='$id'";}
$query=$query . " GROUP BY clienti_date.ID_Client ORDER BY clienti_date.Client_Denumire ASC";

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
	?>
	<script>
	function toggle(source) {
  checkboxes = document.getElementsByName('invoice[]');
  for(var i=0, n=checkboxes.length;i<n;i++) {
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
			<th><?php echo $strSum?></th>
        </tr>
		</thead>
<?php	While ($row=ezpub_fetch_array($result)){
	
If ($row["abonament_client_frecventa"]==1)
{
	$bucati=1;
	}
ElseIf ($row["abonament_client_frecventa"]==2)
{
	$bucati=3;
	}
Else
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
			<td align=\"right\">";
			echo romanize($articoltotal);
			echo "</td>
			</tr>";

}
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td><td>&nbsp;</td></tr></tfoot></table>";
}

?>
</div>
</div>

 <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell"> <input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button success"> 
		 </div>
	</div>
  </form>
  <?php }?>
</div>
</div>
<?php
include '../bottom.php';
?>