<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Vizualizare profil client";
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
	header("location:$strSiteURL/login/index.php?message=MLF");
	die;
}

// Validare și sanitizare parametru cID
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    header("location:$strSiteURL/clients/siteclients.php?message=INVALID_ID");
    die;
}
$clientID = (int)$_GET['cID'];

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
                <?php if ($_SESSION['clearence']=='USER')
{
           echo     "<p><a href=\"siteuserclients.php\" class=\"button\">$strBack<i class=\"fas fa-backward\"></i></a>";
             }
              else {
                  echo "<p><a href=\"siteclients.php\" class=\"button\">$strBack<i class=\"fas fa-backward\"></i></a>";
              }?>
            </div>
        </div>
        <ul class="tabs" data-tabs id="example-tabs">
            <li class="tabs-title is-active"><a href="#panel1" aria-selected="true"><?php echo $strClientProfile?></a>
            </li>
            <li class="tabs-title"><a href="#panel2"><?php echo $strClientAspects?></a></li>
            <li class="tabs-title"><a href="#panel3"><?php echo $strAuthorizations?></a></li>
            <li class="tabs-title"><a href="#panel4"><?php echo $strActivities?></a></li>
            <li class="tabs-title"><a href="#panel5"><?php echo $strContact?></a></li>
            <li class="tabs-title"><a href="#panel6"><?php echo $strVisits?></a></li>
            <?php if ($_SESSION['clearence']=='ADMIN')
{           echo "<li class=\"tabs-title\"><a href=\"#panel7\">$strInvoices</a></li>
            <li class=\"tabs-title\"><a href=\"#panel8\">$strBalances</a></li>
            <li class=\"tabs-title\"><a href=\"#panel9\">$strFiscalData</a></li>
            <li class=\"tabs-title\"><a href=\"#panel10\">$strCourtTrials</a></li>";
        }
        ?>
        </ul>

        <div class="tabs-content" data-tabs-content="example-tabs">
            <div class="tabs-panel is-active" id="panel1">
                <?php
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
mysqli_stmt_close($stmt);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>
                                <h4><?php echo $strClientProfile?></h4>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
