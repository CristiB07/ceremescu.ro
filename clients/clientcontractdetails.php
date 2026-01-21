<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Date contract";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}
include '../dashboard/header.php';

// Generare CSRF token pentru protecție
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <h2><?php echo $strPageTitle?></h2>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' AND IsSet($_POST['Cui'])) {

// Verificare CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo '<div class="callout alert">Eroare de securitate. Vă rugăm să reîncărcați pagina.</div>';
    die;
}

// Validare și sanitizare input
$cui = preg_replace('/[^0-9]/', '', $_POST['Cui']);
if (empty($cui) || strlen($cui) < 6 || strlen($cui) > 10) {
    echo '<div class="callout alert">CUI invalid. Introduceți un CUI valid (6-10 cifre).</div>';
    $cui = null;
}

$reprezentant = trim($_POST['Reprezentant']);
if (empty($reprezentant) || strlen($reprezentant) > 255) {
    echo '<div class="callout alert">Nume reprezentant invalid.</div>';
    $reprezentant = null;
}
$reprezentant = htmlspecialchars(strtoupper($reprezentant), ENT_QUOTES, 'UTF-8');

if ($cui && $reprezentant) {
$postfields = [];

$url='https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva';
$header = ["Content-Type: application/json"];
$postfields[] = ['cui'=>$cui, 'data'=>date('Y-m-d')];
$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 7.01; Windows NT 5.0)");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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
else
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
  // Prepared statement pentru SQL injection prevention
  $stmt = mysqli_prepare($conn, "SELECT Localitate FROM generale_coduripostale WHERE Codpostal = ?");
  mysqli_stmt_bind_param($stmt, 's', $codpostal);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
$oras=$row["Localitate"] ?? '';
}
else
{
	if ($judet=='MUNICIPIUL BUCUREșTI')
	{$oras="BUCUREȘTI";}
	else 
	{$oras=" ";}
	}
// Sanitizare toate câmpurile pentru XSS protection
$denumire = htmlspecialchars($denumire ?? '', ENT_QUOTES, 'UTF-8');
$adresa = htmlspecialchars($adresa ?? '', ENT_QUOTES, 'UTF-8');
$oras = htmlspecialchars($oras ?? '', ENT_QUOTES, 'UTF-8');
$judet = htmlspecialchars($judet ?? '', ENT_QUOTES, 'UTF-8');
$numar_reg_com = htmlspecialchars($numar_reg_com ?? '', ENT_QUOTES, 'UTF-8');
$tva = htmlspecialchars($tva ?? '', ENT_QUOTES, 'UTF-8');
$cif = htmlspecialchars($cif ?? '', ENT_QUOTES, 'UTF-8');
$codpostal = htmlspecialchars($codpostal ?? '', ENT_QUOTES, 'UTF-8');

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " reprezentată prin ". $reprezentant;
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
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		
    } 
    else 
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
else
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
  // Prepared statement pentru SQL injection prevention
  $stmt = mysqli_prepare($conn, "SELECT Localitate FROM generale_coduripostale WHERE Codpostal = ?");
  mysqli_stmt_bind_param($stmt, 's', $codpostal);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  mysqli_stmt_close($stmt);
$oras=$row["Localitate"] ?? '';
}
else
{
	if ($judet=='MUNICIPIUL BUCUREșTI')
	{$oras="BUCUREȘTI";}
	else 
	{$oras=" ";}
	}
// Sanitizare toate câmpurile pentru XSS protection
$denumire = htmlspecialchars($denumire ?? '', ENT_QUOTES, 'UTF-8');
$adresa = htmlspecialchars($adresa ?? '', ENT_QUOTES, 'UTF-8');
$oras = htmlspecialchars($oras ?? '', ENT_QUOTES, 'UTF-8');
$judet = htmlspecialchars($judet ?? '', ENT_QUOTES, 'UTF-8');
$numar_reg_com = htmlspecialchars($numar_reg_com ?? '', ENT_QUOTES, 'UTF-8');
$tva = htmlspecialchars($tva ?? '', ENT_QUOTES, 'UTF-8');
$cif = htmlspecialchars($cif ?? '', ENT_QUOTES, 'UTF-8');
$codpostal = htmlspecialchars($codpostal ?? '', ENT_QUOTES, 'UTF-8');

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " reprezentată prin ". $reprezentant;
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
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		
}
else {
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
else
{
	$tva="";
	}

// Sanitizare toate câmpurile pentru XSS protection
$denumire = htmlspecialchars($denumire ?? '', ENT_QUOTES, 'UTF-8');
$adresa = htmlspecialchars($adresa ?? '', ENT_QUOTES, 'UTF-8');
$oras = htmlspecialchars($oras ?? '', ENT_QUOTES, 'UTF-8');
$judet = htmlspecialchars($judet ?? '', ENT_QUOTES, 'UTF-8');
$numar_reg_com = htmlspecialchars($numar_reg_com ?? '', ENT_QUOTES, 'UTF-8');
$tva = htmlspecialchars($tva ?? '', ENT_QUOTES, 'UTF-8');
$cif = htmlspecialchars($cif ?? '', ENT_QUOTES, 'UTF-8');
$codpostal = htmlspecialchars($codpostal ?? '', ENT_QUOTES, 'UTF-8');

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " reprezentată prin ". $reprezentant;
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
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

}
}
} // Închidere if ($cui && $reprezentant)
?>
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="clientcontractdetails.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
</div>
</div>
<div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strDetails?>
	  <textarea name="datecontract" id="simple-editor-html" class="simple-editor-html" rows="5"><?php echo htmlspecialchars($datecontract, ENT_QUOTES, 'UTF-8') ?></textarea>
</label>	</div>	
	</div>	
	<div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> 
	<button class="button" onclick="copyContent()"><?php echo $strCopy?>&nbsp; <i class="fas fa-copy"></i></button>

<script>
  let text = document.getElementById('simple-editor-html').innerHTML;
  const copyContent = async () => {
    try {
      await navigator.clipboard.writeText(text);
      console.log('Content copied to clipboard');
    } catch (err) {
      console.error('Failed to copy: ', err);
    }
  }
</script>
	</div>	
	</div>	

<?php
}
else
{
	?>
	
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="clientcontractdetails.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></i></a></p>
</div>
</div>
<form method="post" action="clientcontractdetails.php" >
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
 <div class="grid-x grid-margin-x">
     <div class="large-6 medium-6 small-6 cell"> 
	  <label><?php echo $strCompanyVAT?>
	<input name="Cui" type="text" size="50"  placeholder="<?php echo $strCompanyVAT?>" class="required" />
	 </label></div>  
	  <div class="large-6 medium-6 small-6 cell"> 
	  <label><?php echo $strAdministrator?>
	<input name="Reprezentant" type="text" size="50" placeholder="<?php echo $strFirstName .' ' . strtoupper($strLastName)?>"  class="required" />
	  </label></div>
	  </div>
	 
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> 
	 <input type="submit" Value="<?php echo $strSearch?>" name="Submit" class="button success" /> 
	</div>
	</div>
  </form>
	<?php
	
}
?>
</div>
</div>
<?php
include '../bottom.php';
?>