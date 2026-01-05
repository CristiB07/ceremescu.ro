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
	header("location:$strSiteURL/login/login.php?message=MLF");
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
                    <a href="verifymessages.php?mode=verify&display=FP"
                        class="paginate"><?php echo $strReceivedInvoices?></a>
                    <a href="verifymessages.php?mode=verify&display=ER" class="paginate"><?php echo $strErrors?></a>
                    <a href="verifymessages.php?mode=verify&display=FT"
                        class="paginate"><?php echo $strSentInvoices?></a>
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
    curl_close($ch); 
	$obj = json_decode($response, true);

If (isset ($obj['eroare']))
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
elseIf ($filtru=='ER') {$cautare='ERORI FACTURA';}
else {$cautare="FACTURA TRIMISA";}

$result = array_filter($mesaje, function($element) use ($cautare) {
    return $element['tip'] == $cautare;
});
}
array_multisort(array_column($result, 'data_creare'), SORT_DESC, $result);


echo "<table class=\"hover\">
<thead>
<tr>
<td>$strDate</td>
<td>$strCompanyVAT</td>
<td>$strDetails</td>
<td>$strType</td>
<td>$strDownloadIndex</td>
<td>$strFileDownloaded</td>
<td>$strDate</td>
<td>$strDownload</td>
<td>$strRegister</td>
<td>$strView</td>
</tr>
</thead>
<tbody>";
 foreach($result as $index => $value) {
	 echo "<tr>";
	 If ($value['tip']=='FACTURA PRIMITA') {$tip='FP';}
	 If ($value['tip']=='FACTURA TRIMISA') {$tip='FT';}
	 If ($value['tip']=='ERORI FACTURA') {$tip='ER';}
	 $pieces = explode(" ", $value['detalii']);
	 if ($value['tip']=='FACTURA PRIMITA' OR $value['tip']=='FACTURA TRIMISA')
	 { $indexdexincarcare=$pieces[2];}
 elseif($value['tip']=='ERORI FACTURA')
 { $indexdexincarcare=$pieces[8];}
	 $indexi=substr($indexdexincarcare, strpos($indexdexincarcare, "=") + 1);
	 if ($value['tip']=='FACTURA PRIMITA')
	 {	 $cifemitent=$pieces[5];}
 elseIf ($value['tip']=='FACTURA TRIMISA')
 {	 $cifemitent=$pieces[7];}
 elseIf ($value['tip']=='ERORI FACTURA')
 {$cifemitent='=0';}
 
$whatIWant = substr($cifemitent, strpos($cifemitent, "=") + 1);
$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_mesaje WHERE message_id_solicitare = ?");
mysqli_stmt_bind_param($stmt, "s", $value['id_solicitare']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$RS = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
If (!isSet($RS))
{
	$stmt = mysqli_prepare($conn, "INSERT INTO efactura_mesaje(message_datacreare, message_cif, message_id_solicitare, message_detalii, message_tip, message_downloadid) VALUES (?, ?, ?, ?, ?, ?)");
	mysqli_stmt_bind_param($stmt, "ssssss", $value['data_creare'], $value['cif'], $value['id_solicitare'], $value['detalii'], $value['tip'], $value['id']);
	if (!mysqli_stmt_execute($stmt)) {
		die('Error: ' . mysqli_error($conn));
	}
	mysqli_stmt_close($stmt);
}

print "<td>"  . date("d.m.Y H:m", strtotime($value['data_creare']))."</td>";
print "<td><strong><a href=\"verifysupplier.php?cui=".urlencode($whatIWant)."\">".htmlspecialchars($whatIWant, ENT_QUOTES, 'UTF-8')."</strong></a></td>";
print "<td>".htmlspecialchars($value['detalii'], ENT_QUOTES, 'UTF-8')."</td>";
print "<td>".htmlspecialchars($tip, ENT_QUOTES, 'UTF-8')."</td>";
print "<td>".htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')."</td>";
		If ($value['tip']=='FACTURA PRIMITA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_primite WHERE efactura_primita_CUI = ? AND efactura_primita_index = ?");
			mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
		} elseIf ($value['tip']=='FACTURA TRIMISA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura WHERE factura_CIF = ? AND factura_index_descarcare = ?");
			mysqli_stmt_bind_param($stmt, "ss", $whatIWant, $value['id']);
		} elseIf ($value['tip']=='ERORI FACTURA') {
			$stmt = mysqli_prepare($conn, "SELECT * FROM efactura_erori WHERE index_incarcare = ? AND index_descarcare = ?");
			mysqli_stmt_bind_param($stmt, "ss", $indexi, $value['id']);
		}
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
if (!$row) 
{
print "<td>$strNo</td>";
print "<td>$strNo</td>";
print "<td><a href=\"verifymessages.php?type=".urlencode($tip)."&mode=dowload&cid=".urlencode($value['id'])."&cif=".urlencode($whatIWant)."&datap=".urlencode($value['data_creare'])."&idi=".urlencode($indexi)."\"><i class=\"fa-xl fas fa-file-download\" title=\"".htmlspecialchars($strDownload, ENT_QUOTES, 'UTF-8')."\"></a></i></td>";
}
else
{
	If ($value['tip']=='FACTURA PRIMITA')
	{$datadescarcarii=date("d.m.Y H:m", strtotime($row['efactura_primita_datad'] ?? ''));}
	elseIf ($value['tip']=='FACTURA TRIMISA')
	{If (!$row['factura_data_descarcarii'])
		{		$datadescarcarii='';}
	else
		{		$datadescarcarii=date("d.m.Y H:m", strtotime($row['factura_data_descarcarii']) ?? '');}
		}
	elseIf ($value['tip']=='ERORI FACTURA')
	{$datadescarcarii=date("d.m.Y H:m", strtotime($row['data_descarcare']) ?? '');}
	print "<td>$strYes</td>";
	print "<td>$datadescarcarii</td>";
print "<td><i class=\"fa-xl fas fa-file-download\" title=\"$strDownload\"></i></td>";

}
If ($value['tip']=='FACTURA PRIMITA')
{print "<td><a href=\"einvoicereader.php?cid=".urlencode($value['id'])."\"><i class=\"fa-xl fas fa-file-import\" title=\"".htmlspecialchars($strRegister, ENT_QUOTES, 'UTF-8')."\"></i></a></td>";
?>
        <div class="full reveal" id="exampleModal1_<?php echo htmlspecialchars($value['id'], ENT_QUOTES, 'UTF-8')?>" data-reveal>
            <iframe src="viewinvoice.php?type=0&option=show&cID=<?php echo urlencode($value['id'])?>" frameborder="0"
                style="border:0" Width="100%" height="1000"></iframe>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <td><i class="fa-xl fas fa-search" title="<?php echo $strView?>"
                data-open="exampleModal1_<?php echo $value['id']?>"></i></td>
        <?php }
				else
				{
					print "<td><i class=\"fa-xl fas fa-file-import\" title=\"$strRegister\"></i></td>
					<td><i class=\"fa-xl fas fa-search\" title=\"$strView\"></i></td>";
				}
 print "</tr>";}
 print "</tbody><tfoot><tr><td></td><td  colspan=\"8\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
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
		If ($type=='FP')
		{$fp = fopen($hddpath .'/' . $efacturareceived_folder .'/'.$iddescarcare.'.zip', 'w');}
		elseIf ($type=='FT')
		{$fp = fopen($hddpath .'/' . $efactura_folder .'/'.$iddescarcare.'.zip', 'w');}
		elseIf ($type=='ER')
		{$fp = fopen($hddpath .'/' . $error_folder .'/'.$iddescarcare.'.zip', 'w');}	
	
fwrite($fp, $datafile);

		If ($type=='FP')
	{
		$stmt = mysqli_prepare($conn, "INSERT INTO efactura_primite(efactura_primita_CUI, efactura_primita_index, efactura_primita_download, efactura_primita_datap, efactura_primita_datad) VALUES (?, ?, 'DA', ?, ?)");
		mysqli_stmt_bind_param($stmt, "ssss", $cifemitent, $iddescarcare, $datap, $datad);
		if (!mysqli_stmt_execute($stmt)) {
			die('Error: ' . mysqli_error($conn));
		}
		mysqli_stmt_close($stmt);
	}
	If ($type=='ER')
	{
		$stmt = mysqli_prepare($conn, "INSERT INTO efactura_erori(data_erorii, index_incarcare, index_descarcare, status, data_descarcare) VALUES (?, ?, ?, 'DA', ?)");
		mysqli_stmt_bind_param($stmt, "ssss", $datap, $index, $iddescarcare, $datad);
		if (!mysqli_stmt_execute($stmt)) {
			die('Error: ' . mysqli_error($conn));
		}
		mysqli_stmt_close($stmt);
	}
elseIf ($type=='FT')
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
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
echo "</div></div>";
include '../bottom.php';		
?>