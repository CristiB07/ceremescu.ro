<?php
//update 29.07.2025
include '../settings.php';

include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare deconturi";
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

$nsql="DELETE FROM administrative_deconturi WHERE decont_ID=" .$_GET['cID']. ";";
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
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If ($_GET['mode']=="new"){
//insert new user

$suma=str_replace(",",".",$_POST["decont_suma"]);

	$mSQL = "INSERT INTO administrative_deconturi(";
	$mSQL = $mSQL . "decont_descriere,";
	$mSQL = $mSQL . "decont_suma,";
	$mSQL = $mSQL . "decont_luna,";
	$mSQL = $mSQL . "decont_data,";
	$mSQL = $mSQL . "decont_achitat_card,";
	$mSQL = $mSQL . "decont_user,";
	$mSQL = $mSQL . "decont_document)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$_POST["decont_descriere"] . "', ";
	$mSQL = $mSQL . "'" .$suma . "', ";
	$mSQL = $mSQL . "'" .$_POST["decont_data"]	. "', ";
	$mSQL = $mSQL . "'" .$_POST["decont_data"]	. "', ";
	$mSQL = $mSQL . "'" .$_POST["decont_achitat_card"] . "', ";
	$mSQL = $mSQL . "'" .$code . "', ";
	$mSQL = $mSQL . "'" .$_POST["decont_document"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>" ;
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
}}
Else
{// edit


$suma=str_replace(",",".",$_POST["decont_suma"]);

$strWhereClause = " WHERE administrative_deconturi.decont_ID=" . $_GET["cID"] . ";";
$query= "UPDATE administrative_deconturi SET administrative_deconturi.decont_descriere='" .$_POST["decont_descriere"] . "' ," ;
$query= $query . " administrative_deconturi.decont_suma='" .$suma . "' ," ;
$query= $query . " administrative_deconturi.decont_document='" .$_POST["decont_document"] .   "' ," ;
$query= $query . " administrative_deconturi.decont_luna='" .$_POST["decont_data"] .   "' ," ;
$query= $query . " administrative_deconturi.decont_data='" .$_POST["decont_data"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div><hr/>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"personalexpenses.php\"
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
<?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="personalexpenses.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" Action="personalexpenses.php?mode=new" >

<div class="grid-x grid-margin-x">
    <div class="large-2 medium-2 small-2 cell">
<label><?php echo $strMonth?></label>	 
		<select name="strDData2">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {
    		
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select> 
				</div>
     <div class="large-1 medium-1 small-1 cell"> 
<label><?php echo $strYear?></label>		 
		<select name="strDData3">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
    </div> 
     <div class="large-2 medium-2 small-2 cell"> 
	  <label><?php echo $strExpense?></label>
 <input name="decont_descriere" id="decont_descriere" Type="text"  />
	</div>
				     <div class="large-2 medium-2 small-2 cell">    
      <label><?php echo $strPaidCard?></label>
      <input name="decont_achitat_card" Type="radio" value="0" checked /> <?php echo $strYes?>&nbsp;&nbsp;
	  <input name="decont_achitat_card" Type="radio" value="1"><?php echo $strNo?>
    </div> 
     <div class="large-1 medium-1 small-1 cell"> 	  
	  <label><?php echo $strSum?></label>
	  <input name="decont_suma" Type="text"  value="" />
	</div>
	   <div class="large-2 medium-2 small-2 cell">  
	  <label><?php echo $strDocument?></label>
  <input name="decont_document" Type="text"  value="" />
    </div> 
    		  <div class="large-2 medium-2 small-2 cell"> 
      <label><?php echo $strDate?></label>
    <input name="decont_data" Type="date"  value="<?php echo date("Y-m-d")?>" min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
    </div> 
    </div> 
		    <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <p align="center"><input Type="submit" Value="<?php echo $strAdd?>" name="Submit" class="button"></p></div>
	</div>
  </form>
<?php
}
ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM administrative_deconturi WHERE decont_ID=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
			    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="personalexpenses.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<form Method="post" id="users" Action="personalexpenses.php?mode=edit&cID=<?php echo $_GET['cID']?>" >
			    		  <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-4 cell"> 
	  <label><?php echo $strExpense?></label>
 <input name="decont_descriere" Type="text" value="<?php echo $row["decont_descriere"]?>" />
	</div>
	<div class="large-2 medium-2 small-2 cell"> 
	      <label><?php echo $strPaidCard?></label>
      <input name="decont_achitat_card" Type="radio" value="0" <?php If ($row["decont_achitat_card"]==0) echo "checked"?> />&nbsp;<?php echo $strYes?> 
	  <input name="decont_achitat_card" Type="radio" value="1" <?php If ($row["decont_achitat_card"]==1) echo "checked"?> />&nbsp;<?php echo $strNo?>
	  </div>
		       <div class="large-2 medium-2 small-2 cell">  
	  <label><?php echo $strSum?><label>
	  <input name="decont_suma" Type="text"  value="<?php echo $row["decont_suma"]?>" />
	</div>
	   <div class="large-2 medium-2 small-2 cell">  
	  <label><?php echo $strDocument?></label>
  <input name="decont_document" Type="text"  value="<?php echo $row["decont_document"]?>" />
    </div> 
   	   <div class="large-2 medium-2 small-2 cell">  
		  <label><?php echo $strDate?></label>
         <input name="decont_data" Type="date"  value="<?php echo $row["decont_data"]?>" min="<?php echo $mindate?>" max="<?php echo $maxdate?>" />
   </div>
   </div>
   	
	  		  <div class="grid-x grid-margin-x">
     <div class="large-12 medium-12 small-12 cell"> <p align="center"><input Type="submit" Value="<?php echo $strModify?>" name="Submit" class="button"> </p></div>
	</div>
  </form>
<?php
}
Else 
{?>
<h3><?php echo $strSendPE?></h3>
				  <form Method="post" id="users" Action="pe2excel.php">
				  <div class="grid-x grid-margin-x">
				  			      <div class="large-2 medium-2 small-2 cell">
<label><?php echo $strMonth?></label>	 
		<select name="month">
	<option value="00" selected>--</option>
         <?php for ( $m = 1; $m <= 12; $m ++) {
    		
     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
    			echo "<OPTION value=\"$m\">$monthname</OPTION>";} 
			?>
        </select> 
				</div>
     <div class="large-2 medium-2 small-2 cell"> 
<label><?php echo $strYear?></label>			 
		<select name="year">
		<option value="0000" selected>--</option>
		<?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<OPTION value=\"$y\">$y</OPTION>";} 
			?>
        </select>
</div>
     <div class="large-2 medium-2 small-2 cell "><p align="right"> <label>&nbsp;</label><input Type="submit" Value="<?php echo $strSend?>" name="Submit" class="button"> </p> 	</div>
	</div>
			  </form>
			
<?php

echo "<a href=\"personalexpenses.php?mode=new\" class=\"button\">$strAddNew <i class=\"large fa fa-plus\" title=\"$strAdd\"></i></a><br />";
$query="SELECT * from administrative_deconturi where decont_user='$code' Order By decont_data DESC";
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
<h3><?php echo $strExpenses?></h3>
	 
<div class="paginate">
<?php
echo $strTotal . " " .$numar." ".$strPayments ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"personalexpenses.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
?>
</div>

<table width="100%">
	      <thead>
    	<tr>
        	<th><?php echo $strExpense?></th>
			<th><?php echo $strValue?></th>
			<th><?php echo $strDate?></th>
			<th><?php echo $strEdit?></th>
			<th><?php echo $strDelete?></th>
        </tr>
		</thead>
<tbody>
<?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[decont_descriere]</td>
<td>".romanize($row['decont_suma'])."</td>";
$m=date("m", strtotime($row['decont_luna']));
			$dateObj   = DateTime::createFromFormat('!m', $m);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
$year=date("Y", strtotime($row['decont_luna']));	
$lunadecont=$monthname.".".$year;

		echo	  "<td>$lunadecont</td><td><a href=\"personalexpenses.php?mode=edit&cID=$row[decont_ID]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			<td><a href=\"personalexpenses.php?mode=delete&cID=$row[decont_ID]\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"3\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
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