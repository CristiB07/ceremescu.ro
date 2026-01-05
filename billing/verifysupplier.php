<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Verificare furnizor";
include '../dashboard/header.php';

// Validate CUI input
if (!isset($_GET['cui']) || !preg_match('/^\d{6,10}$/', $_GET['cui'])) {
    die("Invalid CUI format. Must be 6-10 digits.");
}
$cui = $_GET['cui'];
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
        <p><a href="verifymessages.php?mode=verify" class="button"><?php echo $strBack?>&nbsp;<i
                    class="fas fa-backward fa-xl"></i></a></p>
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
  $stmt = mysqli_prepare($conn, "SELECT Localitate FROM generale_coduripostale WHERE Codpostal = ?");
  mysqli_stmt_bind_param($stmt, "s", $codpostal);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
$oras=$row["Localitate"];
}
else
{
	if ($judet=='MUNICIPIUL BUCUREșTI')
	{$oras="BUCUREȘTI";}
	else 
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
			<td>".htmlspecialchars($denumire, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($tva.$cif, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($numar_reg_com, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($adresa, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($oras, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($judet, ENT_QUOTES, 'UTF-8')."</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
		
    } 
    else // get data from ANAF
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
  $stmt = mysqli_prepare($conn, "SELECT Localitate FROM generale_coduripostale WHERE Codpostal = ?");
  mysqli_stmt_bind_param($stmt, "s", $codpostal);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
$oras=$row["Localitate"];
}
else
{
	if ($judet=='MUNICIPIUL BUCUREșTI')
	{$oras="BUCUREȘTI";}
	else 
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
			<td>".htmlspecialchars($denumire, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($tva.$cif, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($numar_reg_com, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($adresa, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($oras, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($judet, ENT_QUOTES, 'UTF-8')."</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
		
}
else
{
		// go on with ANAF
$dategenerale=$data['found'][0];
$judet=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Judet']);
$oras=strtoupper($dategenerale['adresa_sediu_social']['sdenumire_Localitate']);

If (!empty($dategenerale['inregistrare_scop_Tva']['scpTVA']))
{
$tva="RO";
}
else
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
			<td>".htmlspecialchars($denumire, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($tva.$cif, ENT_QUOTES, 'UTF-8')."</td>
			<td>".htmlspecialchars($numar_reg_com, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($adresa, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($oras, ENT_QUOTES, 'UTF-8')."</td>        
			<td>".htmlspecialchars($judet, ENT_QUOTES, 'UTF-8')."</td>        
			</tr>
			</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";

}
	}
echo "</div></div>";
include '../bottom.php';		
?>