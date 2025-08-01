<?php
//update 29.07.2025
error_reporting(E_ERROR | E_PARSE);
include '../settings.php';
include '../classes/common.php';

$strPageTitle="Citire  efactura";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">	
<a href="verify_messages.php?mode=verify" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i> <a/>			  
			 </div> 
			 </div> 
			  <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <?php
			  echo "<h1>$strPageTitle</h1>";
$filename=$_GET['cid']. '.zip';
$foldername=$_GET['cid'];
$filelocation=$hddpath .'/' . $efacturareceived_folder ."/".$filename;
$ziplocation=$hddpath .'/' . $efacturareceived_folder ."/".$foldername."/";

$zip = new ZipArchive;
$res = $zip->open($filelocation);
if ($res === TRUE) {
	echo "<div class=\"callout success\">$strFileExtracted:<br/>";
	for( $i = 0; $i < $zip->numFiles; $i++ ){ 
	$stat = $zip->statIndex( $i ); 
    print_r( basename( $stat['name'] ) . '<br />' ); 
	$zipfile=basename( $stat['name']);
	$result = file_get_contents('zip://'.$filelocation."#".$zipfile);
	}	 

  $zip->extractTo($ziplocation);
  $zip->close();
		echo "</div>";
} else {
	echo "<div class=\"callout alert\">";
  echo $strThereWasAnError;
  echo "</div>";
}

$files = scandir ($ziplocation);
$firstFile = $ziplocation . $files[2];// because [0] = "." [1] = ".." 
//echo $firstFile;

$xml=file_get_contents($firstFile) or die("Error: Cannot create object");
$result=xml2array($xml);

echo "<hr>";

$information=json_encode($result, true);
$obj = json_decode($information, true);

if (array_key_exists("Invoice",$obj))
{$invoice=$obj['Invoice'];
}
else
{$invoice=$obj['ubl:Invoice'];}

$newinvoice=removeLeftPartOfColonsFromArray($invoice);
$invoice=$newinvoice;

//general terms
$invoiceID=$invoice['ID'];
$typecode=$invoice['InvoiceTypeCode'];
$issuedate=$invoice['IssueDate'];
$duedate=$invoice['DueDate'];
If (empty($invoice['ContractDocumentReference']))
{$referenceID="fără contract";}
	Else
{$reference=$invoice['ContractDocumentReference'];
$referenceID=$reference['ID'];}

//supplier
$supplierMain=$invoice['AccountingSupplierParty'];
$supplierBranch=$supplierMain['Party'];
$supplierAddress=$supplierBranch['PostalAddress'];
$supplierstreet=$supplierAddress['StreetName'];
$suppliercity=$supplierAddress['CityName'];
$supplierReg=$supplierBranch['PartyLegalEntity'];
if (empty($supplierBranch['PartyTaxScheme']))
{$supplierTax="Neplătitor de TVA";
$supplierCIF=$supplierReg['CompanyID'];}
Else
{$supplierTax=$supplierBranch['PartyTaxScheme'];
$supplierCIF=$supplierTax['CompanyID'];}

$supplierName=$supplierReg['RegistrationName'];
if (empty($supplierReg['CompanyID']))
{$supplierREC=$supplierCIF;}
else
	{$supplierREC=$supplierReg['CompanyID'];}

//customer
$customerMain=$invoice['AccountingCustomerParty'];
$customerBranch=$customerMain['Party'];
$customerAddress=$customerBranch['PostalAddress'];
$customerstreet=$customerAddress['StreetName'];
$customercity=$customerAddress['CityName'];
if (empty($customerBranch['PartyTaxScheme']))
	{$customerTax="";
	$customerCIF="";}
Else
{$customerTax=$customerBranch['PartyTaxScheme'];
if (empty($customerTax['CompanyID']))
{$customerCIF="";}
else
{$customerCIF=$customerTax['CompanyID'];}}
$customerReg=$customerBranch['PartyLegalEntity'];
$customerName=$customerReg['RegistrationName'];
if (empty($customerReg['CompanyID']))
{$customerREC="";}
Else
{$customerREC=$customerReg['CompanyID'];
$customerCIF=$customerReg['CompanyID'];}

