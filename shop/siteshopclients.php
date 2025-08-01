<?php
//update 8.01.2025

include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strDescription="Administreaza cumpărătorii";
$strPageTitle="Administreaza cumpărătorii";
$url="siteshopclients.php";
?>

<?php
include '../dashboard/header.php';
echo "      <div class=\"grid-x grid-padding-x\">
        <div class=\"large-12 medium-12 small-12 cell\">
<h1>$strPageTitle</h1>";

If (IsSet($_GET['mode']) AND $_GET['mode']=="view"){
If (IsSet($_GET['bID']) AND is_numeric($_GET['bID'])){
	$query="SELECT * FROM magazin_cumparatori WHERE cumparator_id='$_GET[bID]'";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);

	
$query2="SELECT * FROM magazin_firme WHERE firma_cumparatorID='$_GET[bID]'";
$result2=ezpub_query($conn,$query2);
$nume=ezpub_num_rows($result2,$query2);

echo "<h3>$strUser</h3><table>";
echo"	<tr><td width=\"30%\">$strName</td><td width=\"70%\">$row[cumparator_prenume] $row[cumparator_nume]</td></tr>
	<tr><td>$strEmail</td><td>$row[cumparator_email]</td></tr>
	<tr><td>$strPhone</td><td>$row[cumparator_telefon]</td></tr>
	<tr><td>$strAddress</td><td>$row[cumparator_adresa]</td></tr>
	<tr><td>$strCity</td><td>$row[cumparator_oras]</td></tr>
	<tr><td>$strCounty</td><td>$row[cumparator_judet]</td></tr>
	
</table>
<h3>$strCompany</h3>";
if ($nume==0)
{
echo $strNoRecordsFound;
}
Else {		
While ($row2=ezpub_fetch_array($result2)){
echo "<table>";
echo"<tr><td width=\"30%\">$strCompanyName</td><td width=\"70%\">$row2[firma_nume]</td></tr>
	<tr><td>$strCompanyVAT</td><td>$row2[firma_RO] $row2[firma_CIF]</td></tr>
	<tr><td>$strCompanyRC</td><td>$row2[firma_reg]</td></tr>
	<tr><td>$strCompanyAddress</td><td>$row2[firma_adresa]</td></tr>
	<tr><td>$strCompanyBank</td><td>$row2[firma_banca]</td></tr>
	<tr><td>$strCompanyIBAN</td><td>$row2[firma_IBAN]</td></tr>
</table><br /><br />";		
}}
	echo"<br /><br /><a href=\"siteshopclients.php\" class=\"button right\">Înapoi</a></div></div>";
}
Else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; 
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteshopclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM magazin_cumparatori WHERE cumparator_id=" .$_GET['bID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div><hr/>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteshopclients.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

Else
{
$query="SELECT * FROM magazin_cumparatori";
$result=ezpub_query($conn,$query);
$nume=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $nume;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query2= $query . " ORDER BY cumparator_nume ASC $pages->limit" ;
$result2=ezpub_query($conn,$query2);

if ($nume==0)
{
echo $strNoRecordsFound;
}
Else {
?>
<div class="paginate">
<?php
echo $strTotal . " " .$nume." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages();
?>
</div>
<table id="rounded-corner" summary="<?php echo $strOrders?>">
	      <thead>
    	<tr>
        	<th><?php echo $strID?></th>
			<th	><?php echo $strFirstName?></th>
			<th><?php echo $strLastName?></th>
			<th><?php echo $strEmail?></th>
			<th><?php echo $strPhone?></th>
			<th><?php echo $strDetails?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result2)){
    		echo"<tr>
			<td>$row[cumparator_id]</td>
			<td>$row[cumparator_prenume]</td>
			<td>$row[cumparator_nume]</td>
			<td>$row[cumparator_email]</td>
			<td>$row[cumparator_telefon]</td>
			<td><a href=\"siteshopclients.php?mode=view&bID=$row[cumparator_id]\" ><i class=\"fas fa-info\"></i></a></td>
			<td><a href=\"siteshopclients.php?mode=delete&bID=$row[cumparator_id]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
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
<hr/>
<?php
include '../bottom.php';
?>