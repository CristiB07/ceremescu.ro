<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare efactura";
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

// Validate cID parameter
if (!isset($_GET['cID']) || !filter_var($_GET['cID'], FILTER_VALIDATE_INT)) {
    die("Invalid cID parameter");
}
$cID = (int)$_GET['cID'];

$stmt = mysqli_prepare($conn, "SELECT * FROM efactura WHERE efactura_ID = ?");
mysqli_stmt_bind_param($stmt, "i", $cID);
mysqli_stmt_execute($stmt);
$result3 = mysqli_stmt_get_result($stmt);
$row3 = mysqli_fetch_assoc($result3);
mysqli_stmt_close($stmt);

if (!$row3) {
    die("Invoice not found");
}

$index=$row3["factura_index_incarcare"];
$indexd=$row3["factura_index_descarcare"];

// Validate mode parameter
if (!isset($_GET["mode"]) || !in_array($_GET["mode"], ['verify', 'download'])) {
    die("Invalid mode parameter");
}

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
else		
{	$iddescarcare= $retval['id']= $b->id_descarcare;}
		}
		
		// Validate external data from XML
		if (!is_string($status) || empty($status)) {
			$status = 'unknown';
		}
		if (!is_string($iddescarcare)) {
			$iddescarcare = '';
		}
		
		echo "<div class=\"callout success\">" . htmlspecialchars($strStatus, ENT_QUOTES, 'UTF-8')." = ". htmlspecialchars($status, ENT_QUOTES, 'UTF-8')  .". ". htmlspecialchars($strDownloadIndexIs, ENT_QUOTES, 'UTF-8')." <strong>". htmlspecialchars($iddescarcare, ENT_QUOTES, 'UTF-8') ."</strong>.</div>";
		
		$stmt = mysqli_prepare($conn, "UPDATE efactura SET factura_status = ?, factura_index_descarcare = ? WHERE efactura_ID = ?");
		mysqli_stmt_bind_param($stmt, "ssi", $status, $iddescarcare, $cID);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
}
else
{
	// Validate indexd before using in filename
	if (empty($indexd) || !preg_match('/^[a-zA-Z0-9_-]+$/', $indexd)) {
		die("Invalid download index");
	}
	
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
$stmt = mysqli_prepare($conn, "UPDATE efactura SET factura_descarcata='DA', factura_data_descarcarii = ? WHERE efactura_ID = ?");
mysqli_stmt_bind_param($stmt, "si", $datad, $cID);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

}
		
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
?>