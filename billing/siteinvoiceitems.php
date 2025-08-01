<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
 if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}


$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
?>
<!doctype html>
<head>
<!--Start Header-->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"/> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"/> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en"/> <![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--Font Awsome-->
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css">
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname ?>.css"/>

<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
 </head>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM facturare_articole_facturi WHERE articol_ID=" .$_GET['aID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=$_GET[cID]\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user

	$mSQL = "INSERT INTO facturare_articole_facturi(";
	$mSQL = $mSQL . "factura_ID,";
	$mSQL = $mSQL . "articol_descriere,";
	$mSQL = $mSQL . "articol_unitate,";
	$mSQL = $mSQL . "articol_bucati,";
	$mSQL = $mSQL . "articol_pret,";
	$mSQL = $mSQL . "articol_valoare,";
	$mSQL = $mSQL . "articol_procent_TVA,";
	$mSQL = $mSQL . "articol_total,";
	$mSQL = $mSQL . "articol_TVA)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_GET["cID"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_descriere"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_unitate"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_bucati"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_pret"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_valoare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_procent_TVA"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_total"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["articol_TVA"] . "') ";
		
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn,$mSQL));
  }
Else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=$_GET[cID]\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
Else
{// edit
$strWhereClause = " WHERE facturare_articole_facturi.articol_ID=" . $_GET["aID"] . ";";
$query= "UPDATE facturare_articole_facturi SET facturare_articole_facturi.articol_descriere='" .$_POST["articol_descriere"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_unitate='" .$_POST["articol_unitate"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_bucati='" .$_POST["articol_bucati"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_pret='" .$_POST["articol_pret"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_valoare='" .$_POST["articol_valoare"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_procent_TVA='" .$_POST["articol_procent_TVA"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_total='" .$_POST["articol_total"] . "' ," ;
$query= $query . " facturare_articole_facturi.articol_TVA='" .$_POST["articol_TVA"] . "' " ;

$query= $query . $strWhereClause;

if (!ezpub_query($conn,$query))
  {
  echo $query;
   die;
  die('Error: ' . ezpub_error($conn));
  }
 
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"siteinvoiceitems.php?cID=$_GET[cID]\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}
Else {
?>
   <div class="grid-x grid-padding-x">
		<div class="large-12 cell">
					<iframe src="sumaineuro.php" frameborder="0" style="border:0" Width="100%" height="250" scrolling="no" onload="resizeIframe(this)"></iframe>
						</div>
								</div>
<script>
function calculate(rowIndex) {
                        var myBox1 = document.getElementById('articol_bucati_' + rowIndex).value; 
                        var myBox2 = document.getElementById('articol_pret_' + rowIndex).value;
                        var articol_valoare = document.getElementById('articol_valoare_' + rowIndex);         
                        var myResult = myBox1 * myBox2;
                        articol_valoare.value = myResult;
}
      </script>
	  <script>
function calculateTVA(rowIndex) {
		var myBox3 = document.getElementById('articol_valoare_' + rowIndex).value;	
		var articol_TVA = document.getElementById('articol_TVA_' + rowIndex);	
		var myBox33 = document.getElementById('articol_procent_TVA_' + rowIndex).value;	
		var myResult1 = myBox3 * myBox33/100;
		articol_TVA.value = myResult1;
}
</script>
	  	  <script>
function calculateTotal(rowIndex) {
		var myBox4 = document.getElementById('articol_valoare_' + rowIndex).value;	
		var myBox5 = document.getElementById('articol_TVA_' + rowIndex).value;	
		var articol_total = document.getElementById('articol_total_' + rowIndex);	
		var myResult2 = +myBox4 + +myBox5;
		articol_total.value = myResult2;
}
		</script>
		
<table width="100%">
    <thead>
    	<tr>
        	<th width="30%"><?php echo $strDetails?></th>
			<th><?php echo $strUnit?></th>
			<th><?php echo $strItems?></th>
			<th><?php echo $strPrice?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVATPercent?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strTotal?></th>
			<th><?php echo $strAdd?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
		<?php
		$query="SELECT * FROM facturare_articole_facturi WHERE factura_ID=$_GET[cID]";

$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
if ($numar==0)
{
		?>
		
	<form Method="post" Action="siteinvoiceitems.php?mode=new&cID=<?php echo $_GET["cID"]?>" >
	<tr> 
	  <td><input name="articol_descriere" Type="text" id="obiect"></td>
	  <td><input name="articol_unitate" Type="text"  size="4"  value=""  /></td>
	  <td><input name="articol_bucati" id="articol_bucati_0" Type="text"  size="4"  value="" oninput="calculate(0)"/></td>
	  <td><input name="articol_pret" id="articol_pret_0" Type="text"  size="10"  value="" oninput="calculate(0)"/></td>
	  <td><input name="articol_valoare" id="articol_valoare_0" Type="text" size="10"  value=""  /></td>
	  <td><input name="articol_procent_TVA" id="articol_procent_TVA_0" Type="text" size="2"  value=""  /></td>
	  <td><input name="articol_TVA" Type="text" id="articol_TVA_0" size="10"  value="" onfocus="calculateTVA(0)" /></td>
	  <td><input name="articol_total" Type="text" id="articol_total_0" size="10"  value="" onfocus="calculateTotal(0)"/></td>
	  <td><input Type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
	  <td><p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p></td>
		</tr>
  </form>
</tbody><tfoot><tr><td></td><td  colspan="8"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
<?php
}
Else
{
		$valoareproduse=0;
		$valoareTVA=0;
		$i=0;
	While ($row=ezpub_fetch_array($result)){
$i=$i+1;
		?>
	<form Method="post" id="users" Action="siteinvoiceitems.php?mode=edit&cID=<?php echo $_GET["cID"]?>&aID=<?php echo $row["articol_ID"]?>" >
	<tr> 
	  <td><input name="articol_descriere" Type="text"id="obiect" value="<?php echo $row["articol_descriere"]?>"></td>
	  <td><input name="articol_unitate" Type="text"  size="4"  value="<?php echo $row["articol_unitate"]?>" /></td>
	  <td><input name="articol_bucati" Type="text" id="articol_bucati_<?php echo $i?>" size="4"  value="<?php echo $row["articol_bucati"]?>" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="articol_pret" Type="text" id="articol_pret_<?php echo $i?>" size="10"  value="<?php echo $row["articol_pret"]?>" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="articol_valoare" Type="text" id="articol_valoare_<?php echo $i?>" size="10"  value="<?php echo $row["articol_valoare"]?>" /></td>
	  <td><input name="articol_procent_TVA" Type="text" id="articol_procent_TVA_<?php echo $i?>" size="2"  value="<?php echo $row["articol_procent_TVA"]?>" /></td>
	  <td><input name="articol_TVA" Type="text"  id="articol_TVA_<?php echo $i?>" size="10"  value="<?php echo $row["articol_TVA"]?>" onfocus="calculateTVA(<?php echo $i?>)"/></td>
	  <td><input name="articol_total" Type="text" id="articol_total_<?php echo $i?>"  size="10"  value="<?php echo $row["articol_total"]?>" onfocus="calculateTotal(<?php echo $i?>)"/></td>
	  <td><input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button"></td>
	  <td>
	  <a href="siteinvoiceitems.php?mode=delete&aID=<?php echo $row["articol_ID"]?>&cID=<?php echo $_GET['cID']?>" class="button" OnClick="return confirm('<?php echo $strConfirmDelete?>');">
	  <i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></a></td>
		</tr>
  </form>
	<?php 
	$valoareproduse=$valoareproduse+$row["articol_valoare"];
	$valoareTVA=$valoareTVA+$row["articol_TVA"];
	} ?>
		<form Method="post" Action="siteinvoiceitems.php?mode=new&cID=<?php echo $_GET["cID"]?>" >
	<tr> 
	  <td><input name="articol_descriere" Type="text" id="obiect" value=""></td>
	  <td><input name="articol_unitate" Type="text"  size="4"  value=""  /></td>
	  <td><input name="articol_bucati" id="articol_bucati_0" Type="text"  size="4"  value="" oninput="calculate(0)"/></td>
	  <td><input name="articol_pret" id="articol_pret_0" Type="text"  size="10"  value="" oninput="calculate(0)"/></td>
	  <td><input name="articol_valoare" id="articol_valoare_0" Type="text" size="10"  value=""  /></td>
	  <td><input name="articol_procent_TVA" id="articol_procent_TVA_0" Type="text" size="2"  value=""  /></td>
	  <td><input name="articol_TVA" Type="text" id="articol_TVA_0" size="10"  value="" onfocus="calculateTVA(0)" /></td>
	  <td><input name="articol_total" Type="text" id="articol_total_0" size="10"  value="" onfocus="calculateTotal(0)"/></td>
	  <td><input Type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
	  <td><p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p>
	  </td>
		</tr>
  </form>
</tbody
><tfoot>
	<tr>
		<td></td>
		<td  colspan="8"><em></em></td>
		<td>&nbsp;</td>
	</tr>
</tfoot>
</table>  
	
	<table width="100%">
    <thead>
    	<tr>
        	<th><?php echo $strTotal?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strVAT?></th>
			<th><?php echo $strTotal?></th>
        </tr>
		</thead>
		<tr>
		<?php
		$grandtotal=$valoareTVA+$valoareproduse;
		echo "<td>$strTotal</td>";
		echo "<td>". romanize($valoareproduse)."</td>";
		echo "<td>". romanize($valoareTVA)."</td>";
		echo "<td>". romanize($grandtotal)."</td>";
		?>
		</tr>
		<tfoot><tr></tr></table>
	<?php
	
}
}
?>
</div>
</div>