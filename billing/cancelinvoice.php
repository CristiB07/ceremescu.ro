<?php
//update 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Anulare factură";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

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

$strWhereClause = " WHERE facturare_facturi.factura_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_facturi SET facturare_facturi.factura_client_pdf='1' ," ;
$query= $query. " facturare_facturi.factura_client_anulat='1' ," ;
$query= $query. " facturare_facturi.factura_client_valoare='0' ," ;
$query= $query. " facturare_facturi.factura_client_valoare_totala='0' ," ;
$query= $query. " facturare_facturi.factura_client_valoare_tva='0' ," ;
$query= $query . "facturare_facturi.factura_client_pdf_generat='" .$data . "' " ;
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn,$query));
  }
  
  $strWhereClause = " WHERE facturare_articole_facturi.factura_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_articole_facturi SET facturare_articole_facturi.articol_bucati='0', " ;
$query= $query. " facturare_articole_facturi.articol_valoare='0', " ;
$query= $query. " facturare_articole_facturi.articol_TVA='0', " ;
$query= $query. " facturare_articole_facturi.articol_pret='0', " ;
$query= $query . "facturare_articole_facturi.articol_total='0' " ;
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn,$query));
  }

$query="SELECT * FROM facturare_facturi WHERE factura_ID='$_GET[cID]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 80,
	'margin_bottom' => 60,
	'margin_header' => 10,
	'margin_footer' => 50,
	'showBarcodeNumbers' => true
]);
$mpdf->SetTitle($strInvoice . " ". $siteInvoicingCode . " Nr. 0000".$row["factura_numar"]);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetSubject('Facturarea serviciilor ' . $siteCompanyLegalName );
$mpdf->SetKeywords('factură, factura, invoice');
$mpdf->SetWatermarkText('ANULATĂ');
$mpdf->showWatermarkText = true;

$sumafacturii=$row['factura_client_valoare_totala'];
$barcodesuma=number_format(abs($sumafacturii),2,'','');
$barcodesuma=str_pad($barcodesuma, 8, '0', STR_PAD_LEFT);
$barcodenumarfactura=str_pad($row["factura_numar"], 6, '0', STR_PAD_LEFT);
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
$barcodedataemiterii=date("dmy", strtotime($row["factura_data_emiterii"]));
$barcodedatascadentei=date("dmy", strtotime($row["factura_client_termen"]));
$barcodeemitent=$siteCIF;
$barcode=$barcodeemitent.$barcodedataemiterii.$barcodedatascadentei.$barcodenumarfactura.$barcodesuma;

$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px;font-size: 12px;font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 0px;}";
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
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row["factura_data_emiterii"]))."</h3>";
$HTMLBody=$HTMLBody . "<h3>Data anulării: ".date("d.m.Y")."</h3>";
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
$HTMLBody=$HTMLBody . "<h4>".$row["factura_client_denumire"]."</h4>CUI: ".$row["factura_client_RO"]." ".$row["factura_client_CIF"]. " <br />Nr. Înreg. Reg. Com: ".$row["factura_client_RC"]."<br />
Adresă: ".$row["factura_client_adresa"].".<br />
Localitate: ".$row["factura_client_localitate"]."<br />
Județ: ".$row["factura_client_judet"]."<br />
IBAN: ".$row["factura_client_IBAN"]."<br />".$row["factura_client_banca"]."<br />
Contract: ".$row["factura_client_contract"];
$HTMLBody=$HTMLBody . "</td>";
$HTMLBody=$HTMLBody . "</tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "</htmlpageheader>";
$HTMLBody=$HTMLBody . "
<htmlpagefooter name=\"myfooter\">";
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
<td align=\"right\">". romanize($row2["articol_procent_TVA"]) ."</td>
<td align=\"right\">". romanize($row2["articol_TVA"]) ."</td>
</tr>";
}
$HTMLBody=$HTMLBody . "<tr><td colspan=\"7\"></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"5\"><strong>Total fără TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row["factura_client_valoare"]) ."</strong></td></tr>";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"5\"><strong>Total TVA</strong></td><td colspan=\"2\" align=\"right\"><strong>". romanize($row["factura_client_valoare_tva"]) ."</strong></td></tr>";
$HTMLBody=$HTMLBody . "<tr bgcolor=\"" . $color ."\"><td colspan=\"5\"><font color=\"#ffffff\" size=\"4\"><strong>Total de plată</strong></font></td><td colspan=\"2\" align=\"right\"><font color=\"#ffffff\" size=\"5\"><strong>". romanize($row["factura_client_valoare_totala"]) ." lei</strong></font></td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<h5>Curs valutar: 1€= ".$row["factura_client_curs_valutar"]." lei</h5>";
$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$invoice=$HTMLBody;

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $invoice_folder ."/Factura_". $siteInvoicingCode. $codenumarfactura .'.pdf','F');
$invoicename='Factura_'. $siteInvoicingCode. $codenumarfactura. '.pdf';

echo "<div class=\"callout success\">Factura_". $siteInvoicingCode. $codenumarfactura  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=1&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
include '../bottom.php';
?>