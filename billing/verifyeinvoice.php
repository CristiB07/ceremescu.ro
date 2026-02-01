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
	header("location:$strSiteURL/login/index.php?message=MLF");
}
include '../dashboard/header.php';

$d = date("d-m-Y ");
$dataincarcarii = date("Y-m-d H:i:s");
//$s = date('d-m-Y', strtotime($d . ' +10 day'));

// Validate mode parameter
if (!isset($_GET["mode"]) || !in_array($_GET["mode"], ['verify', 'download', 'bulk'])) {
	die("Invalid mode parameter");
}

$mode = $_GET['mode'];

// For verify/download modes we require a valid cID and need to load the efactura row
if ($mode === 'verify' || $mode === 'download') {
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

	$index = $row3["factura_index_incarcare"];
	$indexd = $row3["factura_index_descarcare"];
}

// Bulk downloader: iterate pending efactura rows and download when ready
if ($mode == 'bulk') {

	// Select efactura rows that have an upload index but no status/download info
	$query = "SELECT * FROM efactura WHERE factura_index_incarcare<>'' 
		AND (factura_status IS NULL OR factura_status='')
		AND (factura_index_descarcare IS NULL OR factura_index_descarcare='')
		AND (factura_descarcata IS NULL OR factura_descarcata='')
		AND (factura_data_incarcarii IS NOT NULL AND DATEDIFF(NOW(), factura_data_incarcarii) <= 60)";

	$res = ezpub_query($conn, $query);
	if (!$res) {
		echo "<div class=\"callout alert\">Database query failed: " . ezpub_error($conn) . "</div>";
	} else {
		$processed = 0;
		$downloaded = 0;
		while ($row = ezpub_fetch_array($res)) {
			$processed++;
			$cID_loop = (int)$row['efactura_ID'];
			$index_loop = $row['factura_index_incarcare'];

			// Check status for this upload index
			$status_check_url = $status_url . $index_loop;
			$headr = array();
			$headr[] = 'Authorization: Bearer '.$site_client_token;
			$headr[] = 'Content-Type: text/plain';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $status_check_url);
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
			$server_output = curl_exec($ch);
			if (curl_errno($ch)) {
				$curl_err = curl_error($ch);
			} else {
				$curl_err = '';
			}
			curl_close($ch);

			if (!empty($curl_err) || empty($server_output)) {
				echo "<div class=\"callout warning\">Error fetching status for index " . htmlspecialchars($index_loop) . ": " . htmlspecialchars($curl_err) . "</div>";
				// continue to next
				continue;
			}

			$xml = @json_encode(@simplexml_load_string($server_output));
			$json = json_decode($xml);
			$status = 'unknown';
			$iddescarcare = '';
			foreach ($json as $a => $b) {
				if (isset($b->stare)) $status = (string)$b->stare;
				if (isset($b->id_descarcare)) $iddescarcare = (string)$b->id_descarcare;
			}

			// Update status in DB at minimum
			$stmt_up = mysqli_prepare($conn, "UPDATE efactura SET factura_status = ? WHERE efactura_ID = ?");
			mysqli_stmt_bind_param($stmt_up, "si", $status, $cID_loop);
			mysqli_stmt_execute($stmt_up);
			mysqli_stmt_close($stmt_up);

			// If not "in prelucrare" and we have an id to download - fetch and save
			if (strtolower(trim($status)) !== 'in prelucrare' && !empty($iddescarcare)) {
				$download_check_url = $download_url . $iddescarcare;
				$ch2 = curl_init();
				curl_setopt($ch2, CURLOPT_URL, $download_check_url);
				curl_setopt($ch2, CURLOPT_POST, 0);
				curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch2, CURLOPT_HTTPHEADER, $headr);
				$filedata = curl_exec($ch2);
				if (curl_errno($ch2)) {
					$cerr = curl_error($ch2);
				} else {
					$cerr = '';
				}
				curl_close($ch2);

				if (!empty($cerr) || $filedata === false || strlen($filedata) < 10) {
					echo "<div class=\"callout warning\">Failed to download for id " . htmlspecialchars($iddescarcare) . ": " . htmlspecialchars($cerr) . "</div>";
					continue;
				}

				// Save file
				$savedir = includeTrailingBackslash($hddpath) .'/'. $efacturadownload_folder;
				if (!is_dir($savedir)) @mkdir($savedir, 0755, true);
				$savepath = $savedir . '/' . $iddescarcare . '.zip';
				$fp = fopen($savepath, 'w');
				if ($fp) {
					fwrite($fp, $filedata);
					fclose($fp);

					// Update DB: set index_descarcare, descarcata and data
					$datad = date('Y-m-d H:i:s');
					$stmt_fin = mysqli_prepare($conn, "UPDATE efactura SET factura_index_descarcare = ?, factura_descarcata='DA', factura_data_descarcarii = ? WHERE efactura_ID = ?");
					mysqli_stmt_bind_param($stmt_fin, "ssi", $iddescarcare, $datad, $cID_loop);
					mysqli_stmt_execute($stmt_fin);
					mysqli_stmt_close($stmt_fin);

					$downloaded++;
					echo "<div class=\"callout success\">Downloaded and saved: " . htmlspecialchars(basename($savepath)) . " for efactura ID " . intval($cID_loop) . "</div>";
				} else {
					echo "<div class=\"callout alert\">Unable to open file for writing: " . htmlspecialchars($savepath) . "</div>";
				}
			} else {
				// still in processing or no id yet
				echo "<div class=\"callout notice\">Index " . htmlspecialchars($index_loop) . " status: " . htmlspecialchars($status) . " - skipping</div>";
			}
		}

		echo "<div class=\"callout success\">Processed: " . intval($processed) . " invoices, downloaded: " . intval($downloaded) . "</div>";
	}

	echo "<script type=\"text/javascript\">function delayer(){ window.history.back(-1);} </script><body onLoad=\"setTimeout('delayer()', 1500)\">";
	include '../bottom.php';
	die;
}

// Continue with single verify/download modes
if ($mode=='verify')
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