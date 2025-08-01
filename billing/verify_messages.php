<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare mesaje";
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
if ($_GET["mode"]=='verify')
{
	?>
  <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <div class="paginate">
			  <a href ="verify_messages.php?mode=verify" class="paginate"><?php echo $strShowAll?></a>
			  <a href ="verify_messages.php?mode=verify&display=FP" class="paginate"><?php echo $strReceivedInvoices?></a>
			  <a href ="verify_messages.php?mode=verify&display=ER" class="paginate"><?php echo $strErrors?></a>
			  <a href ="verify_messages.php?mode=verify&display=FT" class="paginate"><?php echo $strSentInvoices?></a>
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
Else {
$mesaje=$obj['mesaje'];

if (!isset($_GET['display']))
{$result=$mesaje;}
Else
{
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
 ElseIF ($value['tip']=='ERORI FACTURA')
 { $indexdexincarcare=$pieces[8];}
	 $indexi=substr($indexdexincarcare, strpos($indexdexincarcare, "=") + 1);
	 if ($value['tip']=='FACTURA PRIMITA')
	 {	 $cifemitent=$pieces[5];}
 ElseIf ($value['tip']=='FACTURA TRIMISA')
 {	 $cifemitent=$pieces[7];}
 ElseIf ($value['tip']=='ERORI FACTURA')
 {$cifemitent='=0';}
 
$whatIWant = substr($cifemitent, strpos($cifemitent, "=") + 1);
$query="SELECT * FROM efactura_mesaje WHERE message_id_solicitare=$value[id_solicitare]";
$result=ezpub_query($conn,$query);
$RS=ezpub_fetch_array($result);
If (!isSet($RS))
{
	$mSQL = "INSERT INTO efactura_mesaje(";
	$mSQL = $mSQL . "message_datacreare,";
	$mSQL = $mSQL . "message_cif,";
	$mSQL = $mSQL . "message_id_solicitare,";
	$mSQL = $mSQL . "message_detalii,";
	$mSQL = $mSQL . "message_tip,";
	$mSQL = $mSQL . "message_downloadid)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$value['data_creare'] . "', ";
	$mSQL = $mSQL . "'" .$value['cif'] . "', ";
	$mSQL = $mSQL . "'" .$value['id_solicitare'] . "', ";
	$mSQL = $mSQL . "'" .$value['detalii'] . "', ";
	$mSQL = $mSQL . "'" . $value['tip'] . "', ";
	$mSQL = $mSQL . "'" .$value['id'] . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
}

print "<td>"  . date("d.m.Y H:m", strtotime($value['data_creare']))."</td>";
print "<td><strong><a href=\"verify_supplier.php?cui=$whatIWant\">". $whatIWant."</strong></a></td>";
print "<td>"  . $value['detalii']. "</strong></td>";
print "<td>"  . $tip."</td>";
print "<td>"  . $value['id']."</td>";
		If ($value['tip']=='FACTURA PRIMITA')
		{$query="SELECT * FROM efactura_primite WHERE efactura_primita_CUI='$whatIWant' AND efactura_primita_index='$value[id]'";}
	ElseIf ($value['tip']=='FACTURA TRIMISA')
	{$query="SELECT * FROM efactura WHERE factura_CIF='$whatIWant' AND factura_index_descarcare='$value[id]'";
	}ElseIf ($value['tip']=='ERORI FACTURA')
	{$query="SELECT * FROM efactura_erori WHERE index_incarcare='$indexi' AND index_descarcare='$value[id]'";}
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
if (!$row) 
{
print "<td>$strNo</td>";
print "<td>$strNo</td>";
print "<td><a href=\"verify_messages.php?type=$tip&mode=dowload&cid="  . $value['id']."&cif=".$whatIWant."&datap=".$value['data_creare']."&idi=".$indexi."\"><i class=\"large fas fa-file-download\" title=\"$strDownload\"></a></i></td>";
}
Else
{
	If ($value['tip']=='FACTURA PRIMITA')
	{$datadescarcarii=date("d.m.Y H:m", strtotime($row['efactura_primita_datad'] ?? ''));}
	ElseIf ($value['tip']=='FACTURA TRIMISA')
	{If (!$row['factura_data_descarcarii'])
		{		$datadescarcarii='';}
	Else
		{		$datadescarcarii=date("d.m.Y H:m", strtotime($row['factura_data_descarcarii']) ?? '');}
		}
	ElseIf ($value['tip']=='ERORI FACTURA')
	{$datadescarcarii=date("d.m.Y H:m", strtotime($row['data_descarcare']) ?? '');}
	print "<td>$strYes</td>";
	print "<td>$datadescarcarii</td>";
print "<td><i class=\"large fas fa-file-download\" title=\"$strDownload\"></i></td>";

}
print "<td><a href=\"efactura_reader2.php?cid="  . $value['id']."\"><i class=\"large fas fa-search\" title=\"$strView\"></i></a></td>";

 echo "</tr>";}
 echo "</tbody><tfoot><tr><td></td><td  colspan=\"7\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
curl_close ($ch);
}}
Else
{
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
		ElseIf ($type=='FT')
		{$fp = fopen($hddpath .'/' . $efactura_folder .'/'.$iddescarcare.'.zip', 'w');}
		ElseIf ($type=='ER')
		{$fp = fopen($hddpath .'/' . $error_folder .'/'.$iddescarcare.'.zip', 'w');}	
	
fwrite($fp, $datafile);

		If ($type=='FP')
	{
	$mSQL = "INSERT INTO efactura_primite(";
	$mSQL = $mSQL . "efactura_primita_CUI,";
	$mSQL = $mSQL . "efactura_primita_index,";
	$mSQL = $mSQL . "efactura_primita_download,";
	$mSQL = $mSQL . "efactura_primita_datap,";
	$mSQL = $mSQL . "efactura_primita_datad)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$cifemitent . "', ";
	$mSQL = $mSQL . "'" .$iddescarcare . "', ";
	$mSQL = $mSQL . "'DA', ";
	$mSQL = $mSQL . "'" .$datap . "', ";
	$mSQL = $mSQL . "'" .$datad . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
	}
	If ($type=='ER')
	{
	$mSQL = "INSERT INTO efactura_erori(";
	$mSQL = $mSQL . "data_erorii,";
	$mSQL = $mSQL . "index_incarcare,";
	$mSQL = $mSQL . "index_descarcare,";
	$mSQL = $mSQL . "status,";
	$mSQL = $mSQL . "data_descarcare)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$datap . "', ";
	$mSQL = $mSQL . "'" .$index . "', ";
	$mSQL = $mSQL . "'" .$iddescarcare . "', ";
	$mSQL = $mSQL . "'DA', ";
	$mSQL = $mSQL . "'" .$datad . "') ";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
	}
ElseIf ($type=='FT')
{
$usql="UPDATE efactura SET factura_status='OK', factura_descarcata='DA', factura_index_descarcare='$iddescarcare', factura_data_descarcarii='$datad' WHERE factura_index_incarcare='$index';";
		ezpub_query($conn,$usql);
}
echo "<div class=\"callout success\"><strong>". $strFileDownloaded ."</strong>.</div></div></div>";

		echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.back(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1000)\">";
include '../bottom.php';
die;
}
echo "</div></div>";
include '../bottom.php';		
?>