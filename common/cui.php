<?php
//update 8.01.2025
ob_start();
include '../settings.php';
include '../classes/common.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' AND IsSet($_POST['Cui'])) {

// Validate and sanitize CUI input

$cui = preg_replace('/[^0-9]/', '', $_POST['Cui']); // Only digits
if (empty($cui) || !is_numeric($cui)) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'CUI invalid']);
    exit();
}
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
//curl_close($ch);
$data = json_decode($result, true);
if (empty($data['found'][0])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Nu s-au găsit date pentru CUI-ul specificat']);
    exit();
}
$dategenerale=$data['found'][0];
$numar_reg_com=$dategenerale['date_generale']['nrRegCom'] ?? '';
$cif=$dategenerale['date_generale']['cui'] ?? '';
$denumire=strtoupper($dategenerale['date_generale']['denumire'] ?? '');
$adresa=$dategenerale['date_generale']['adresa'] ?? '';
$codpostal=$dategenerale['date_generale']['codPostal'] ?? '';
$cpl=strlen($codpostal);
	if ($cpl!=6)
	{$codpostal=str_pad($codpostal, 6, '0', STR_PAD_LEFT); }

$judet=normalizeDiacritice(strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Judet'] ?? ''));
$codlocalitate=$dategenerale['adresa_sediu_social']['scod_Localitate'] ?? '';
$localitate=$dategenerale['adresa_sediu_social']['sdenumire_Localitate'] ?? '';

  // Use prepared statement to prevent SQL injection
  $stmt = mysqli_prepare($conn, "SELECT Localitate FROM generale_coduripostale WHERE Codpostal = ?");
  mysqli_stmt_bind_param($stmt, 's', $codpostal);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
  $oras = $row["Localitate"] ?? '';
if (empty($oras)&&!empty($codpostal))
{
  If ($judet!='MUNICIPIUL BUCUREȘTI' && !empty($codlocalitate))
  {
    $oras=$localitate;
  }
elseif ($judet=='MUNICIPIUL BUCUREȘTI')
{
	$oras="SECTOR".$codlocalitate;
 }
}

If (!empty($dategenerale['inregistrare_scop_Tva']['scpTVA']))
{
$tva="RO";
}
else
{
	$tva="";
	}

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif;
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

ob_end_clean();
header('Content-Type: application/json');
echo $formresult;
}
else
{
  ob_end_clean();
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Nu există conexiune']);
}
?>