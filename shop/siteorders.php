<?php
//update 8.01.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administrează comenzile";
$strPageTitle="Administrează comenzile";
$url="siteorders.php";
include '../dashboard/header.php';
echo "      <div class=\"grid-x grid-padding-x\">
        <div class=\"large-12 cell\">
<h1>$strPageTitle</h1>
<br />
<a href=\"siteorders.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a><br />";

If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['oID']) AND is_numeric($_GET['oID'])){
	$query="SELECT * FROM magazin_comenzi WHERE comanda_ID='$_GET[oID]'";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result, $query);
If ($numar==0)
{echo $strNoRecordsFound;}
	Else {
$orderr=ezpub_fetch_array($result);
$oID=$orderr['comanda_ID'];
$orderuser=$orderr['comanda_utilizator'];
$itemq="SELECT * FROM magazin_articole where articol_idcomanda=$oID";
$resulti=ezpub_query($conn,$itemq);
$ordertotal=0;
$nume=ezpub_num_rows($resulti,$itemq);
if ($nume==0)
{
echo $strNoRecordsFound;
}
Else {

While ($rowi=ezpub_fetch_array($resulti)) {
$queryp="SELECT * FROM magazin_produse WHERE produs_id='$rowi[articol_produs]'";
$resultp=ezpub_query($conn,$queryp);
$row=ezpub_fetch_array($resultp);
If ($row["produs_dpret"]!=='0.0000')
{
$unitprice=$row['produs_dpret'];
}
Else
{
	$unitprice=$row['produs_pret'];
}
$quantity=$rowi['articol_cantitate'];
$totalprice=$unitprice*$quantity;
$ordertotal=$ordertotal+$totalprice;
$VAT=$totalprice*0.19;
$transportVAT=$transportprice*0.19;
Echo "<h2>$strOrder $oID </h2>";
echo "<table>
  <thead >
    <th width=\"50%\" >$strProduct</th>
	<th width=\"10%\" >$strProductPrice</th>
	<th width=\"20%\" >$strQuantity</th>
	<th width=\"10%\" >$strTotalPrice</th>
	<th width=\"10%\" >$strVAT</th></thead>";
echo "<tr >
<td >$row[produs_nume]</td>
<td align=\"right\" >".romanize($unitprice)."</td>
<td align=\"right\" >$quantity</td>
<td align=\"right\" >".romanize($totalprice)."</td>
<td align=\"right\" >".romanize($VAT)."</td>
</tr>";
}
$totalinterim=$ordertotal*1.19;
If ($totalinterim<=400 AND $paidtransport!="0"){
echo "
<tr>
<td colspan=\"3\">$strTransport</td>
<td align=\"right\">".romanize($transportprice)."</td>
<td align=\"right\">".romanize($transportVAT)."</td>
</tr>";	
$ordertotal=$ordertotal+$transportprice;}
$totalVAT=$ordertotal*0.19;
$finalprice=$ordertotal*1.19;

echo "<tr><td colspan=\"3\" >$strTotal</td><td align=\"right\" >".romanize($ordertotal)."</td><td align=\"right\" >".romanize($totalVAT)."</td></tr>";
echo "<tr><td colspan=\"4\" >$strTotal</td><td align=\"right\" >".romanize($finalprice)."</td></tr>";
echo "</table><br /><br />";
	}}
$query1="SELECT * FROM magazin_cumparatori WHERE cumparator_id='$orderuser'";
$result1=ezpub_query($conn,$query1);
$row1=ezpub_fetch_array($result1);

	
$query2="SELECT * FROM magazin_firme WHERE firma_cumparatorID='$orderuser'";
$result2=ezpub_query($conn,$query2);
$row2=ezpub_fetch_array($result2);

echo "<h3>$strUser</h3>
<table>";
echo"	
	<tr><td width=\"30%\">$strName</td><td width=\"70%\">$row1[cumparator_prenume] $row1[cumparator_nume]</td></tr>
	<tr><td>$strEmail</td><td>$row1[cumparator_email]</td></tr>
	<tr><td>$strPhone</td><td>$row1[cumparator_telefon]</td></tr>
	<tr><td>$strAddress</td><td>$row1[cumparator_adresa]</td></tr>
	<tr><td>$strCity</td><td>$row1[cumparator_oras]</td></tr>
	<tr><td>$strCounty</td><td>$row1[cumparator_judet]</td></tr>
</table><br /><br />		
<h3>$strCompany</h3>
<table>";
echo"	
	<tr><td width=\"30%\">$strCompanyName</td><td width=\"70%\">$row2[firma_nume]</td></tr>
	<tr><td>$strCompanyVAT</td><td>$row2[firma_RO]$row2[firma_CIF]</td></tr>
	<tr><td>$strCompanyRC</td><td>$row2[firma_reg]</td></tr>
	<tr><td>$strCompanyAddress</td><td>$row2[firma_adresa]</td></tr>
	<tr><td>$strCompanyBank</td><td>$row2[firma_banca]</td></tr>
	<tr><td>$strCompanyIBAN</td><td>$row2[firma_IBAN]</td></tr>
</table><br /><br />		
	<a href=\"siteorders.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward\"></i></a><hr>";
	include '../bottom.php';
die;
}
Else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteorders.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM magazin_comenzi WHERE comanda_ID=" .$_GET['oID']. ";";
ezpub_query($conn,$nsql);
$nsql="DELETE FROM magazin_articole WHERE articol_idcomanda=" .$_GET['oID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteorders.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include 'bottom.php';
die;}

Else
{
$query="SELECT comanda_ID, comanda_utilizator, comanda_total, comanda_status, comanda_IP, comanda_deschisa, comanda_inchisa, cumparator_prenume, cumparator_nume, cumparator_telefon, cumparator_email FROM magazin_comenzi, magazin_cumparatori
WHERE magazin_comenzi.comanda_utilizator=magazin_cumparatori.cumparator_id
";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY comanda_deschisa DESC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

if ($nume==0)
{
echo $strNorecordsFound;
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$nume." ".$strOrders ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
<table id="rounded-corner" summary="<?php echo $strOrders?>">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th><?php echo $strDateCreated?></th>
			<th><?php echo $strDateFinished?></th>
			<th><?php echo $strClient?></th>
			<th><?php echo $strPhone?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strTotal?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result2)){
	$vat=$row['comanda_total']*$vatrat;
	$ordertotal=$row['comanda_total']*$vatprc;
    		echo"<tr>
			<td>$row[comanda_ID]</td>
			<td>$row[comanda_deschisa]</td>
			<td>$row[comanda_inchisa]</td>
			<td>$row[cumparator_prenume] $row[cumparator_nume]</td>
			<td>$row[cumparator_telefon]</td>
			<td>$row[cumparator_email]</td>
			<td>". romanize($row["comanda_total"])."</td>
			<td>". romanize($vat)."</td>
			<td>". romanize($ordertotal)."</td>
			<td><a href=\"siteorders.php?mode=view&oID=$row[comanda_ID]\" ><i class=\"fas fa-info\"></i></a></td>
			<td><a href=\"siteorders.php?mode=delete&oID=$row[comanda_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody></table>";
?>
<div class="paginate">
<?php
echo $pages->display_pages();
?>
</div>

<?php 
}
}
?>
</div>
</div>
<hr>
<?php
include '../bottom.php';
?>