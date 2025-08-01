<?php
//update 01.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/convertor.class.php';
$strPageTitle="Anulare chitanță";
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

$query="SELECT facturare_chitante.chitanta_numar,  facturare_chitante.chitanta_factura_ID, facturare_chitante.chitanta_pdf, facturare_chitante.chitanta_data_incasarii,  facturare_chitante.chitanta_descriere, facturare_chitante.chitanta_suma_incasata,
facturare_facturi.factura_client_denumire, facturare_facturi.factura_client_CUI, facturare_facturi.factura_client_RC, factura_client_adresa, factura_data_emiterii, factura_numar
FROM facturare_chitante, facturare_facturi
Where chitanta_factura_ID=factura_ID AND chitanta_ID='$_GET[cID]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$ammount=romanize($row["chitanta_suma_incasata"]);
$codenumarchitanta=str_pad($row["chitanta_numar"], 8, '0', STR_PAD_LEFT);
$codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);


$mpdf = new \Mpdf\Mpdf();
$mpdf->SetWatermarkText('ANULATĂ');
$mpdf->showWatermarkText = true;
$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px;font-size: 12px;font-family: font-family: 'Open Sans',sans-serif; padding: 0px;}";
$HTMLBody=$HTMLBody . "td {font-size: 10px; font-family: 'Open Sans',sans-serif; COLOR: #000000; padding: 3px;  font-weight: normal;}";
$HTMLBody=$HTMLBody . "th {font-size: 12px; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . $color ."; padding: 3px; font-weight: normal;}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . "table,IMG,A {BORDER: 0px;}";
$HTMLBody=$HTMLBody . "table .spacing {line-height:150%;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse;}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\" height=\"100%\">";
$HTMLBody=$HTMLBody . "<tr><td valign=\"top\" width=\"50%\" >";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"../img/logo.jpg\" title=\"$strSiteOwner\" width=\"200\" /></a><br /><br /><br /><br />";
$HTMLBody=$HTMLBody . "<h1>Chitanța $siteInvoicingCode Nr.".$codenumarchitanta."</h1>";
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row["chitanta_data_incasarii"]))."</h3></td>
<td width=\"50%\" valign=\"top\">
<h3>$siteCompanyLegalName</h3>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
$siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />$siteFirstAccount<br />
<h5>TVA la încasare.</h5></td></tr></table><br /><br />";
$HTMLBody=$HTMLBody . "<table border=\"0\"  align=\"center\" width=\"100%\" class=\"spacing\">";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"2\" class \"spacing\">";
$HTMLBody=$HTMLBody . "<font size=\"5\" STYLE=\"line-height:1.5\">Am primit de la <strong>".$row["factura_client_denumire"]."</strong><br /> 
Adresă: ".$row["factura_client_adresa"]."<br />
CUI: ".$row["factura_client_CUI"]."; Nr. Înreg. Reg. Com: ".$row["factura_client_RC"].";<br />";
$HTMLBody=$HTMLBody . "Suma de <strong>".romanize($row["chitanta_suma_incasata"])." (". StrLei($ammount,'.',','). ")</strong><br /> 
Reprezentând: contravaloarea facturii ". $siteInvoicingCode ."Nr. ".$codenumarfactura."/".date("d.m.Y", strtotime($row["factura_data_emiterii"]))."  ";
$HTMLBody=$HTMLBody . "</font></td>";
$HTMLBody=$HTMLBody . "</tr><tr><td width=\"50%\">&nbsp;</td><td width=\"50%\" align=\"right\">$strCashier</td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<br />";
$HTMLBody=$HTMLBody . "<hr />";
//copy 2
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\" height=\"100%\">";
$HTMLBody=$HTMLBody . "<tr><td valign=\"top\" width=\"50%\" >";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"../img/logo.jpg\" title=\"$strSiteOwner\" width=\"200\" /></a><br /><br /><br /><br />";
$HTMLBody=$HTMLBody . "<h1>Chitanța $siteInvoicingCode Nr.".$codenumarchitanta."</h1>";
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row["chitanta_data_incasarii"]))."</h3></td>
<td width=\"50%\" valign=\"top\">
<h3>$siteCompanyLegalName</h3>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
$siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />$siteFirstAccount<br />
<h5>TVA la încasare.</h5></td></tr></table><br /><br />";
$HTMLBody=$HTMLBody . "<table border=\"0\"  align=\"center\" width=\"100%\" class=\"spacing\">";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"2\" class \"spacing\">";
$HTMLBody=$HTMLBody . "<font size=\"5\" STYLE=\"line-height:1.5\">Am primit de la <strong>".$row["factura_client_denumire"]."</strong><br /> 
Adresă: ".$row["factura_client_adresa"]."<br />
CUI: ".$row["factura_client_CUI"]."; Nr. Înreg. Reg. Com: ".$row["factura_client_RC"].";<br />";
$HTMLBody=$HTMLBody . "Suma de <strong>".romanize($row["chitanta_suma_incasata"])." (". StrLei($ammount,'.',','). ")</strong><br /> 
Reprezentând: contravaloarea facturii ". $siteInvoicingCode ."Nr. ".$codenumarfactura."/".date("d.m.Y", strtotime($row["factura_data_emiterii"]))."  ";
$HTMLBody=$HTMLBody . "</font></td>";
$HTMLBody=$HTMLBody . "</tr><tr><td width=\"50%\">&nbsp;</td><td width=\"50%\" align=\"right\">$strCashier</td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "<br />";
$HTMLBody=$HTMLBody . "<hr />";

