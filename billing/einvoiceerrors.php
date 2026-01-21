<?php
include_once '../settings.php';
include_once '../classes/common.php';

if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
else
{
	include '../lang/language_EN.php';
}


$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<!doctype html>

<head>
    <!--Start Header-->
    <!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
    <!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
    <!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="no-js" lang="en">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--Font Awsome-->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css" />

    <script>
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }
    </script>
</head>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php

// Validare parametru cid
if (!isset($_GET['cID']) || empty($_GET['cID'])) {
    header("location:$strSiteURL/billing/receivedeinvoices.php?message=ER");
    die;
}
$cid = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['cID']);
if (empty($cid)) {
    header("location:$strSiteURL/billing/receivedeinvoices.php?message=ER");
    die;
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
			  echo "<h1>$strPageTitle</h1>";
$filename=$cid. '.zip';
$foldername=$cid;
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
        echo '<div class="callout alert">Factura cu index de încărcare <strong>' . htmlspecialchars($index_incarcare) . '</strong> are următoarea eroare: <strong>' . htmlspecialchars($errorArray['Error_attr']['errorMessage']) . '</strong>!</div>';
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
?>
    </div>
</div>
