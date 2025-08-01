<?php
//update 01.08.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Date financiare clienți";
include '../dashboard/header.php';
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php If ($_SERVER['REQUEST_METHOD'] == 'POST'){
echo "<h1>$strPageTitle</h1>";
print "<a href=\"siteclientfinancials.php\" class=\"button\">$strBack&nbsp;<i class=\"large fas  fa-backward\" title=\"$strBack\"></i></a>";

$cuicomplet=$_POST['cui'];
$cui = preg_replace("/[^0-9]/", "", $cuicomplet);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.openapi.ro/api/companies/$cui/balances");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_HTTPHEADER, array($openapikey));
if( ! $response = curl_exec($ch)) 
    { 
        trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
	$obj = json_decode($response, true);
	array_multisort(array_column($obj, 'year'), SORT_DESC, $obj);
	$query="SELECT * FROM clienti_date where Client_CUI = '$cuicomplet'";
	$result=ezpub_query($conn,$query);
	$row=ezpub_fetch_array($result);
	echo"<table>
			<tr><td>$strName</td><td colspan=\"2\">$row[Client_Denumire]</td></tr>
			<tr><td>$strActivity</td><td colspan=\"2\">$row[Client_Descriere_Activitate]</td></tr>
			<tr><td>$strAddress</td><td colspan=\"2\">$row[Client_Adresa]</td></tr>
			<tr><td>$strCompanyVAT</td><td colspan=\"2\">$row[Client_CUI]</td></tr>
			<tr><td>$strCompanyRC</td><td colspan=\"2\">$row[Client_RC]</td></tr>
			<tr><td>$strCAENCode</td><td colspan=\"2\">" . $obj[0]['caen_code']." - ".$obj[1]['data']['caen_descriere']."</td></tr>
			<tr><td>$strEmployees</td><td colspan=\"2\">$row[Client_Numar_Angajati]</td></tr>
			<tr><td>$strPhone</td><td colspan=\"2\">$row[Client_Telefon]</td></tr>
			<tr><td>$strEmail</td><td colspan=\"2\">$row[Client_Email]</td></tr>
			<tr><td>$strWWW</td><td colspan=\"2\">$row[Client_Web]</td></tr>
			<tr><td>$strCompanyBank</td><td colspan=\"2\">$row[Client_Banca]</td></tr>
			<tr><td>$strCompanyIBAN</td><td colspan=\"2\">$row[Client_IBAN]</td></tr>
			<tr><td>$strCity</td><td colspan=\"2\">$row[Client_Localitate]</td></tr>
			<tr><td>$strCounty</td><td colspan=\"2\">$row[Client_Judet]</td></tr>
			<tr><td>$strProfile</td><td colspan=\"2\">$row[Client_Caracterizare]</td></tr>
			</table>
			<br />
			<br />
	
<table class=\"hover\">
<thead>
<tr>
<td>An</td>
<td>Capital social</td>
<td>Cifra de afaceri</td>
<td>Venituri totale</td>
<td>Profit/Pierdere (net)</td>
<td>Profit/Pierdere (brută)</td>
<td>Salariați</td>
<td>Datorii</td>
<td>Creanțe</td>
<td>Cheltuieli totale</td>
<td>Disponibil cash</td>
<td>Capitaluri total</td>
<td>Active imobilizate</td>
<td>Active circulante</td>
</tr>
</thead>
<tbody>";
 foreach($obj as $index => $value) {
	 echo "<tr>";
     If (!empty($value['year'])) {print "<td align=\"right\">"  . $value['year']."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['capitaluri_capital'])){print "<td align=\"right\">"  . number_format($value['data']['capitaluri_capital'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['cifra_de_afaceri_neta'])){print "<td align=\"right\">"  . number_format($value['data']['cifra_de_afaceri_neta'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['venituri_totale'])){print "<td align=\"right\">"  . number_format($value['data']['venituri_totale'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['profit_net'])){print "<td align=\"right\">" . number_format($value['data']['profit_net'],0,",",".")."</td>";} elseIf (!empty($value['data']['pierdere_neta'])) {print "<td class=\"loss\" align=\"right\">- ". number_format($value['data']['pierdere_neta'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['profit_brut'])){print "<td align=\"right\">"  . number_format($value['data']['profit_brut'],0,",",".")."</td>";} elseIf (!empty($value['data']['pierdere_bruta'])) {print "<td class=\"loss\" align=\"right\">- ". number_format($value['data']['pierdere_bruta'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['numar_mediu_de_salariati'])){print "<td align=\"right\">"  . number_format($value['data']['numar_mediu_de_salariati'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['datorii_total'])){print "<td align=\"right\">"  . number_format($value['data']['datorii_total'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['creante'])){print "<td align=\"right\">"  . number_format($value['data']['creante'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['cheltuieli_totale'])){print "<td align=\"right\">"  . number_format($value['data']['cheltuieli_totale'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['casa_si_conturi'])){print "<td align=\"right\">"  . number_format($value['data']['casa_si_conturi'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['capitaluri_total'])){print "<td align=\"right\">"  . number_format($value['data']['capitaluri_total'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['active_imobilizate_total'])){print "<td align=\"right\">"  . number_format($value['data']['active_imobilizate_total'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
     If (!empty($value['data']['active_circulante_total'])){print "<td align=\"right\">"  . number_format($value['data']['active_circulante_total'],0,",",".")."</td>";} else {print "<td align=\"right\">-</td>";};
 echo "</tr>";}
 echo "</tbody><tfoot><tr><td></td><td  colspan=\"12\"><em></em></td><td>&nbsp;</td></tr></tfoot></table></div></div>";
 include '../bottom.php';
}
Else {

?>
<h1><?php echo $strPageTitle?></h1>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<form method="POST" action="siteclientfinancials.php">
<label><?php echo $strPick?></label>
<select name="cui" class="required">
           <option value=""><?php echo $strClient?></option>
          <?php $sql = "Select * FROM clienti_date ORDER BY Client_Denumire ASC";
        $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
          <option  value="<?php echo $rss["Client_CUI"]?>"><?php echo $rss["Client_Denumire"]?></option>
          <?php
}?>
        </select>
		</div>
		<div class="large-12 medium-12 small-12 cell text-center">
	 <input Type="submit" Value="<?php echo $strSearch?>" name="Submit" class="button success" /> 
	</div>
	</div>
</form>
</div>
</div>
<?php
 include '../bottom.php';
}
?>