While ($row=mysqli_fetch_array($result, MYSQLI_ASSOC)){
    		echo"
			<tr><td>$strName</td><td colspan=\"2\">".htmlspecialchars($row['Client_Denumire'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strActivity</td><td colspan=\"2\">".htmlspecialchars($row['Client_Descriere_Activitate'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strAddress</td><td colspan=\"2\">".htmlspecialchars($row['Client_Adresa'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCompanyVAT</td><td colspan=\"2\">".htmlspecialchars($row['Client_CUI'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCompanyRC</td><td colspan=\"2\">".htmlspecialchars($row['Client_RC'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCAENCode</td><td colspan=\"2\">".htmlspecialchars($row['Client_Cod_CAEN'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strEmployees</td><td colspan=\"2\">".htmlspecialchars($row['Client_Numar_Angajati'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strPhone</td><td colspan=\"2\">".htmlspecialchars($row['Client_Telefon'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strEmail</td><td colspan=\"2\">".htmlspecialchars($row['Client_Email'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strWWW</td><td colspan=\"2\">".htmlspecialchars($row['Client_Web'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCompanyBank</td><td colspan=\"2\">".htmlspecialchars($row['Client_Banca'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCompanyIBAN</td><td colspan=\"2\">".htmlspecialchars($row['Client_IBAN'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCity</td><td colspan=\"2\">".htmlspecialchars($row['Client_Localitate'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strCounty</td><td colspan=\"2\">".htmlspecialchars($row['Client_Judet'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>
			<tr><td>$strProfile</td><td colspan=\"2\">".htmlspecialchars($row['Client_Caracterizare'] ?? '', ENT_QUOTES, 'UTF-8')."</td></tr>";
}
echo "</tbody><tfoot><tr><td></td><td><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
?>
            </div>
            <div class="tabs-panel" id="panel2">
                <?php
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_fisa WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$stmt2 = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, Client_Denumire FROM clienti_date WHERE clienti_date.ID_Client=?");
mysqli_stmt_bind_param($stmt2, 'i', $clientID);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$rss = mysqli_fetch_array($result2, MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);
 ?>

                <div class="grid-x grid-margin-x">
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strClient?></label>
                        <?php echo $rss["Client_Denumire"]?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strWasteManagement?></label>
                        <?php If ($row["fisa_GD"]==0) echo $strYes; else echo $strNo; ?>
                        <label><?php echo $strDSPReporting?></label>
                        <?php If ($row["fisa_raportare_DSP"]==0) echo $strYes; else echo $strNo; ?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strPackageManagement?></label>
                        <?php If ($row["fisa_GA"]==0) echo $strYes; else echo $strNo; ?>
                        <label><?php echo $strOTRName?></label>
                        <?php echo $row["fisa_OTR"]?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strEEEManagement?></label>
                        <?php If ($row["fisa_DEE"]==0) echo $strYes; else echo $strNo; ?>
                        <label><?php echo $strOTRName?></label>
                        <?php echo $row["fisa_OTR_EE"]?>
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strBateryManagement?></label>
                        <?php If ($row["fisa_baterii"]==0) echo $strYes; else echo $strNo; ?>
                        <label><?php echo $strOTRName?></label>
                        <?php echo $row["fisa_OTR_BAT"]?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strTiresManagement?></label>
                        <?php If ($row["fisa_anvelope"]==0) echo $strYes; else echo $strNo; ?>
                        <label><?php echo $strOTRName?></label>
                        <?php echo $row["fisa_OTR_Anvelope"]?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strOilManagement?></label>
                        <?php If ($row["fisa_uleiuri"]==0) echo $strYes; else echo $strNo; ?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strChemicalsManagement?></label>
                        <?php If ($row["fisa_substante"]==0) echo $strYes; else echo $strNo; ?>
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strCOVManagement?></label>
                        <?php If ($row["fisa_COV"]==0) echo $strYes; else echo $strNo; ?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strEmmissionsManagement?></label>
                        <?php If ($row["fisa_emisii_stationare"]==0) echo $strYes; else echo $strNo; ?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strWaterMonitoring?></label>
                        <?php If ($row["fisa_monitorizari_apa"]==0) echo $strYes; else echo $strNo;?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <TD colspan="2"><?php echo $strDetails?><br />
                            <?php echo $row["fisa_detalii_monitorizari_apa"]?>
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strAirMonitoring?></label>
                        <?php If ($row["fisa_monitorizari_aer"]==0) echo $strYes; else echo $strNo;?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <TD colspan="2"><?php echo $strDetails?><br />
                            <?php echo $row["fisa_detalii_monitorizari_aer"]?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strSoilMonitoring?></label>
                        <?php If ($row["fisa_monitorizari_sol"]==0) echo $strYes; else echo $strNo;?>
                    </div>
                    <div class="large-3 medium-3 small-3 cell">
                        <label><?php echo $strDetails?><label>
                                <?php echo $row["fisa_detalii_monitorizari_sol"]?>
                    </div>
                </div>
                <div class="grid-x grid-margin-x">
                    <div class="large-12 medium-12 small-12 cell">
                        <label><?php echo $strOther?><label>
                                <?php echo $row["fisa_alte_detalii"]?>
                    </div>
                </div>
            </div>
            <div class="tabs-panel" id="panel3">
                <?php

echo "<a href=\"siteclientauthorizations.php?mode=new\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>";
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_autorizatii_clienti.ID_Client, clienti_autorizatii_clienti.ID_Autorizatie, clienti_autorizatii_clienti.Autorizatie_Client_Emitere, clienti_autorizatii_clienti.Autorizatie_Client_Expirare, 
clienti_autorizatii.ID_autorizatii, Autorizatie,
ID_Autorizatie_Client
FROM 
clienti_autorizatii_clienti, clienti_date, clienti_autorizatii
WHERE
clienti_autorizatii_clienti.ID_Client=? AND clienti_date.ID_Client=clienti_autorizatii_clienti.ID_Client AND clienti_autorizatii_clienti.ID_Autorizatie=clienti_autorizatii.ID_autorizatii
ORDER By Client_Denumire ASC");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
mysqli_stmt_close($stmt);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
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

?>
            </div>
            <div class="tabs-panel" id="panel4">
                <?php
echo "<a href=\"siteclientactivities.php?mode=new\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a>";

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_activitati_clienti.ID_Client, clienti_activitati_clienti.ID_Activitate, clienti_activitati_clienti.Activitate_Client_Frecventa, clienti_activitati_clienti.Activitate_Client_Termen,
clienti_activitati_clienti.ID_Activitate, clienti_activitati_lista.Activitate_Nume, ID_activitati_client
FROM clienti_activitati_lista, clienti_date, clienti_activitati_clienti
WHERE  clienti_activitati_clienti.ID_Client=? AND clienti_date.ID_Client=clienti_activitati_clienti.ID_Client AND clienti_activitati_clienti.ID_Activitate=clienti_activitati_lista.ID_Activitate
ORDER By Client_Denumire ASC");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar=ezpub_num_rows($result,$stmt);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
                <table width="100%">
                    <thead>
                        <tr>
                            <th width="50%"><?php echo $strClient?></th>
                            <th width="30%"><?php echo $strClientAspects?></th>
                            <th width="10%"><?php echo $strFrequency?></th>
                            <th width="5%"><?php echo $strEdit?></th>
                            <th width="5%"><?php echo $strDelete?></th>
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
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}

?>
            </div>

            <div class="tabs-panel" id="panel5">
                <?php
	echo "<a href=\"sitecontacts.php?mode=new\" class=\"button\"><i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i>&nbsp;$strAdd</a>";
	// Prepared statement pentru SQL injection prevention
	$stmt = mysqli_prepare($conn, "SELECT clienti_date.ID_Client, clienti_date.Client_Denumire, clienti_date.Client_Aloc, 
clienti_contacte.contact_ID, clienti_contacte.client_ID, clienti_contacte.contact_nume, clienti_contacte.contact_prenume,  clienti_contacte.contact_telefon, clienti_contacte.contact_email, clienti_contacte.contact_tip
FROM clienti_contacte, clienti_date
WHERE clienti_contacte.client_ID=? AND clienti_date.ID_Client=clienti_contacte.client_ID
ORDER By Client_Denumire ASC");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
mysqli_stmt_close($stmt);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
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
            <div class="tabs-panel" id="panel6">
                <?php 
$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
Client_Denumire, ID_Client
FROM clienti_vizite, clienti_date WHERE 
ID_Client='$_GET[cID]' AND ID_Client=client_vizita 
ORDER BY data_vizita DESC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
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
?>
            </div>
                        <?php if ($_SESSION['clearence']=='ADMIN')
{     ?>
            <div class="tabs-panel" id="panel7">
                <?php 

// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT * from facturare_facturi WHERE factura_client_ID=?");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
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
else
{
	If ($row["factura_client_achitat"]=="0") 
	{
echo "<tr class=\"notpaid\">";
} 
else 
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
	echo "<td>". date("d.m.Y", strtotime($row["factura_client_data_achitat"]))."</td>
	<td>$row[factura_client_zile_achitat]</td>";
	
}	
else 	{
	echo "<td>&nbsp;</td>
	<td>&nbsp;</td>
	";
}	
	echo	"
			<td>$row[factura_client_alocat]</td>";
}
echo "</table>";
}
?>
            </div>
            <div class="tabs-panel" id="panel8">
                <?php
// Prepared statement pentru SQL injection prevention
$stmt = mysqli_prepare($conn, "SELECT Client_CIF from clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
 
$cui = htmlspecialchars($row['Client_CIF'] ?? '', ENT_QUOTES, 'UTF-8');
$cui_numeric = preg_replace('/\D/', '', $cui);
echo $cui_numeric;
// Importă și afișează bilanțuri ANAF dacă CUI numeric valid
if ($cui_numeric && is_numeric($cui_numeric) && (int)$cui_numeric > 0) {
    $_GET['cui'] = $cui_numeric;
    include_once '../anaf/balancesview.php';
    unset($_GET['cui']);
}

 ?>
            </div>
            <div class="tabs-panel" id="panel9">
                <!--   show fiscal data -->
                <?php
                $stmt = mysqli_prepare($conn, "SELECT Client_CIF from clienti_date WHERE ID_Client=?");
mysqli_stmt_bind_param($stmt, 'i', $clientID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
 
$cui = htmlspecialchars($row['Client_CIF'] ?? '', ENT_QUOTES, 'UTF-8');
$cui_numeric = preg_replace('/\D/', '', $cui);

// Importă și afișează date fiscale ANAF dacă CUI numeric valid
if ($cui_numeric && is_numeric($cui_numeric) && (int)$cui_numeric > 0) {
    $_GET['cui'] = $cui_numeric;
    include_once '../anaf/fiscalview.php';
    unset($_GET['cui']);
}
?>
            </div>
            <div class="tabs-panel" id="panel10">
                <!--   show trials data -->
                <?php
                // Load client name and include the JUST search as an embedded tab.
                $stmt = mysqli_prepare($conn, "SELECT Client_Denumire FROM clienti_date WHERE ID_Client=?");
                mysqli_stmt_bind_param($stmt, 'i', $clientID);
                mysqli_stmt_execute($stmt);
                $result_j = mysqli_stmt_get_result($stmt);
                $row_j = mysqli_fetch_array($result_j, MYSQLI_ASSOC);
                mysqli_stmt_close($stmt);

                $Client_Denumire = $row_j['Client_Denumire'] ?? '';
                // Include the search UI from common; `just_query.php` detects being included and will auto-run search using $Client_Denumire
                include_once __DIR__ . '/../common/just_query.php';
                ?>
            </div>
            <?php } // <-- CLOSE the if block for ADMIN
            ?>
        </div>


    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>