//copy 3
$HTMLBody=$HTMLBody . "<table border=\"0\" align=\"center\" width=\"100%\" height=\"100%\">";
$HTMLBody=$HTMLBody . "<tr><td valign=\"top\" width=\"50%\" >";
$HTMLBody=$HTMLBody . "<a href=\"$siteCompanyWebsite\"><img src=\"../img/logo.jpg\" title=\"$strSiteOwner\" width=\"200\" /></a><br /><br /><br /><br />";
$HTMLBody=$HTMLBody . "<h1>Chitanța $siteInvoicingCode Nr.".$codenumarchitanta."</h1>";
$HTMLBody=$HTMLBody . "<h3>Data emiterii: ". date("d.m.Y", strtotime($row["chitanta_data_incasarii"]))."</h3></td>
<td width=\"50%\" valign=\"top\">
<h3>$siteCompanyLegalName</h3>CUI: $siteVATNumber; $siteCompanyRegistrationNr; Capital social $siteCompanySocialCapital.<br />
$siteCompanyLegalAddress<br />
$siteCompanyPhones; Email: $siteCompanyEmail $siteCompanyShortSite<br />$siteFirstAccount<br />
<h5>TVA la încasare.</h5></td></tr></table><br /><br />";
$HTMLBody=$HTMLBody . "<table border=\"0\"  align=\"center\" width=\"100%\" class=\"spacing\">";
$HTMLBody=$HTMLBody . "<tr><td colspan=\"2\" class \"spacing\">";
$HTMLBody=$HTMLBody . "<font size=\"5\" STYLE=\"line-height:1.5\">Am primit de la <strong>".$row["factura_client_denumire"]."</strong><br /> 
Adresă: ".$row["factura_client_adresa"]."<br />
CUI: ".$row["factura_client_CUI"]."; Nr. Înreg. Reg. Com: ".$row["factura_client_RC"].";<br />";
$HTMLBody=$HTMLBody . "Suma de <strong>".romanize($row["chitanta_suma_incasata"])." (". StrLei($ammount,'.',','). ")</strong><br /> 
Reprezentând: contravaloarea facturii ". $siteInvoicingCode ."Nr. ".$codenumarfactura."/".date("d.m.Y", strtotime($row["factura_data_emiterii"]))."  ";
$HTMLBody=$HTMLBody . "</font></td>";
$HTMLBody=$HTMLBody . "</tr><tr><td width=\"50%\">&nbsp;</td><td width=\"50%\" align=\"right\">$strCashier</td></tr>";
$HTMLBody=$HTMLBody . "</table>";
$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$invoice=$HTMLBody;

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $receipts_folder ."/Chitanta_". $siteInvoicingCode.$codenumarchitanta .'.pdf','F');
$receiptname='Chitanta_'. $siteInvoicingCode.'0000'. $row["chitanta_numar"] .'.pdf';

$strWhereClause = " WHERE facturare_chitante.chitanta_ID=" . $_GET["cID"] . ";";
$query= "UPDATE facturare_chitante SET facturare_chitante.chitanta_pdf='1' ," ;
$query= $query ." facturare_chitante.chitanta_anulat='1' ," ;
$query= $query . "facturare_chitante.chitanta_pdf_generat='" .$data . "' " ;
$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn,$query));
  }
echo "<div class=\"callout success\">Chitanța " . $siteInvoicingCode.$codenumarchitanta .".pdf a fost anulată. <a href=\"../common/opendoc.php?type=2&docID=$receiptname\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";
include '../bottom.php';
?>