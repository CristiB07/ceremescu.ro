<?php
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

// Validare și sanitizare input array
if (!isset($_POST["factura_ID"]) || !is_array($_POST["factura_ID"]) || empty($_POST["factura_ID"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Date invalide']);
    exit;
}

$mesaj="";
$valoare=0;
$array = $_POST["factura_ID"];

// Validare fiecare ID din array
$validated_ids = [];
for ($i = 0; $i < sizeof($array); $i++) {
    $id = filter_var($array[$i], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID factură invalid']);
        exit;
    }
    $validated_ids[] = $id;
}

// Procesare cu prepared statement
for ($i = 0; $i < sizeof($validated_ids); $i++) {
    $value = $validated_ids[$i];
    
    // SELECT cu prepared statement
    $stmt = mysqli_prepare($conn, "SELECT factura_client_valoare_totala, factura_data_emiterii, factura_numar FROM facturare_facturi WHERE factura_ID=?");
    mysqli_stmt_bind_param($stmt, "i", $value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = ezpub_fetch_array($result);
    mysqli_stmt_close($stmt);
    
    if ($row) {
        $codenumarfactura=str_pad($row["factura_numar"], 8, '0', STR_PAD_LEFT);
        $mesaj = $mesaj . "Contravaloare factură " . $siteInvoicingCode . $codenumarfactura . "/" . date('d.m.Y', strtotime($row["factura_data_emiterii"])) . "; ";
        $valoare = $valoare + $row["factura_client_valoare_totala"];
    }
}
$formresult=json_encode([
  'suma' => $valoare,
  'factura' => $mesaj
]);
header('Content-Type: application/json');
echo $formresult;