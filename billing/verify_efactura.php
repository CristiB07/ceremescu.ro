<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare efactura";
include '../dashboard/header.php';

$d = date("d-m-Y ");
$dataincarcarii = date("Y-m-d H:i:s");
//$s = date('d-m-Y', strtotime($d . ' +10 day'));

$query3="SELECT * FROM date_efactura WHERE efactura_ID='$_GET[cID]'";
$result3=ezpub_query($conn,$query3);
$row3=ezpub_fetch_array($result3);
$index=$row3["factura_index_incarcare"];
$indexd=$row3["factura_index_descarcare"];

if ($_GET["mode"]=='verify')
{
		$retval=array();
		$status_url=$status_url.$index;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$site_client_token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$status_url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		$xml = json_encode(simplexml_load_string($server_output));
		$json=json_decode($xml);
		foreach ($json as $a=>$b){
			$status= $retval['status']= $b->stare;
			If ($status=='in prelucrare')
			$iddescarcare='';
Else		
{	$iddescarcare= $retval['id']= $b->id_descarcare;}
		}
		echo "<div class=\"callout success\">" . $strStatus." = ". $status  .". ". $strDownloadIndexIs." <strong>". $iddescarcare ."</strong>.</div>";
		
		$usql="UPDATE date_efactura SET factura_status='$status', factura_index_descarcare='$iddescarcare' WHERE efactura_ID=$_GET[cID];";
		ezpub_query($conn,$usql);
}
Else
{
	$retval=array();
		$download_url=$download_url.$indexd;
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
		$fp = fopen($hddpath .'/' . $efacturadownload_folder .'/'.$indexd.'.zip', 'w');
fwrite($fp, $datafile);
echo "<div class=\"callout success\"><strong>". $strFileDownloaded ."</strong>.</div>";

$datad=date("Y-m-d H:i:s");
$usql="UPDATE date_efactura SET factura_descarcata='DA', factura_data_descarcarii='$datad' WHERE efactura_ID=$_GET[cID];";
		ezpub_query($conn,$usql);

}
		
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
?>