//totals
$totalMain=$invoice['TaxTotal'];
$totalTaxTotal=$totalMain['TaxAmount'];

$totalValues=$invoice['LegalMonetaryTotal'];
$totalinvoiceNet=$totalValues['LineExtensionAmount'];
$totalinvoiceBrut=$totalValues['TaxInclusiveAmount'];

//cont bancar
if (empty($invoice['PaymentMeans']))
{$supplierBankIBAN="fără cont bancar";}
Else
{$supplierBankDetails=$invoice['PaymentMeans'];
if (empty($supplierBankDetails['PayeeFinancialAccount']))
	{
		if(empty($supplierBankDetails[0]['PayeeFinancialAccount']))
		{$supplierBankIBAN="fără cont";}
	Else
	{		$bankdetails=$supplierBankDetails[0]['PayeeFinancialAccount'];
	$supplierBankIBAN=$bankdetails['ID'];
	}}
	Else
	{$bankdetails=$supplierBankDetails['PayeeFinancialAccount'];
	$supplierBankIBAN=$bankdetails['ID'];}
}
//invoicelines
$invoicelines=$invoice['InvoiceLine'];
$xmlinvoiceheader = "
<table border=\"0\" align=\"center\" width=\"100%\">
<tr>
<td td width=\"50%\">
<h3>Factura : $invoiceID</h3>
<h3>Data emiterii:<strong> $issuedate</strong></h3>
Tip: $typecode<br /></td>
<td td width=\"50%\">Scadența: <strong>$duedate</strong> <br />
Referința : $referenceID <br />
Cont bancar: <strong>$supplierBankIBAN</strong></td>
</tr>
<tr>
<td>
<h4>Furnizor</h4> 
<h3>$supplierName</h3>
CUI: <strong>$supplierCIF</strong><br />
Recom: <strong>$supplierREC</strong><br />
Adresa: $supplierstreet<br />
Oraș : $suppliercity</td>
<td>
<h4>Client</h4> 
<h3>$customerName</h3>
CUI: <strong>$customerCIF</strong><br />
Recom: <strong>$customerREC</strong><br />
Adresa: $customerstreet<br />
Oraș : $customercity</td>
</tr>
</table><br /><br />";
echo $xmlinvoiceheader;

//invoice taxes
$taxlines="<h3>Total taxe = ". $totalTaxTotal . " lei, din care:</h3>";

$totalsubtotal=$totalMain['TaxSubtotal'];

