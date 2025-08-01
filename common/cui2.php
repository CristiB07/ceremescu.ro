<?php
//update 13.07.2023
include '../settings.php';
include '../classes/common.php';


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openapi.ro/api/companies/40842148");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  $openapikey
));
if( ! $response = curl_exec($ch)) 
    { 
        trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
$obj = json_decode($response, true);
$denumire=$obj['denumire'];

If (!empty($obj['tva'])){
$tva="RO";}
Else
{$tva="";}
$cif=$obj['cif'];
$denumire=strtoupper($obj['denumire']);
$adresa=$obj['adresa'];
$judet=strtoupper($obj['judet']);
$numar_reg_com=$obj['numar_reg_com'];
$codpostal=$obj['cod_postal'];
If (!empty($obj['cod_postal'])){
$codpostal=$obj['cod_postal'];
  $sql = "Select Localitate FROM coduripostale WHERE Codpostal =$codpostal";
      $result = ezpub_query($conn,$sql);
	$row=ezpub_fetch_array($result);
$oras=$row["Localitate"];
}
Else
{
	if ($judet=='MUNICIPIUL BUCUREșTI')
	{$oras="BUCUREȘTI";}
	Else 
	{$oras=" ";}
	}
$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif;
$formresult=json_encode([
  'tva' => $tva,
  'cif' => $cif,
  'adresa' => $adresa,
  'denumire' => $denumire,
  'numar_reg_com' => $numar_reg_com,
  'oras' => $oras,
  'judet' => $judet,
  'codpostal' => $codpostal,
  'datecontract'=>$datecontract
]);
header('Content-Type: application/json');
echo $formresult;
?>