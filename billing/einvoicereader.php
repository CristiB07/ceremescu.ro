<?php
error_reporting(E_ERROR | E_PARSE);
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { 
    session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

// Validare parametru cid
if (!isset($_GET['cid']) || empty($_GET['cid'])) {
    header("location:$strSiteURL/billing/receivedeinvoices.php?message=ER");
    die;
}
$cid = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['cid']);
if (empty($cid)) {
    header("location:$strSiteURL/billing/receivedeinvoices.php?message=ER");
    die;
}

$strPageTitle="Citire  efactura";
include '../dashboard/header.php';

?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <a href="verifymessages.php?mode=verify" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i>
</a>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
			  echo "<h1>$strPageTitle</h1>";
$filename=$cid. '.zip';
$foldername=$cid;
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
	else
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
else
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
else
{$customerTax=$customerBranch['PartyTaxScheme'];
if (empty($customerTax['CompanyID']))
{$customerCIF="";}
else
{$customerCIF=$customerTax['CompanyID'];}}
$customerReg=$customerBranch['PartyLegalEntity'];
$customerName=$customerReg['RegistrationName'];
if (empty($customerReg['CompanyID']))
{$customerREC="";}
else
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
else
{$supplierBankDetails=$invoice['PaymentMeans'];
if (empty($supplierBankDetails['PayeeFinancialAccount']))
	{
		if(empty($supplierBankDetails[0]['PayeeFinancialAccount']))
		{$supplierBankIBAN="fără cont";}
	else
	{		$bankdetails=$supplierBankDetails[0]['PayeeFinancialAccount'];
	$supplierBankIBAN=$bankdetails['ID'];
	}}
	else
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
else
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
else
{$totaltaxPercent=$totaltaxscheme['Percent'];}
$totaltaxcode=$totaltaxscheme['ID'];
$totaltaxtype=$totaltaxscheme['TaxScheme'];
$totaltaxname=$totaltaxtype['ID'];
$taxlines=$taxlines . "<h4>$totaltaxammount lei - $totaltaxPercent % - $totaltaxname Cod: $totaltaxcode</h4>";
}
echo $taxlines;
$indexdownload=$cid;
$stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi_primite WHERE fp_index_download=?");
mysqli_stmt_bind_param($stmt, "s", $indexdownload);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result)>0)
{
echo "<div class=\"callout alert\">$strReceivedInvoiceAlreadyRegistered</div></div></div>" ;
}
else 
	{ if ($duedate=='')
		{
			$duedate=$issuedate;
		}
		else
		{
			$duedate=$duedate;
		}
$stmt_insert = mysqli_prepare($conn, "INSERT INTO facturare_facturi_primite(fp_nume_furnizor, fp_numar_factura, fp_adresa_furnizor, fp_oras_furnizor, fp_CUI_furnizor, fp_RC_furnizor, fp_valoare_neta, fp_valoare_totala, fp_valoare_TVA, fp_data_emiterii, fp_data_scadenta, fp_index_download, fp_achitat) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$achitat = '0';
mysqli_stmt_bind_param($stmt_insert, "sssssssssssss", $supplierName, $invoiceID, $supplierstreet, $suppliercity, $supplierCIF, $supplierREC, $totalinvoiceNet, $totalinvoiceBrut, $totalTaxTotal, $issuedate, $duedate, $indexdownload, $achitat);
		
//It executes the SQL
if (!mysqli_stmt_execute($stmt_insert))
  {
  die('Error: ' . mysqli_stmt_error($stmt_insert));
  }
else{
	mysqli_stmt_close($stmt_insert);
	$facturaID=mysqli_insert_id($conn);
echo "<div class=\"callout success\">$strReceivedInvoiceRegistered</div>" ;
}
}
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
//echo json_encode($invoicelines);
if ($invoicelines['ID']=='0')
{
	$invoicelines['ID']='1';}
if (empty($invoicelines['ID'])) 
{ //there are more than one line
	$tableline="";
	$indexdownload=$cid;
$stmt2 = mysqli_prepare($conn, "SELECT * FROM facturare_articole_facturi_primite WHERE index_download=?");
mysqli_stmt_bind_param($stmt2, "s", $indexdownload);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);
if (mysqli_num_rows($result)>0)
{// invoice registered, products registere
echo "<div class=\"callout alert\">$strSkipped<br />$strReceivedInvoiceAlreadyRegistered</div>" ;
foreach($invoicelines as $index => $value) {
	if (empty($value['ID'])&&empty($value['Item']['Name'])&&empty($value['Price']['PriceAmount']))
	{echo "";}	
	else	{
	 $tableline = $tableline . "<tr>";
	 $tableline = $tableline .  "<td>" .$value['ID']. "</td>";
	 $tableline = $tableline .  "<td>".htmlentities($value['Item']['Name']) ." - ".htmlentities($value['Item']['Description'])."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['LineExtensionAmount']."</td>";
	 if (empty($value['Item']['ClassifiedTaxCategory']['Percent']))
		  {	$procentTVA=0;}
		 else
		 { $procentTVA=$value['Item']['ClassifiedTaxCategory']['Percent'];}
		 $tableline = $tableline .  "<td align=\"right\">$procentTVA</td>";
	 	$tableline = $tableline .  "</tr>";}
	} // end multiple lines with invoice registered
	}//ends emptyline
else 
	{ //invoice wasn't registered, register products

	foreach($invoicelines as $index => $value) {
		if (empty($value['ID'])&&empty($value['Item']['Name'])&&empty($value['Price']['PriceAmount']))
	{echo "";}	
	else	{	
	 $tableline = $tableline . "<tr>";
	 $tableline = $tableline .  "<td>" .$value['ID']. "</td>";
	 $tableline = $tableline .  "<td>".htmlentities($value['Item']['Name']) ." - ".htmlentities($value['Item']['Description'])."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['LineExtensionAmount']."</td>";
	  if (empty($value['Item']['ClassifiedTaxCategory']['Percent']))
	
		  {	$procentTVA=0;}
		 else
		 { $procentTVA=$value['Item']['ClassifiedTaxCategory']['Percent'];}
		 $tableline = $tableline .  "<td align=\"right\">$procentTVA</td>";
	 	$tableline = $tableline .  "</tr>";
			$numearticol=htmlentities($value['Item']['Name']) ." - ".htmlentities($value['Item']['Description']);
		$unitatearticol="buc";
		$cantitatearticol=$value['InvoicedQuantity'];
		$pretarticol=$value['Price']['PriceAmount'];
		$valoaretotala=$value['LineExtensionAmount'];
		$valoareTVA=$valoaretotala * $procentTVA / 100;
		if (empty($facturaID) or !isset($facturaID))
		{
			$stmt_fid = mysqli_prepare($conn, "SELECT * FROM facturare_facturi_primite WHERE fp_index_download=?");
			mysqli_stmt_bind_param($stmt_fid, "s", $indexdownload);
			mysqli_stmt_execute($stmt_fid);
			$result_fid = mysqli_stmt_get_result($stmt_fid);
			$row=mysqli_fetch_array($result_fid, MYSQLI_ASSOC);
			$facturaID=$row['fp_id'];
			mysqli_stmt_close($stmt_fid);
		}
$stmt_art = mysqli_prepare($conn, "INSERT INTO facturare_articole_facturi_primite(articolFP_nume, articolFP_unitate, articolFP_cantitate, articolFP_pret, articolFP_procent_TVA, articolFP_valoare, articolFP_TVA, index_download, factura_ID) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_art, "ssssssssi", $numearticol, $unitatearticol, $cantitatearticol, $pretarticol, $procentTVA, $valoaretotala, $valoareTVA, $indexdownload, $facturaID);
		
//It executes the SQL
if (!mysqli_stmt_execute($stmt_art))
  {
  die('Error: ' . mysqli_stmt_error($stmt_art));
  }
else{
echo "<div class=\"callout success\">$strReceivedInvoiceArticlesRegistered</div></div></div>" ;}
} //ends empty line
}//we registered products
}	// ends invoice not registered, products registered
	} // ends multiple lines invoice
else
	{//there is only one line in invoice
		$tableline="";
    $tableline = $tableline .  "<tr>";
	 $tableline = $tableline .  "<td>" .$invoicelines['ID']. "</td>";
	 $tableline = $tableline .  "<td>".$invoicelines['Item']['Name'] ." - ".$invoicelines['Item']['Description']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['LineExtensionAmount']."</td>";
 if (empty($invoicelines['Item']['ClassifiedTaxCategory']['Percent']))
	
		  {	$procentTVA=0;}
		 else
		 { $procentTVA=$invoicelines['Item']['ClassifiedTaxCategory']['Percent'];}
		 $tableline = $tableline .  "<td align=\"right\">$procentTVA</td>";
	 $tableline = $tableline .  "</tr>";}
$indexdownload=$cid;
$stmt3 = mysqli_prepare($conn, "SELECT * FROM facturare_articole_facturi_primite WHERE index_download=?");
mysqli_stmt_bind_param($stmt3, "s", $indexdownload);
mysqli_stmt_execute($stmt3);
$result = mysqli_stmt_get_result($stmt3);
if (mysqli_num_rows($result)>0)
{
echo "<div class=\"callout alert\">$strSkipped<br />$strReceivedInvoiceAlreadyRegistered</div>" ;
}
else 
	{
		if (empty($facturaID) or !isset($facturaID))
		{
			$stmt_fid2 = mysqli_prepare($conn, "SELECT * FROM facturare_facturi_primite WHERE fp_index_download=?");
			mysqli_stmt_bind_param($stmt_fid2, "s", $indexdownload);
			mysqli_stmt_execute($stmt_fid2);
			$result_fid2 = mysqli_stmt_get_result($stmt_fid2);
			$row=mysqli_fetch_array($result_fid2, MYSQLI_ASSOC);
			$facturaID=$row['fp_id'];
			mysqli_stmt_close($stmt_fid2);
		}

		$numearticol=htmlentities($invoicelines['Item']['Name'])." - ".htmlentities($invoicelines['Item']['Description']);
		$unitatearticol="buc";
		$cantitatearticol=$invoicelines['InvoicedQuantity'];
		$pretarticol=$invoicelines['Price']['PriceAmount'];
		$valoaretotala=$invoicelines['LineExtensionAmount'];
		$valoareTVA=$valoaretotala * $procentTVA / 100;
$stmt_art2 = mysqli_prepare($conn, "INSERT INTO facturare_articole_facturi_primite(articolFP_nume, articolFP_unitate, articolFP_cantitate, articolFP_pret, articolFP_procent_TVA, articolFP_valoare, articolFP_TVA, index_download, factura_ID) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_art2, "ssssssssi", $numearticol, $unitatearticol, $cantitatearticol, $pretarticol, $procentTVA, $valoaretotala, $valoareTVA, $indexdownload, $facturaID);
			
//It executes the SQL
if (!mysqli_stmt_execute($stmt_art2))
  {
  die('Error: ' . mysqli_stmt_error($stmt_art2));
  }
else{
	mysqli_stmt_close($stmt_art2);
	$facturaID=mysqli_insert_id($conn);
echo "<div class=\"callout success\">$strReceivedInvoiceArticlesRegistered</div>" ;
} // we registered products
} //ends single line invoice	 
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
$mpdf->Output($hddpath ."/" . $receivedeinvoices ."/Factura_". sanitarization($invoiceID)."_" . sanitarization($customerCIF) .'.pdf','F');
$invoicename='Factura_'. sanitarization($invoiceID)."_" . sanitarization($customerCIF) . '.pdf';
echo "<div class=\"callout success\">Factura_". $invoiceID."_" .sanitarization($customerCIF)  .".pdf a fost generată. <a href=\"../common/opendoc.php?type=3&docID=$invoicename\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"far fa-file-pdf\"></i></a></div>";

echo "</div></div>";
include '../bottom.php';?>