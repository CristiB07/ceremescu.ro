<?php
//update 29.07.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare alimentări";
include '../dashboard/header.php';

if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

  $mindate = strtotime("-1 year", time());
  $mindate = date("Y-m-d", $mindate);
    $maxdate = strtotime("+1 year", time());
  $maxdate = date("Y-m-d", $maxdate);
  
If ((isSet($_GET['message'])) AND $_GET['message']=="Error"){
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
If ((isSet($_GET['message'])) AND $_GET['message']=="Success"){
echo "<div class=\"callout success\">$strMessageSent</div>" ;
}
?>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM administrative_alimentari WHERE alimentare_ID=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;} // ends delete

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){
//insert new user
$suma=str_replace(",",".",$_POST["alimentare_valoare"]);
$litri=str_replace(",",".",$_POST["alimentare_litri"]);

	$mSQL = "INSERT INTO administrative_alimentari(";
	$mSQL = $mSQL . "alimentare_litri,";
	$mSQL = $mSQL . "alimentare_valoare,";
	$mSQL = $mSQL . "alimentare_data,";
	$mSQL = $mSQL . "alimentare_platit,";
	$mSQL = $mSQL . "alimentare_auto,";
	$mSQL = $mSQL . "alimentare_aloc,";
	$mSQL = $mSQL . "alimentare_km,";
	$mSQL = $mSQL . "alimentare_bf)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$litri . "', ";
	$mSQL = $mSQL . "'" .$suma . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_data"]	. "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_platit"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_auto"] . "', ";
	$mSQL = $mSQL . "'" .$code . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_km"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_bf"] . "') ";
			
