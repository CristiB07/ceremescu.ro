<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' AND IsSet($_POST['Cui'])) {

$cui=$_POST['Cui'];
//$cui='29722426';
$postfields = [];

$url='https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva';
$header = ["Content-Type: application/json"];
$postfields[] = ['cui'=>$cui, 'data'=>date('Y-m-d')];
$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 7.01; Windows NT 5.0)");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 130);
curl_setopt($ch, CURLOPT_TIMEOUT, 130);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST,TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postfields));
$result = curl_exec($ch);
if (empty($result)) //fallback on OpenAPI
    { 
		
	$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openapi.ro/api/companies/$cui");
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
	$cpl=strlen($codpostal);
	if ($cpl!=6)
	{$codpostal=str_pad($codpostal, 6, '0', STR_PAD_LEFT); }
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
$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " sursa date OpenApi";
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
		
    } 
    Else 
	{
$result = curl_exec($ch);
$data = json_decode($result, true);
If (empty($data['found'][0])) //again to Open API
{
		$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openapi.ro/api/companies/$cui");
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
	$cpl=strlen($codpostal);
	if ($cpl!=6)
	{$codpostal=str_pad($codpostal, 6, '0', STR_PAD_LEFT); }
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
$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " sursa date OpenApi";
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
		
}
Else {
	// go on with ANAF
$dategenerale=$data['found'][0];
$numar_reg_com=$dategenerale['date_generale']['nrRegCom'];
$cif=$dategenerale['date_generale']['cui'];
$denumire=strtoupper($dategenerale['date_generale']['denumire']);
$adresa=$dategenerale['date_generale']['adresa'];
$codpostal=$dategenerale['date_generale']['codPostal'];
$judet=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Judet']);
$oras=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Localitate']);
$oras=str_replace("MUN. ","",$oras); 
If ($judet=='MUNICIPIUL BUCUREŞTI')
{
	$oras=str_replace("BUCUREşTI","",$oras);
	$oras=str_replace(" ","",$oras);
	$cpl=strlen($codpostal);
	if ($cpl!=6)
	{$codpostal=str_pad($codpostal, 6, '0', STR_PAD_LEFT); }
 }


If (!empty($dategenerale['inregistrare_scop_Tva']['scpTVA']))
{
$tva="RO";
}
Else
{
	$tva="";
	}

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " sursa date ANAF";
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
}
}
}
Else
{Echo "Nu există conexiune";}
?>