$count = count($totalsubtotal);
if ($count<>'5'){
foreach($totalsubtotal as $index => $value) {
$totaltaxammount=$value['TaxAmount'];
$totaltaxscheme=$value['TaxCategory'];
If (empty($totaltaxscheme['Percent']))
{$totaltaxPercent="";}
Else
{$totaltaxPercent=$totaltaxscheme['Percent'];}
$totaltaxcode=$totaltaxscheme['ID'];
$totaltaxtype=$totaltaxscheme['TaxScheme'];
$totaltaxname=$totaltaxtype['ID'];
$taxlines=$taxlines . "<h4>$totaltaxammount lei - $totaltaxPercent % - $totaltaxname Cod: $totaltaxcode</h4>";
}}
else
{
	$totaltaxammount=$totalsubtotal['TaxAmount'];
$totaltaxscheme=$totalsubtotal['TaxCategory'];
If (empty($totaltaxscheme['Percent']))
{$totaltaxPercent="";}
Else
{$totaltaxPercent=$totaltaxscheme['Percent'];}
$totaltaxcode=$totaltaxscheme['ID'];
$totaltaxtype=$totaltaxscheme['TaxScheme'];
$totaltaxname=$totaltaxtype['ID'];
$taxlines=$taxlines . "<h4>$totaltaxammount lei - $totaltaxPercent % - $totaltaxname Cod: $totaltaxcode</h4>";
}
echo $taxlines;
$xmlinvoicecontent= "
<table border=\"0\" align=\"center\" width=\"100%\">
<thead>
<th width=\"10%\" align=\"left\">Nr.</th>
<th width=\"50%\" align=\"left\">Articol</th>
<th width=\"10%\" align=\"right\">Cantitate</th>
<th width=\"10%\" align=\"right\">Preț</th>
<th width=\"10%\" align=\"right\">Total</th>
<th width=\"10%\" align=\"right\">Procent TVA</th>
</thead></tr>
<tbody>
";
echo $xmlinvoicecontent;
if (empty($invoicelines['ID'])) 
{
	$tableline="";
	foreach($invoicelines as $index => $value) {
		
	 $tableline = $tableline . "<tr>";
	 $tableline = $tableline .  "<td>" .$value['ID']. "</td>";
	 $tableline = $tableline .  "<td>".$value['Item']['Name'] ."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['LineExtensionAmount']."</td>";
	 if (empty($value['Item']['ClassifiedTaxCategory']['Percent']))
		  {	 $tableline = $tableline .  "<td align=\"right\">-</td>";}
		 Else
		 {	 $tableline = $tableline .  "<td align=\"right\">".$value['Item']['ClassifiedTaxCategory']['Percent']."</td>";}
	 $tableline = $tableline .  "</tr>";
}}
else
	{
		$tableline="";
    $tableline = $tableline .  "<tr>";
	 $tableline = $tableline .  "<td>" .$invoicelines['ID']. "</td>";
	 $tableline = $tableline .  "<td>".$invoicelines['Item']['Name'] ."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['LineExtensionAmount']."</td>";
 if (empty($invoicelines['Item']['ClassifiedTaxCategory']['Percent']))
		  {	 $tableline = $tableline .  "<td align=\"right\">-</td>";}
		 Else
		 {	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['Item']['ClassifiedTaxCategory']['Percent']."</td>";}
	 $tableline = $tableline .  "</tr>";
}
	 echo $tableline;
$invoicefoot = "
<tr>
<td colspan=\"5\" >Valoare TVA</td><td align=\"right\"> $totalTaxTotal</td>
<tr>
<td  colspan=\"5\"><h3>Valoare netă factură</h3></td> <td align=\"right\">$totalinvoiceNet</td>
<tr>
<td  colspan=\"5\"><h3>Valoare totală factură</h3></td> <td align=\"right\">$totalinvoiceBrut</td>
</tr>
</tbody>
<tfoot>
<tr><td colspan=\"7\">&nbsp;</td></tr>
</tfoot>

</table>
";
echo $invoicefoot;


echo "</div></div>";

echo "			  <div class=\"grid-x grid-margin-x\">
			  <div class=\"large-12 medium-12 small-12 cell\">";
	
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
$mpdf = new \Mpdf\Mpdf([
	'margin_left' => 20,
	'margin_right' => 10,
	'margin_top' => 20,
	'margin_bottom' => 20,
	'margin_header' => 10,
	'margin_footer' => 20,
	'showBarcodeNumbers' => true
]);
$mpdf->SetTitle($strInvoice . " ". $invoiceID);
$mpdf->SetAuthor($siteCompanyLegalName);
$mpdf->SetKeywords('factură, factura, invoice');	

