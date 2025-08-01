<?php
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';
    $sql = "Select factura_client_valoare_totala, factura_data_emiterii, factura_numar FROM facturare_facturi WHERE factura_ID =$_POST[factura_ID]";
        $result = ezpub_query($conn,$sql);
		$row=ezpub_fetch_array($result);
		$mesaj="Contravaloare factură CNS" .$row["factura_numar"] ."/". date('d.m.Y',strtotime($row["factura_data_emiterii"]));
$formresult=json_encode([
  'suma' => $row["factura_client_valoare_totala"],
  'factura' => $mesaj
]);
header('Content-Type: application/json');
echo $formresult;