//It executes the SQL
	if (!ezpub_query($conn,$mSQL))
	{
  die('Error: ' . ezpub_error($conn));
	}
  Else{ //continue with post new

$suma=str_replace(",",".",$_POST["alimentare_valoare"]);
$alimentare="Alimentare auto";

	$mSQL = "INSERT INTO administrative_deconturi(";
	$mSQL = $mSQL . "decont_descriere,";
	$mSQL = $mSQL . "decont_suma,";
	$mSQL = $mSQL . "decont_luna,";
	$mSQL = $mSQL . "decont_data,";
	$mSQL = $mSQL . "decont_achitat_card,";
	$mSQL = $mSQL . "decont_user,";
	$mSQL = $mSQL . "decont_document)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$alimentare . "', ";
	$mSQL = $mSQL . "'" .$suma . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_data"]	. "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_data"]	. "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_platit"] . "', ";
	$mSQL = $mSQL . "'" .$code . "', ";
	$mSQL = $mSQL . "'" .$_POST["alimentare_bf"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
	  }
	  Else {
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}}
Else
{// edit
$suma=str_replace(",",".",$_POST["alimentare_valoare"]);

$strWhereClause = " WHERE administrative_alimentari.alimentare_ID=" . $_GET["cID"] . ";";
$query= "UPDATE administrative_alimentari SET administrative_alimentari.alimentare_bf='" .$_POST["alimentare_bf"] . "' ," ;
$query= $query . " administrative_alimentari.alimentare_valoare='" .$suma . "' ," ;
$query= $query . " administrative_alimentari.alimentare_litri='" .$_POST["alimentare_litri"] .   "' ," ;
$query= $query . " administrative_alimentari.alimentare_km='" .$_POST["alimentare_km"] .   "' ," ;
$query= $query . " administrative_alimentari.alimentare_data='" .$_POST["alimentare_data"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"gasfilling.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
} // ends if post
Else {
?>
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="gasfilling.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" Action="gasfilling.php?mode=new" >
		    		  <div class="grid-x grid-margin-x">
     <div class="large-2 medium-2 small-2 cell"> 
	  <label><?php echo $strCarPlate?></label>
	  <?php
	  $query="SELECT utilizator_Carplate from date_utilizatori WHERE utilizator_ID=$uid";
	  $result=ezpub_query($conn,$query);
	$row=ezpub_fetch_array($result);
	  ?>
 <input name="alimentare_auto" id="alimentare_auto" Type="text" value=<?php echo $row["utilizator_Carplate"]?>  />
	</div>
				     <div class="large-3 medium-3 small-3 cell">    
      <label><?php echo $strPaidWithCard?></label>
      <input name="alimentare_platit" Type="radio" value="0" checked /> <?php echo $strCompanyCard?>&nbsp;&nbsp;
	  <input name="alimentare_platit" Type="radio" value="3"><?php echo $strLeasingCard?>
    </div> 
     <div class="large-1 medium-1 small-1 cell"> 	  
	  <label><?php echo $strSum?></label>
	  <input name="alimentare_valoare" Type="text"  value="" />
	</div>
	  <div class="large-2 medium-2 small-2 cell"> 
      <label><?php echo $strDate?></label>
    <input name="alimentare_data" Type="date"  value="<?php echo date("Y-m-d")?>" min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
    </div> 

     <div class="large-1 medium-1 small-1 cell"> 
	  <label><?php echo $strLiters?></label>
 <input name="alimentare_litri" id="alimentare_litri" Type="text"  />
	</div>
	
				     <div class="large-1 medium-1 small-1 cell">    
      <label><?php echo $strKilometers?></label>
 <input name="alimentare_km" id="alimentare_km" Type="text"  />
    </div> 
		   <div class="large-2 medium-2 small-2 cell">  
	  <label><?php echo $strDocument?></label>
  <input name="alimentare_bf" Type="text"  value="" />
    </div> 
	</div>
			    <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <p align="center"><input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM administrative_alimentari WHERE alimentare_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="gasfilling.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="gasfilling.php?mode=edit&cID=<?php echo $_GET['cID']?>" >
			    		  <div class="grid-x grid-margin-x">
        <div class="large-2 medium-2 small-2 cell"> 
	  <label><?php echo $strCarPlate?></label>
 <input name="alimentare_auto" Type="text" value="<?php echo $row["alimentare_auto"]?>" />
	</div>
	<div class="large-3 medium-3 small-3 cell"> 
	      <label><?php echo $strPaidWithCard?></label>
      <input name="alimentare_platit" Type="radio" value="0" <?php If ($row["alimentare_platit"]==0) echo "checked"?> />&nbsp;<?php echo $strYes?> 
	  <input name="alimentare_platit" Type="radio" value="1" <?php If ($row["alimentare_platit"]==1) echo "checked"?>>&nbsp;<?php echo $strNo?>
	  </div>
		       <div class="large-1 medium-1 small-1 cell">  
	  <label><?php echo $strSum?><label>
	  <input name="alimentare_valoare" Type="text"  value="<?php echo $row["alimentare_valoare"]?>" />
	</div>
	<div class="large-2 medium-2 small-2 cell"> 
			  <label><?php echo $strDate?></label>
         <input name="alimentare_data" Type="date"  value="<?php echo $row["alimentare_data"]?>" min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
	</div>
	        <div class="large-1 medium-1 small-1 cell"> 
	  <label><?php echo $strKilometers?></label>
 <input name="alimentare_km" Type="text" value="<?php echo $row["alimentare_km"]?>" />
	</div>						  
	   <div class="large-1 medium-1 small-1 cell">  
	  <label><?php echo $strLiters?></label>
  <input name="alimentare_litri" Type="text"  value="<?php echo $row["alimentare_litri"]?>" />
    </div> 	   
	<div class="large-2 medium-2 small-2 cell">  
	  <label><?php echo $strDocument?></label>
  <input name="alimentare_bf" Type="text"  value="<?php echo $row["alimentare_bf"]?>" />
    </div> 
    </div> 
		  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"><p align="center"> <input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
Else 
{
echo "<a href=\"gasfilling.php?mode=new\" class=\"button\">$strAddNew <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
$query="SELECT * from administrative_alimentari where alimentare_aloc='$code' Order By alimentare_data DESC";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$result=ezpub_query($conn,$query);
echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
Else {
?>
<h3><?php echo $strGasFillings?></h3>
	 
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strGasFillings ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"gasfilling.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
</div>

<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strExpense?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strKilometers?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$strGasFilling</td>
<td>".romanize($row['alimentare_valoare'])."</td>";
echo "<td>".date('d.m.Y', strtotime($row['alimentare_data']))."</td>";
echo "<td>$row[alimentare_km]</td>";

		echo	  "<td><a href=\"gasfilling.php?mode=edit&cID=$row[alimentare_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"gasfilling.php?mode=delete&cID=$row[alimentare_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"4\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
</div>
</div>
<hr/>
<?php
include '../bottom.php';
?>