$HTMLBody="<html>";
$HTMLBody=$HTMLBody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px;font-size: 12px;font-family: font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 0px;}";
$HTMLBody=$HTMLBody . "td {font-size: 10px; font-family: 'Open Sans',sans-serif; COLOR: #000000; padding: 3px;  font-weight: normal;}";
$HTMLBody=$HTMLBody . "th {font-size: 12px; font-family: 'Open Sans',sans-serif; COLOR: #ffffff; background-color: " . $color ."; padding: 3px; font-weight: normal;}";
$HTMLBody=$HTMLBody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$HTMLBody=$HTMLBody . "table,IMG,A {BORDER: 0px;}";
$HTMLBody=$HTMLBody . "table {border-collapse:collapse;}";
$HTMLBody=$HTMLBody . ".barcode {padding: 1.5mm; margin: 0;	vertical-align: top; color: " . $color ."; } .barcodecell {text-align: center;	vertical-align: middle;	padding: 0;}";
$HTMLBody=$HTMLBody . "</style>";
$HTMLBody=$HTMLBody . "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$HTMLBody=$HTMLBody . "</head><body>";
$HTMLBody=$HTMLBody . $xmlinvoiceheader;
$HTMLBody=$HTMLBody . $taxlines;
$HTMLBody=$HTMLBody . $xmlinvoicecontent;
$HTMLBody=$HTMLBody . $tableline;
$HTMLBody=$HTMLBody . $invoicefoot;
$HTMLBody=$HTMLBody . "</body>";
$HTMLBody=$HTMLBody . "</html>";
$invoice=$HTMLBody;

$mpdf->WriteHTML($invoice);
$mpdf->Output($hddpath ."/" . $receivedeinvoices ."\Factura_". sanitarization($invoiceID)."_" . sanitarization($customerCIF) .'.pdf','F');
$invoicename='Factura_'. sanitarization($invoiceID)."_" . sanitarization($customerCIF) . '.pdf';
echo "<div class=\"callout success\">Factura_". $invoiceID."_" .sanitarization($customerCIF)  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=3&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";

$indexdownload=$_GET['cid'];
$sql="SELECT * FROM facturare_facturi_primite WHERE fp_index_download='$indexdownload'";
$result=ezpub_query($conn,$sql);
if (ezpub_num_rows($result)>0)
{
echo "<div class=\"callout alert\">$strReceivedInvoiceAlreadyRegistered</div></div></div>" ;
}
else 
	{# code...}
$mSQL = "INSERT INTO facturare_facturi_primite(";
	$mSQL = $mSQL . "fp_nume_furnizor,";
	$mSQL = $mSQL . "fp_numar_factura,";
	$mSQL = $mSQL . "fp_adresa_furnizor,";
	$mSQL = $mSQL . "fp_oras_furnizor,";
	$mSQL = $mSQL . "fp_CUI_furnizor,";
	$mSQL = $mSQL . "fp_RC_furnizor,";
	$mSQL = $mSQL . "fp_valoare_neta,";
	$mSQL = $mSQL . "fp_valoare_totala,";
	$mSQL = $mSQL . "fp_valoare_TVA,";
	$mSQL = $mSQL . "fp_data_emiterii,";
	$mSQL = $mSQL . "fp_data_scadenta,";
	$mSQL = $mSQL . "fp_index_download,";
	$mSQL = $mSQL . "fp_achitat)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" . $supplierName. "', ";
	$mSQL = $mSQL . "'" . $invoiceID. "', ";
	$mSQL = $mSQL . "'" . $supplierAddress	. "', ";
	$mSQL = $mSQL . "'" . $suppliercity	. "', ";
	$mSQL = $mSQL . "'" .  $supplierCIF. "', ";
	$mSQL = $mSQL . "'" .  $supplierREC. "', ";
	$mSQL = $mSQL . "'" .  $totalinvoiceNet . "', ";
	$mSQL = $mSQL . "'" . $totalinvoiceBrut . "', ";
	$mSQL = $mSQL . "'" . $totalTaxTotal . "', ";
	$mSQL = $mSQL . "'" . $issuedate . "', ";
	$mSQL = $mSQL . "'" . $duedate  . "', ";
	$mSQL = $mSQL . "'" . $indexdownload  . "', ";
	$mSQL = $mSQL . "'0') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	
echo "<div class=\"callout success\">$strReceivedInvoiceRegistered</div></div></div>" ;
}
}
echo "</div></div>";
include '../bottom.php';?>