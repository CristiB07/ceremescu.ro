<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Date contract";
include '../dashboard/header.php';
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <h2><?php echo $strPageTitle?></h2>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' AND IsSet($_POST['Cui'])) {

$cui=$_POST['Cui'];
//$cui='29722426';
$reprezentant=strtoupper($_POST['Reprezentant']);
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
]);
		
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
]);
		
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
]);

}
}
?>
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="datecontract.php" class="button"><?php echo '$strBack&nbsp;<i class=\"large fas  fa-backward\" title=\"$strBack\"></i>'?></a></p>
</div>
</div>
<div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> 
	  <label><?php echo $strDetails?></label>
	  <textarea name="datecontract" id="myTextEditor" class="myTextEditor" rows="5"><?php echo $datecontract ?></textarea>
	</div>	
	</div>	
	<div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> 
	<button class="button" onclick="copyContent()"><?php echo $strCoppy?>&nbsp; <i class="fas fa-copy"></i></button>

<script>
  let text = document.getElementById('myTextEditor').innerHTML;
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
Else
{
	?>
	
				    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="datecontract.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
<form Method="post" action="datecontract.php" >
 <div class="grid-x grid-margin-x">
     <div class="large-6 medium-6 small-6 cell"> 
	  <label><?php echo $strCompanyVAT?></label>
	<input name="Cui" Type="text" size="50"  placeholder="<?php echo $strCompanyVAT?>" class="required" />
	  </div>  
	  <div class="large-6 medium-6 small-6 cell"> 
	  <label><?php echo $strAdministrator?></label>
	<input name="Reprezentant" Type="text" size="50" placeholder="<?php echo $strFirstName .' ' . strtoupper($strLastName)?>"  class="required" />
	  </div>
	  </div>
	 
 <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell text-center"> 
	 <input Type="submit" Value="<?php echo $strSearch?>" name="Submit" class="button success" /> 
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