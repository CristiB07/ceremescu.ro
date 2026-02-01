<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare mesaje";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}
include '../dashboard/header.php';

$d = date("d-m-Y ");
$dataincarcarii = date("Y-m-d H:i:s");
//$s = date('d-m-Y', strtotime($d . ' +10 day'));
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";

?>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
// Validate mode parameter
if (!isset($_GET["mode"]) || $_GET["mode"] !== 'verify') {
    if (!isset($_GET["mode"]) || !in_array($_GET["mode"], ['verify', 'dowload'])) {
        die("Invalid mode parameter");
    }
}
if ($_GET["mode"]=='verify')
{
	?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <div class="paginate">
                    <a href="verifymessages.php?mode=verify" class="paginate"><?php echo $strShowAll?></a>
                    <a href="verifymessages.php?mode=verify&display=FP" class="paginate"><i class="fas fa-file-import"></i> <?php echo $strReceivedInvoices?></a>
                    <a href="verifymessages.php?mode=verify&display=ER" class="paginate"><i class="fas fa-exclamation-circle"></i> <?php echo $strErrors?></a>
                    <a href="verifymessages.php?mode=verify&display=FT" class="paginate"><i class="fas fa-file-export"></i> <?php echo $strSentInvoices?></a>
                </div>
            </div>
        </div>
        <?php
		$retval=array();
		$messages_url=$messages_url.$siteCIF;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$site_client_token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$messages_url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$response = curl_exec($ch);
	    if (PHP_VERSION_ID < 80500) { curl_close($ch); }
	$obj = json_decode($response, true);

if (isset ($obj['eroare']))
{ echo "<div class=\"callout alert\">$strNoRecordsFound</div>" ;}
else {
$mesaje=$obj['mesaje'];

if (!isset($_GET['display']))
{$result=$mesaje;}
else
{
// Validate display parameter
if (!in_array($_GET['display'], ['FP', 'ER', 'FT'])) {
    die("Invalid display parameter");
}
$filtru=$_GET['display'];
if ($filtru=='FP') {$cautare='FACTURA PRIMITA';}
elseif ($filtru=='ER') {$cautare='ERORI FACTURA';}
else {$cautare="FACTURA TRIMISA";}

$result = array_filter($mesaje, function($element) use ($cautare) {
    return $element['tip'] == $cautare;
});
}
array_multisort(array_column($result, 'data_creare'), SORT_DESC, $result);


echo "<table class=\"hover\">
<thead>
<tr>
<th width=\"10%\">$strUploadDate</th>
<th width=\"15%\">$strCompanyName</th>
<th width=\"40%\">$strDetails</th>
<th width=\"5%\">$strType</th>
<th width=\"10%\">$strDownloadIndex</th>
<th width=\"10%\">$strDownloadDate</th>
<th width=\"5%\">$strDownload</th>
<th width=\"5%\">$strView</th>
</tr>
</thead>
<tbody>";
 foreach($result as $index => $value) {
		 if ($value['tip']=='FACTURA PRIMITA') {$tip='FP';}
	 if ($value['tip']=='FACTURA TRIMISA') {$tip='FT';}
	 if ($value['tip']=='ERORI FACTURA') {$tip='ER';}
	 if ($tip=='FP') {$tipicon='<i class="fas fa-file-import fa-xl"></i>';}
	 elseif ($tip=='FT') {$tipicon='<i class="fas fa-file-export fa-xl"></i>';}
	 elseif ($tip=='ER') {$tipicon='<i class="fas fa-exclamation-circle fa-xl"></i>';}
	 $pieces = explode(" ", $value['detalii']);
	 if ($value['tip']=='FACTURA PRIMITA' OR $value['tip']=='FACTURA TRIMISA')
	 { $indexdexincarcare=$pieces[2];}
 elseif($value['tip']=='ERORI FACTURA')
 { $indexdexincarcare=$pieces[8];}
	 $indexi=substr($indexdexincarcare, strpos($indexdexincarcare, "=") + 1);
	 if ($value['tip']=='FACTURA PRIMITA')
	 {	 $cifemitent=$pieces[5];}
 elseif ($value['tip']=='FACTURA TRIMISA')
 {	 $cifemitent=$pieces[7];}
 elseif ($value['tip']=='ERORI FACTURA')
 {$cifemitent='=0';}
 
$whatIWant = substr($cifemitent, strpos($cifemitent, "=") + 1);
$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_mesaje WHERE message_id_solicitare = ?");
mysqli_stmt_bind_param($stmt, "s", $value['id_solicitare']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$RS = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!isSet($RS))
{
	$stmt = mysqli_prepare($conn, "INSERT INTO efactura_mesaje(message_datacreare, message_cif, message_id_solicitare, message_detalii, message_tip, message_downloadid) VALUES (?, ?, ?, ?, ?, ?)");
	mysqli_stmt_bind_param($stmt, "ssssss", $value['data_creare'], $value['cif'], $value['id_solicitare'], $value['detalii'], $value['tip'], $value['id']);
	if (!mysqli_stmt_execute($stmt)) {
		die('Error: ' . mysqli_error($conn));
	}
	mysqli_stmt_close($stmt);
}

			if ($value['tip']=='FACTURA PRIMITA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_primite WHERE efactura_primita_CUI = ? AND efactura_primita_index = ?");
			mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
		} elseif ($value['tip']=='FACTURA TRIMISA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura WHERE factura_CIF = ? AND factura_index_descarcare = ?");
			mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
		} elseif ($value['tip']=='ERORI FACTURA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_erori WHERE index_incarcare = ? AND index_descarcare = ?");
			mysqli_stmt_bind_param($stmt, "ss", $indexi, $value['id']);
		}
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		if (!$row) 
{
	 echo "<tr class=\"notdownloaded\">";
}
else
{ echo "<tr class=\"downloaded\">";}
print "<td>"  . date("d.m.Y H:m", strtotime($value['data_creare']))."</td>";
if (!$row)
{print "<td><strong><a href=\"verifysupplier.php?cui=".urlencode($whatIWant)."\">".htmlspecialchars($whatIWant, ENT_QUOTES, 'UTF-8')."</strong></a></td>";}
else
{
			if ($value['tip']=='FACTURA PRIMITA') {
            $stmt = mysqli_prepare($conn, "SELECT * FROM facturare_facturi_primite WHERE REGEXP_REPLACE(fp_CUI_furnizor, '[^0-9]', '') = ? AND fp_index_download = ?");
            mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
            mysqli_stmt_execute($stmt);
            $fpresult = mysqli_stmt_get_result($stmt);
            $fprow = mysqli_fetch_assoc($fpresult);
            mysqli_stmt_close($stmt);
print"<td>".$fprow["fp_nume_furnizor"]."</td>";
		} 
		elseif 
		($value['tip']=='FACTURA TRIMISA') {
			$stmt = mysqli_prepare($conn, "SELECT facturare_facturi.factura_ID, facturare_facturi.factura_client_denumire, facturare_facturi.factura_client_CIF, efactura.factura_CIF, efactura.factura_index_descarcare FROM facturare_facturi, efactura  WHERE efactura.factura_CIF = ? AND efactura.factura_index_descarcare = ? 
			AND facturare_facturi.factura_ID = efactura.factura_ID AND facturare_facturi.factura_client_CIF = efactura.factura_CIF");
			mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
			mysqli_stmt_execute($stmt);
			$ftresult = mysqli_stmt_get_result($stmt);
			$ftrow = mysqli_fetch_assoc($ftresult);
			mysqli_stmt_close($stmt);
print"<td>".$ftrow["factura_client_denumire"]."</td>";
}
elseif 
		($value['tip']=='ERORI FACTURA') {
print"<td>00000000</td>";
}
}
print "<td>".htmlspecialchars($value['detalii'], ENT_QUOTES, 'UTF-8')."</td>";
print "<td align=\"center\">".$tipicon."</td>";
print "<td>".htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')."</td>";
if (!$row) 
{
print "
<td>&nbsp;</td>
<td align=\"center\">
<a href=\"verifymessages.php?type=".urlencode($tip)."&mode=dowload&cid=".urlencode($value['id'])."&cif=".urlencode($whatIWant)."&datap=".urlencode($value['data_creare'])."&idi=".urlencode($indexi)."\">
<i class=\"fa-xl fas fa-file-download\" title=\"".htmlspecialchars($strDownload, ENT_QUOTES, 'UTF-8')."\"></i>
</a>
</td>";
}
else
{
	if ($value['tip']=='FACTURA PRIMITA')
	{$datadescarcarii=date("d.m.Y H:m", strtotime($row['efactura_primita_datad'] ?? ''));}
	elseif ($value['tip']=='FACTURA TRIMISA')
	{if (!$row['factura_data_descarcarii'])
		{		$datadescarcarii='';}
	else
		{		$datadescarcarii=date("d.m.Y H:m", strtotime($row['factura_data_descarcarii']) ?? '');}
		}
	elseif ($value['tip']=='ERORI FACTURA')

	{
		{if (!$row['data_descarcare'])
		{		$datadescarcarii='';}
	else
		{		$datadescarcarii=date("d.m.Y H:m", strtotime($row['data_descarcare']) ?? '');}
		}
	}
	print "<td>$datadescarcarii</td>";
print "<td align=\"center\"><i class=\"fa-xl far fa-file-archive\" title=\"$strArchived\"></i></td>";

}
if ($value['tip']=='FACTURA PRIMITA')
{
?>
        <div class="full reveal" id="exampleModal1_<?php echo htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')?>" data-reveal>
            <iframe src="viewinvoice.php?type=0&option=show&cID=<?php echo urlencode($value['id'])?>" frameborder="0" style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button"><span aria-hidden="true">&times;</span></button>
        </div>
        <td align="center"><i class="fa-xl fas fa-search" title="<?php echo $strView?>" data-open="exampleModal1_<?php echo $value['id']?>"></i></td>
        <?php }
elseif ($value['tip']=='FACTURA TRIMISA')
				{
					$query="SELECT * FROM efactura WHERE factura_CIF='$whatIWant' AND factura_index_descarcare='$value[id]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <div class="full reveal" id="exampleModal3_<?php echo htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')?>" data-reveal>
            <iframe src="viewinvoice.php?type=1&option=show&cID=<?php echo urlencode($row['factura_ID'])?>" frameborder="0"  style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button"><span aria-hidden="true">&times;</span></button>
        </div>
        <td align="center"><i class="fa-xl fas fa-search" title="<?php echo $strView?>" data-open="exampleModal3_<?php echo $value['id']?>"></i></td>
        <?php
				}
elseif ($value['tip']=='ERORI FACTURA')
				{
?>
        <div class="full reveal" id="exampleModal2_<?php echo htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')?>" data-reveal>
            <iframe src="einvoiceerrors.php?cID=<?php echo urlencode($value['id'])?>" frameborder="0" style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button"><span aria-hidden="true">&times;</span></button>
        </div>
        <td align="center"><i class="fa-xl fas fa-search" title="<?php echo $strView?>" data-open="exampleModal2_<?php echo $value['id']?>"></i></td>
        <?php 
				}
print "</tr>";}
print "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
curl_close ($ch);
}}
else
{
// Validate all download parameters
if (!isset($_GET['type']) || !in_array($_GET['type'], ['FP', 'FT', 'ER'])) {
    die("Invalid type parameter");
}
if (!isset($_GET['cid']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['cid'])) {
    die("Invalid cid parameter");
}
if (!isset($_GET['cif']) || !preg_match('/^[0-9]+$/', $_GET['cif'])) {
    die("Invalid cif parameter");
}
if (!isset($_GET['idi']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['idi'])) {
    die("Invalid idi parameter");
}
if (!isset($_GET['datap']) || !strtotime($_GET['datap'])) {
    die("Invalid datap parameter");
}

$type=$_GET['type'];
$iddescarcare=$_GET["cid"];	
$cifemitent=$_GET["cif"];
$index=$_GET["idi"];
$datap=date("Y-m-d H:m", strtotime($_GET['datap']));
$datad=date("Y-m-d H:m");
	
	$retval=array();
		$download_url=$download_url.$iddescarcare;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$site_client_token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$download_url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		$datafile=$server_output;
		if ($type=='FP')
		{$fp = fopen($hddpath .'/' . $efacturareceived_folder .'/'.$iddescarcare.'.zip', 'w');}
		elseif ($type=='FT')
		{$fp = fopen($hddpath .'/' . $efactura_folder .'/'.$iddescarcare.'.zip', 'w');}
		elseif ($type=='ER')
		{$fp = fopen($hddpath .'/' . $error_folder .'/'.$iddescarcare.'.zip', 'w');}	
	
fwrite($fp, $datafile);

		if ($type=='FP')
	{
		$stmt = mysqli_prepare($conn, "INSERT INTO efactura_primite(efactura_primita_CUI, efactura_primita_index, efactura_primita_download, efactura_primita_datap, efactura_primita_datad) VALUES (?, ?, 'DA', ?, ?)");
		mysqli_stmt_bind_param($stmt, "ssss", $cifemitent, $iddescarcare, $datap, $datad);
		if (!mysqli_stmt_execute($stmt)) {
			die('Error: ' . mysqli_error($conn));
		}
		mysqli_stmt_close($stmt);
		//Insert invoice into received invoices table
		?>
		
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
			  echo "<h1>$strPageTitle</h1>";
$filename=$iddescarcare. '.zip';
$foldername=$iddescarcare;
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
if (empty($invoice['ContractDocumentReference']))
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
if (empty($totaltaxscheme['Percent']))
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
if (empty($totaltaxscheme['Percent']))
{$totaltaxPercent="";}
else
{$totaltaxPercent=$totaltaxscheme['Percent'];}
$totaltaxcode=$totaltaxscheme['ID'];
$totaltaxtype=$totaltaxscheme['TaxScheme'];
$totaltaxname=$totaltaxtype['ID'];
$taxlines=$taxlines . "<h4>$totaltaxammount lei - $totaltaxPercent % - $totaltaxname Cod: $totaltaxcode</h4>";
}
echo $taxlines;
$indexdownload=$iddescarcare;
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
	$indexdownload=$iddescarcare;
// Always try to register articles with INSERT IGNORE
foreach($invoicelines as $index => $value) {
	if (empty($value['ID'])&&empty($value['Item']['Name'])&&empty($value['Price']['PriceAmount']))
	{echo "";}	
	else	{	
	 $tableline = $tableline . "<tr>";
	 $tableline = $tableline .  "<td>" .$value['ID']. "</td>";
	 $name = is_array($value['Item']['Name']) ? implode(' ', $value['Item']['Name']) : $value['Item']['Name'];
	 $desc = is_array($value['Item']['Description']) ? implode(' ', $value['Item']['Description']) : $value['Item']['Description'];
	 $tableline = $tableline .  "<td>".htmlentities($name) ." - ".htmlentities($desc)."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$value['LineExtensionAmount']."</td>";
	  if (empty($value['Item']['ClassifiedTaxCategory']['Percent']))
	
		  {	$procentTVA=0;}
		 else
		 { $procentTVA=$value['Item']['ClassifiedTaxCategory']['Percent'];}
		 $tableline = $tableline .  "<td align=\"right\">$procentTVA</td>";
	 	$tableline = $tableline .  "</tr>";
			$numearticol=$value['Item']['Name'] ." - ".$value['Item']['Description'];
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
$stmt_art = mysqli_prepare($conn, "INSERT IGNORE INTO facturare_articole_facturi_primite(articolFP_nume, articolFP_unitate, articolFP_cantitate, articolFP_pret, articolFP_procent_TVA, articolFP_valoare, articolFP_TVA, index_download, factura_ID) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_art, "ssssssssi", $numearticol, $unitatearticol, $cantitatearticol, $pretarticol, $procentTVA, $valoaretotala, $valoareTVA, $indexdownload, $facturaID);
		
//It executes the SQL
if (!mysqli_stmt_execute($stmt_art))
  {
  die('Error: ' . mysqli_stmt_error($stmt_art));
  }
else{
echo "<div class=\"callout success\">$strReceivedInvoiceArticlesRegistered</div></div></div>" ;}
}
}
}
else
	{//there is only one line in invoice
		$tableline="";
    $tableline = $tableline .  "<tr>";
	 $tableline = $tableline .  "<td>" .$invoicelines['ID']. "</td>";
	 $name = is_array($invoicelines['Item']['Name']) ? implode(' ', $invoicelines['Item']['Name']) : $invoicelines['Item']['Name'];
	 $desc = is_array($invoicelines['Item']['Description']) ? implode(' ', $invoicelines['Item']['Description']) : $invoicelines['Item']['Description'];
	 $tableline = $tableline .  "<td>".$name ." - ".$desc."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['InvoicedQuantity']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['Price']['PriceAmount']."</td>";
	 $tableline = $tableline .  "<td align=\"right\">".$invoicelines['LineExtensionAmount']."</td>";
 if (empty($invoicelines['Item']['ClassifiedTaxCategory']['Percent']))
	
		  {	$procentTVA=0;}
		 else
		 { $procentTVA=$invoicelines['Item']['ClassifiedTaxCategory']['Percent'];}
		 $tableline = $tableline .  "<td align=\"right\">$procentTVA</td>";
	 $tableline = $tableline .  "</tr>";
$indexdownload=$iddescarcare;
// Always try to register the single article with INSERT IGNORE
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

		$numearticol=$name." - ".$desc;
		$unitatearticol="buc";
		$cantitatearticol=$invoicelines['InvoicedQuantity'];
		$pretarticol=$invoicelines['Price']['PriceAmount'];
		$valoaretotala=$invoicelines['LineExtensionAmount'];
		$valoareTVA=$valoaretotala * $procentTVA / 100;
$stmt_art2 = mysqli_prepare($conn, "INSERT IGNORE INTO facturare_articole_facturi_primite(articolFP_nume, articolFP_unitate, articolFP_cantitate, articolFP_pret, articolFP_procent_TVA, articolFP_valoare, articolFP_TVA, index_download, factura_ID) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
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

}
	if ($type=='ER')
	{
		$stmt = mysqli_prepare($conn, "INSERT INTO efactura_erori(data_erorii, index_incarcare, index_descarcare, status, data_descarcare) VALUES (?, ?, ?, 'DA', ?)");
		mysqli_stmt_bind_param($stmt, "ssss", $datap, $index, $iddescarcare, $datad);
		if (!mysqli_stmt_execute($stmt)) {
			die('Error: ' . mysqli_error($conn));
		}
		mysqli_stmt_close($stmt);
		//Show the error message
		$filename=$iddescarcare. '.zip';
$foldername=$iddescarcare;
$filelocation=$hddpath .'/' . $error_folder ."/".$filename;
$ziplocation=$hddpath .'/' . $error_folder ."/".$foldername."/";

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

$information=json_encode($result, true);
$obj = json_decode($information, true);

echo "<hr>";

// Extrage indexul de încărcare și mesajul de eroare
$index_incarcare = isset($result['header_attr']['Index_incarcare']) ? $result['header_attr']['Index_incarcare'] : '';
$error_message = isset($result['header']['Error_attr']['errorMessage']) ? $result['header']['Error_attr']['errorMessage'] : '';

// Funcție recursivă pentru a extrage toate mesajele de eroare
function list_error_messages($errorArray, $index_incarcare) {
    if (isset($errorArray['Error_attr']['errorMessage']) && !empty($errorArray['Error_attr']['errorMessage'])) {
        echo '<div class="callout alert">Factura cu index de încărcare <strong>' . htmlspecialchars($index_incarcare ?? '') . '</strong> are următoarea eroare: <strong>' . htmlspecialchars($errorArray['Error_attr']['errorMessage'] ?? '') . '</strong>!</div>';
    }
    if (isset($errorArray['Error']) && is_array($errorArray['Error'])) {
        // Poate fi o listă de erori sau un singur array
        if (array_keys($errorArray['Error']) === range(0, count($errorArray['Error']) - 1)) {
            // Este o listă indexată
            foreach ($errorArray['Error'] as $subError) {
                list_error_messages($subError, $index_incarcare);
            }
        } else {
            // Este un singur sub-array
            list_error_messages($errorArray['Error'], $index_incarcare);
        }
    }
}

if ($index_incarcare && isset($result['header'])) {
    list_error_messages($result['header'], $index_incarcare);
}

	}
elseif ($type=='FT')
{
	$stmt = mysqli_prepare($conn, "UPDATE efactura SET factura_status='OK', factura_descarcata='DA', factura_index_descarcare = ?, factura_data_descarcarii = ? WHERE factura_index_incarcare = ?");
	mysqli_stmt_bind_param($stmt, "sss", $iddescarcare, $datad, $index);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
}
echo "<div class=\"callout success\"><strong>". $strFileDownloaded ."</strong>.</div></div></div>";

		echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.back(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 2500)\">";
include '../bottom.php';
die;
}
echo "</div></div>";
include '../bottom.php';		
?>