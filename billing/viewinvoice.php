<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

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
// Validare și sanitizare parametri
if (!isset($_REQUEST['cID']) || !isset($_REQUEST['type'])) {
    echo "<div class=\"callout alert\">$strThereWasAnError</div>";
    die("Invalid request parameters.");
}

// Validare cID - trebuie să fie integer sau string valid
$cID = $_REQUEST['cID'];
if (!is_numeric($cID) && !preg_match('/^[a-zA-Z0-9_-]+$/', $cID)) {
    echo "<div class=\"callout alert\">$strThereWasAnError</div>";
    die("Invalid cID parameter.");
}

// Validare type - trebuie să fie 0, 1 sau 2
$type = filter_var($_REQUEST['type'], FILTER_VALIDATE_INT);
if ($type === false || !in_array($type, [0, 1, 2])) {
    echo "<div class=\"callout alert\">$strThereWasAnError</div>";
    die("Invalid type parameter.");
}

// Validare option dacă există
if (isset($_REQUEST['option'])) {
    $option = $_REQUEST['option'];
    $allowed_options = ['print', 'show', 'pdf'];
    if (!in_array($option, $allowed_options)) {
        echo "<div class=\"callout alert\">$strThereWasAnError</div>";
        die("Invalid option parameter.");
    }
    $_REQUEST['option'] = $option;
}

// Setare parametri validați în $_REQUEST pentru invoicetemplate.php
$_REQUEST['cID'] = $cID;
$_REQUEST['type'] = $type;

include './invoicetemplate.php';
echo $invoice;
?>
    </div>
</div>