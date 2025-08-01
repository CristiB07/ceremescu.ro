<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare furnizor";
include '../dashboard/header.php';

$cui=$_GET['cui'];
$postfields = [];
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";

?>
</div>
</div>
	  
	  			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="verify_messages.php?mode=verify" class="button"><?php echo $strBack?></a></p>
</div>
</div>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
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
?>
<table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strSupplier?></th>
			<th><?php echo $strCompanyVAT?></th>
			<th><?php echo $strCompanyRC?></th>
			<th><?php echo $strAddress?></th>
			<th><?php echo $strCity?></th>
			<th><?php echo $strCounty?></th>
	      </tr>
		</thead>
		<?php
echo"<tr>
			<td>$denumire</td>
			<td>$tva$cif</td>
			<td>$numar_reg_com</td>        
			<td>$adresa</td>        
			<td>$oras</td>        
			<td>$judet</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
		
    } 
    Else // get data from ANAF
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
?>
<table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strSupplier?></th>
			<th><?php echo $strCompanyVAT?></th>
			<th><?php echo $strCompanyRC?></th>
			<th><?php echo $strAddress?></th>
			<th><?php echo $strCity?></th>
			<th><?php echo $strCounty?></th>
	      </tr>
		</thead>
		<?php
echo"<tr>
			<td>$denumire</td>
			<td>$tva$cif</td>
			<td>$numar_reg_com</td>        
			<td>$adresa</td>        
			<td>$oras</td>        
			<td>$judet</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
		
}
Else
{
		// go on with ANAF
$dategenerale=$data['found'][0];
$judet=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Judet']);
$oras=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Localitate']);

If (!empty($dategenerale['inregistrare_scop_Tva']['scpTVA']))
{
$tva="RO";
}
Else
{
	$tva="";
	}
$numar_reg_com=$dategenerale['date_generale']['nrRegCom'];
$cif=$dategenerale['date_generale']['cui'];
$denumire=strtoupper($dategenerale['date_generale']['denumire']);
$adresa=$dategenerale['date_generale']['adresa'];
$codpostal=$dategenerale['date_generale']['codPostal'];
$oras=str_replace("MUN. ","",$oras); 
If ($judet=='MUNICIPIUL BUCUREŞTI')
{
	$oras=str_replace("BUCUREşTI","",$oras);
	$oras=str_replace(" ","",$oras);
 }

$datecontract= $denumire. ", cu sediul social în ". $adresa . ", oraș ".$oras.", județul ".$judet.", înregistrată la Registrul Comerțului sub nr. ".$numar_reg_com.", CUI ".$tva." ".$cif . " sursa date ANAF";
?>
<table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strSupplier?></th>
			<th><?php echo $strCompanyVAT?></th>
			<th><?php echo $strCompanyRC?></th>
			<th><?php echo $strAddress?></th>
			<th><?php echo $strCity?></th>
			<th><?php echo $strCounty?></th>
	      </tr>
		</thead>
		<?php
echo"<tr>
			<td>$denumire</td>
			<td>$tva$cif</td>
			<td>$numar_reg_com</td>        
			<td>$adresa</td>        
			<td>$oras</td>        
			<td>$judet</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}
	}
echo "</div></div>";
include '../bottom.php';		
?>