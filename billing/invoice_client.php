<?php
//update 8.01.2025
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';
    $sql = "Select Client_Denumire, Client_CUI, Client_Adresa, Client_CIF, Client_RO, Client_Judet, Client_RC, Client_Localitate, Client_IBAN, Client_Banca FROM clienti_date WHERE ID_Client =$_POST[Client_ID]";
        $result = ezpub_query($conn,$sql);
		$row=ezpub_fetch_array($result);
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