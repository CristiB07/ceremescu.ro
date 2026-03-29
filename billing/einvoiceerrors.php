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

// debug output
if (isset($_GET['debug']) && $_GET['debug']) {
    echo '<pre>DEBUG einvoiceerrors:' . "\n";
    echo 'cid=' . htmlspecialchars($cid) . "\n";
    echo 'filelocation=' . $filelocation . "\n";
    echo 'exists file=' . (file_exists($filelocation) ? 'yes' : 'no') . "\n";
    echo 'ziplocation=' . $ziplocation . "\n";
    echo 'is_dir=' . (is_dir($ziplocation) ? 'yes' : 'no') . "\n";
    echo '</pre>';
}

$zip = new ZipArchive;
$res = $zip->open($filelocation);
if ($res === TRUE) {
	echo "<div class=\"callout success\">$strFileExtracted:<br/>";
	for( $i = 0; $i < $zip->numFiles; $i++ ){ 
		$stat = $zip->statIndex( $i ); 
        print_r( basename( $stat['name'] ) . '<br />' ); 
		$zipfile=basename( $stat['name']);
		$zipEntry = 'zip://' . $filelocation . "#" . $zipfile;
		$result = file_get_contents($zipEntry);
	}

    // make sure destination folder exists before extraction
    if (!is_dir($ziplocation)) {
        mkdir($ziplocation, 0755, true);
    }
    $zip->extractTo($ziplocation);
    $zip->close();
		echo "</div>";
} else {
	echo "<div class=\"callout alert\">";
    echo $strThereWasAnError;
    if (isset($_GET['debug']) && $_GET['debug']) {
        echo ' (zip open returned code ' . $res . ')';
    }
    echo "</div>";
    // nothing to process further
    exit;
}

// ensure the extraction directory exists and contains files before proceeding
if (!is_dir($ziplocation)) {
    if (isset($_GET['debug']) && $_GET['debug']) {
        echo '<pre>DEBUG: ziplocation not directory: ' . $ziplocation . '</pre>';
    }
    echo '<div class="callout alert">' . $strThereWasAnError . '</div>';
    exit;
}

$files = scandir($ziplocation);
if (isset($_GET['debug']) && $_GET['debug']) {
    echo '<pre>DEBUG scandir result: ' . var_export($files, true) . '</pre>';
}
if ($files === false || count($files) <= 2) {
    echo '<div class="callout alert">' . $strThereWasAnError . '</div>';
    exit;
}

$firstFile = $ziplocation . $files[2]; // because [0] = "." [1] = ".." 

if (!file_exists($firstFile)) {
    if (isset($_GET['debug']) && $_GET['debug']) {
        echo '<pre>DEBUG: firstFile not found: ' . $firstFile . '</pre>';
    }
    echo '<div class="callout alert">' . $strThereWasAnError . '</div>';
    exit;
}

$xml = file_get_contents($firstFile);
if ($xml === false) {
    die("Error: Cannot create object");
}
// debug xml
if (isset($_GET['debug']) && $_GET['debug']) {
    echo '<pre>DEBUG raw XML:' . htmlspecialchars($xml) . '</pre>';
}
$result = xml2array($xml);
if (isset($_GET['debug']) && $_GET['debug']) {
    echo '<pre>DEBUG parsed array:' . var_export($result, true) . '</pre>';
}

// Extrage indexul de încărcare și mesajul de eroare
$index_incarcare = isset($result['header_attr']['Index_incarcare']) ? $result['header_attr']['Index_incarcare'] : '';
$error_message = isset($result['header']['Error_attr']['errorMessage']) ? $result['header']['Error_attr']['errorMessage'] : '';

// Flag pentru a detecta dacă a fost tipărită cel puţin o eroare
$errors_found = false;

// Funcție recursivă pentru a extragă toate mesajele de eroare
function list_error_messages($array, $index_incarcare) {
    global $errors_found;
    if (!is_array($array)) {
        return;
    }
    foreach ($array as $key => $value) {
        // dacă cheia se termină cu _attr și are eroare
        if (is_string($key) && substr($key, -5) === '_attr' && isset($value['errorMessage'])) {
            echo '<div class="callout alert">Factura cu index de încărcare <strong>' . htmlspecialchars($index_incarcare ?? '') . '</strong> are următoarea eroare: <strong>' . htmlspecialchars($value['errorMessage']) . '</strong>!</div>';
            $errors_found = true;
        }
        // dacă avem o sub-listă de erori, recursiv
        if (is_array($value)) {
            list_error_messages($value, $index_incarcare);
        }
    }
}

if ($index_incarcare && isset($result['header'])) {
    if (isset($_GET['debug']) && $_GET['debug']) {
        echo '<pre>DEBUG calling list_error_messages with index ' . htmlspecialchars($index_incarcare) . '</pre>';
    }
    list_error_messages($result['header'], $index_incarcare);
} else {
    if (isset($_GET['debug']) && $_GET['debug']) {
        echo '<pre>DEBUG no header or index found; index_incarcare="' . htmlspecialchars($index_incarcare) . '"</pre>';
    }
}

// dacă nu s-a tipărit nicio eroare, arătăm structura pentru inspecţie
if (!$errors_found) {
    echo '<div class="callout warning">Nu s-au găsit mesaje de eroare în fișierul XML.</div>';
    echo '<pre>Structură analizată: ' . var_export($result, true) . '</pre>';
}
?>
    </div>
</div>
