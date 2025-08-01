<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Vizualizare profil client";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
?>
		    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="siteclients.php" class="button"><?php echo $strBack?> <i class="fas fa-backward"></i></a></p>
</div>
</div>
<ul class="tabs" data-tabs id="example-tabs">
  <li class="tabs-title is-active"><a href="#panel1" aria-selected="true"><?php echo $strClientProfile?></a></li>
  <li class="tabs-title"><a href="#panel2"><?php echo $strAuthorizations?></a></li>
  <li class="tabs-title"><a href="#panel3"><?php echo $strActivities?></a></li>
  <li class="tabs-title"><a href="#panel4"><?php echo $strContact?></a></li>
  <li class="tabs-title"><a href="#panel5"><?php echo $strVisits?></a></li>
  <li class="tabs-title"><a href="#panel6"><?php echo $strInvoices?></a></li>
  <li class="tabs-title"><a href="#panel7"><?php echo $strBalances?></a></li>
</ul>

<div class="tabs-content" data-tabs-content="example-tabs">
<div class="tabs-panel is-active" id="panel1">
<a href="siteclients.php?mode=edit&cID=<?php echo $_GET['cID']?>" class="ask"><i class="far fa-edit fa-xl" title="<?php $strEdit?>"></i></a><br />
<?php
$query="SELECT * FROM clienti_date where ID_Client=$_GET[cID]";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th>&nbsp;</th>
			<th><h4><?php echo $strClientProfile?></h4></th>
			<th>&nbsp;</th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"
			<tr><td>$strName</td><td colspan=\"2\">$row[Client_Denumire]</td></tr>
			<tr><td>$strActivity</td><td colspan=\"2\">$row[Client_Descriere_Activitate]</td></tr>
			<tr><td>$strAddress</td><td colspan=\"2\">$row[Client_Adresa]</td></tr>
			<tr><td>$strCompanyVAT</td><td colspan=\"2\">$row[Client_CUI]</td></tr>
			<tr><td>$strCompanyRC</td><td colspan=\"2\">$row[Client_RC]</td></tr>
			<tr><td>$strCAENCode</td><td colspan=\"2\">$row[Client_Cod_CAEN]</td></tr>
			<tr><td>$strEmployees</td><td colspan=\"2\">$row[Client_Numar_Angajati]</td></tr>
			<tr><td>$strPhone</td><td colspan=\"2\">$row[Client_Telefon]</td></tr>
			<tr><td>$strEmail</td><td colspan=\"2\">$row[Client_Email]</td></tr>
			<tr><td>$strWWW</td><td colspan=\"2\">$row[Client_Web]</td></tr>
			<tr><td>$strCompanyBank</td><td colspan=\"2\">$row[Client_Banca]</td></tr>
			<tr><td>$strCompanyIBAN</td><td colspan=\"2\">$row[Client_IBAN]</td></tr>
			<tr><td>$strCity</td><td colspan=\"2\">$row[Client_Localitate]</td></tr>
			<tr><td>$strCounty</td><td colspan=\"2\">$row[Client_Judet]</td></tr>
			<tr><td>$strProfile</td><td colspan=\"2\">$row[Client_Caracterizare]</td></tr>";
}
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
?>
 </div>
 <div class="tabs-panel" id="panel2">
<?php

echo "<a href=\"siteclientauthorizations.php?mode=new\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
/*
$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
date_autorizatii_clienti.ID_Client, date_autorizatii_clienti.ID_Autorizatie, date_autorizatii_clienti.Autorizatie_Client_Emitere, date_autorizatii_clienti.Autorizatie_Client_Expirare, 
date_autorizatii.ID_autorizatii, Autorizatie,
ID_Autorizatie_Client
FROM 
date_autorizatii_clienti, clienti_date, date_autorizatii
WHERE
date_autorizatii_clienti.ID_Client=$_GET[cID] AND clienti_date.ID_Client=date_autorizatii_clienti.ID_Client AND date_autorizatii_clienti.ID_Autorizatie=date_autorizatii.ID_autorizatii
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table id="rounded-corner" summary="<?php echo $strAuthorizations?>" width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strExpiryDate?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Autorizatie]</td>
			<td>$row[Autorizatie_Client_Emitere]</td>
			<td>$row[Autorizatie_Client_Expirare]</td>
			  <td><a href=\"siteclientauthorizations.php?mode=edit&cID=$row[ID_Autorizatie_Client]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteclientauthorizations.php?mode=delete&cID=$row[ID_Autorizatie_Client]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
*/
?>
  </div>
  <div class="tabs-panel" id="panel3">
 <?php
