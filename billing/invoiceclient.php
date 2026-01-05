<?php
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';

// Validare și sanitizare input
if (!isset($_POST['Client_ID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Client ID lipsește']);
    die;
}

$client_id = filter_var($_POST['Client_ID'], FILTER_VALIDATE_INT);
if ($client_id === false || $client_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Client ID invalid']);
    die;
}

// Prepared statement pentru SELECT
$stmt = mysqli_prepare($conn, "SELECT Client_Denumire, Client_CUI, Client_Adresa, Client_CIF, Client_RO, Client_Judet, Client_RC, Client_Localitate, Client_IBAN, Client_Banca FROM clienti_date WHERE ID_Client = ?");
mysqli_stmt_bind_param($stmt, "i", $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = ezpub_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Client negăsit']);
    die;
}

$formresult=json_encode([
  'cif' => $row["Client_CIF"],
  'tva' => $row["Client_RO"],
  'adresa' => $row["Client_Adresa"],
  'denumire' => $row["Client_Denumire"],
  'numar_reg_com' => $row["Client_RC"],
  'iban' => $row["Client_IBAN"],
  'banca' => $row["Client_Banca"],
  'localitate' => $row["Client_Localitate"],
  'judet' => $row["Client_Judet"]
]);
header('Content-Type: application/json');
echo $formresult;