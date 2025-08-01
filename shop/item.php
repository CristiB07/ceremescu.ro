<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';
?>
<?php
If (isSet($_GET['id']) AND is_numeric($_GET['id'])) {
$id=$_GET['id'];}
Else{
include '../header.php';
echo '	<div class=\"grid-x grid-padding-x\" >
	  <div class="large-12 medium-12 small-12 cell">';
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; 
include ('../bottom.php');
die;}
If (isSet($_GET['action']) AND $_GET['action']!="") {
$action=$_GET['action'];}
Else{
include '../header.php';
echo '	<div class=\"grid-x grid-padding-x\" >
	  <div class="large-12 medium-12 small-12 cell">';
echo "<div class=\"callout alert\">$strThereWasAnError</div></div></div>"; 
include ('../bottom.php');
die;}

if ($action=="delete")
{
$query="DELETE from magazin_articole WHERE articol_id=$id";
ezpub_query($conn,$query);
header("location:order.php");
}
elseif ($action=="add")
{
$whereclause=" WHERE articol_id=$id";
$query="SELECT articol_cantitate FROM magazin_articole" . $whereclause;
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$newquantity=$row['articol_cantitate']+1;
$updatequery="UPDATE magazin_articole set articol_cantitate=$newquantity " . $whereclause ;
ezpub_query($conn,$updatequery);
//echo $updatequery;
header("location:order.php");
}
elseif ($action=="decrease")
{
$whereclause=" WHERE articol_id=$id";
$query="SELECT articol_cantitate FROM magazin_articole" . $whereclause;
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
$newquantity=$row['articol_cantitate']-1;
$updatequery="UPDATE magazin_articole set articol_cantitate=$newquantity " . $whereclause ;
ezpub_query($conn,$updatequery);
//echo $updatequery;
header("location:order.php");
}
?>