echo "<a href=\"siteclientactivities.php?mode=new\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
/*
 $query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
date_activitati_clienti.ID_Client, date_activitati_clienti.ID_Activitate, date_activitati_clienti.Activitate_Client_Frecventa, date_activitati_clienti.Activitate_Client_Termen,
date_activitati.ID_Activitate, date_activitati.Activitate_Nume, ID_activitati_client
FROM date_activitati_clienti, clienti_date, date_activitati
WHERE  date_activitati_clienti.ID_Client=$_GET[cID] AND clienti_date.ID_Client=date_activitati_clienti.ID_Client AND date_activitati_clienti.ID_Activitate=date_activitati.ID_Activitate
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th</th>
			<th><?php echo $strTitle?></th>
			<th><?php echo $strFrequency?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[Activitate_Nume]</td>
			<td>$row[Activitate_Client_Frecventa]</td>
			  <td><a href=\"siteclientactivities.php?mode=edit&cID=$row[ID_activitati_client]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"siteclientactivities.php?mode=delete&cID=$row[ID_activitati_client]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"2\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
*/
?>
  </div>
  
    <div class="tabs-panel" id="panel4">
	<?php
	echo "<a href=\"sitecontacts.php?mode=new\"><i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a>";
	$query="SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_contacte.contact_ID, clienti_contacte.client_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume,  clienti_contacte.contact_telefon, clienti_contacte.contact_email, clienti_contacte.contact_tip
FROM clienti_contacte, clienti_date
WHERE clienti_contacte.client_ID='$_GET[cID]' AND clienti_date.ID_Client=clienti_contacte.client_ID
ORDER By Client_Denumire ASC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strClient?></th>
			<th><?php echo $strContact?></th>
			<th><?php echo $strFunction?></th>
			<th><?php echo $strPhone?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strEdit?></th>
			<th scope="col" class="rounded-q4"><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[contact_prenume]". " " ."$row[contact_nume]</td>
			<td>$row[contact_tip]</td>
			<td>$row[contact_telefon]</td>
			<td>$row[contact_email]</td>
			  <td><a href=\"sitecontacts.php?mode=edit&cID=$row[contact_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"sitecontacts.php?mode=delete&cID=$row[contact_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
	?>
  </div>
  <div class="tabs-panel" id="panel5">
 <?php 
 /*
$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
Client_Denumire, ID_Client
FROM date_vizite_clienti, clienti_date WHERE 
ID_Client='$_GET[cID]' AND ID_Client=client_vizita 
ORDER BY data_vizita DESC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<table width="100%">
	      <thead>
    	<tr>
  		<th><?php echo $strTitle?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strScope?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDetails?></th>
	      </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[Client_Denumire]</td>
			<td>$row[data_vizita]</td>
			<td>$row[scop_vizita]</td>
			 <td><a href=\"sitereports.php?mode=edit&cID=$row[ID_vizita]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"sitereports.php?mode=view&cID=$row[ID_vizita]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strView\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
*/
?>
  </div>
 <div class="tabs-panel" id="panel6">
 <?php 
 /*
 $query="SELECT * from clienti_date_facturi WHERE factura_client_ID='$_GET[cID]'";
 $result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>

 <table width="100%" class="unstriped">
	      <thead>
    	<tr>
			<th><?php echo $strNumber?></th>
			<th><?php echo $strIssuedDate?></th>
			<th><?php echo $strClient?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strTotal?></th>
			<th><?php echo $strPaymentDate?></th>
			<th><?php echo $strDays?></th>
			<th><?php echo $strSeenBy?></th>
	      </tr>
		</thead>
<?php 
While ($row=ezpub_fetch_array($result)){
		If ($row["factura_client_anulat"]=="1") 
	{echo "<tr class=\"canceled\">";}
Else
{
	If ($row["factura_client_achitat"]=="0") 
	{
echo "<tr class=\"notpaid\">";
} 
Else 
{
echo "<tr class=\"paid\">";}
}
    		echo"<td>$row[factura_numar]</td>
			<td>". date("d.m.Y",strtotime($row["factura_data_emiterii"]))."</td>
			<td width=\"15%\">$row[factura_client_denumire]</td>
			<td align=\"right\">". romanize($row["factura_client_valoare"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_tva"])."</td>
			<td align=\"right\">". romanize($row["factura_client_valoare_totala"])."</td>";
If ($row["factura_client_achitat"]=="1") {
	echo "<td>". date("d.m.Y", strtotime($row["factura_client_data_achitat"]))."</td>";
	
}	
else 	{
	echo "<td>&nbsp;</td>";
}	
	echo	"<td>$row[factura_client_zile_achitat]</td>
			<td>$row[factura_client_alocat]</td>";
}
echo "</table>";
}
 */
?>
</div>
 <div class="tabs-panel" id="panel7">
 <?php
 $query="SELECT Client_CIF from clienti_date WHERE ID_Client='$_GET[cID]'";
 $result=ezpub_query($conn,$query);
 $row=ezpub_fetch_array($result);
 
$cuicomplet=$row['Client_CIF'];
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
	$query="SELECT * FROM clienti_date where Client_CIF = '$cuicomplet'";
	$result=ezpub_query($conn,$query);
	$row=ezpub_fetch_array($result);
	echo"	
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
 echo "</tbody><tfoot><tr><td></td><td  colspan=\"12\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
 ?>
  </div>
 
  </div>

 
</div>
</div>
<hr/>
<?php
include '../bottom.php';
?>