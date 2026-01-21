<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Actualizare solduri";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/index.php?message=MLF");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
// Validate POST parameters
if (!isset($_POST["cash_banca_ING"]) || !isset($_POST["cash_banca_transilvania"]) || !isset($_POST["cash_banca_trezorerie"])) {
    die("Missing required parameters");
}


// Convertește valorile din format românesc în numeric standard
$cash_banca_ING_str = parseRomanianNumber($_POST["cash_banca_ING"]);
$cash_banca_transilvania_str = parseRomanianNumber($_POST["cash_banca_transilvania"]);
$cash_banca_trezorerie_str = parseRomanianNumber($_POST["cash_banca_trezorerie"]);

// Validate that values are numeric (după conversie)
if (!is_numeric($cash_banca_ING_str) || !is_numeric($cash_banca_transilvania_str) || !is_numeric($cash_banca_trezorerie_str)) {
    die("Invalid numeric values");
}

// Convert to floats (pentru a accepta și zecimale)
$cash_banca_ING = (float)$cash_banca_ING_str;
$cash_banca_transilvania = (float)$cash_banca_transilvania_str;
$cash_banca_trezorerie = (float)$cash_banca_trezorerie_str;

// Use prepared statement cu tipuri double pentru zecimale
$stmt = mysqli_prepare($conn, "UPDATE cash_banca SET cash_banca_ING = ?, cash_banca_transilvania = ?, cash_banca_trezorerie = ?");
mysqli_stmt_bind_param($stmt, "ddd", $cash_banca_ING, $cash_banca_transilvania, $cash_banca_trezorerie);

if (!mysqli_stmt_execute($stmt)) {
    die('Error: ' . mysqli_error($conn));
}
mysqli_stmt_close($stmt);

echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
?>