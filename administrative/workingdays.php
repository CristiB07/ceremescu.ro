<?php
//update 29.07.2025
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare pontaje";
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
$month=$_GET["month"];
$year=$_GET["year"];

If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM administrative_pontaje WHERE pontaj_ID=" .$_GET['cID']. ";";
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

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
//insert new data

	$mSQL = "INSERT INTO administrative_pontaje(";
	$mSQL = $mSQL . "pontaj_user,";
	$mSQL = $mSQL . "pontaj_zi,";
	$mSQL = $mSQL . "pontaj_luna,";
	$mSQL = $mSQL . "pontaj_an,";
	$mSQL = $mSQL . "pontaj_CO,";
	$mSQL = $mSQL . "pontaj_ore_WFH,";
	$mSQL = $mSQL . "pontaj_ore_T,";
	$mSQL = $mSQL . "pontaj_ore_B,";
	$mSQL = $mSQL . "pontaj_ore_A,";
	$mSQL = $mSQL . "pontaj_observatii)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$code . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_zi"] . "', ";
	$mSQL = $mSQL . "'" .$month . "', ";
	$mSQL = $mSQL . "'" .$year . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_CO"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_ore_WFH"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_ore_T"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_ore_B"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_ore_A"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["pontaj_observatii"] . "') ";
			
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
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}}
ElseIf	(IsSet($_GET['mode']) AND $_GET['mode']=="edit")
{// edit
$strWhereClause = " WHERE administrative_pontaje.pontaj_ID=" . $_GET["cID"] . ";";
$query= "UPDATE administrative_pontaje SET administrative_pontaje.pontaj_observatii='" .$_POST["pontaj_observatii"] . "' ," ;
$query= $query . " administrative_pontaje.pontaj_CO='" .$_POST["pontaj_CO"] . "' ," ;
$query= $query . " administrative_pontaje.pontaj_ore_WFH='" .$_POST["pontaj_ore_WFH"] .   "' ," ;
$query= $query . " administrative_pontaje.pontaj_ore_B='" .$_POST["pontaj_ore_B"] .   "' ," ;
$query= $query . " administrative_pontaje.pontaj_ore_A='" .$_POST["pontaj_ore_A"] .   "' ," ;
$query= $query . " administrative_pontaje.pontaj_ore_T='" .$_POST["pontaj_ore_T"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
Else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
        window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 500)\">";
include '../bottom.php';
die;
}
}
}

Else
{
	If (IsSet($_GET['mode']) AND $_GET['mode']=="fill")
{	
$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$dd=0;
for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
	 $dd=$dd;
 }
 Else
 {
	 		$dd=$dd+1;
 }
 }

$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);

echo $strNumberOfDaysInMonth." ".$monthname." " .$strInYear." ".$year." ".$strWas." ".$d. "<br />";
echo $strNumberOfWorkingDaysInMonth." ".$monthname." " .$strInYear." ".$year." ".$strWas." ".$dd. "<br />";
?>
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="personalworkingdays.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<table width="100%" class="fixed-header">
    <thead>
    	<tr>
        	<th width="5%"><?php echo $strDay?></th>
        	<th width="5%"><?php echo $strMonth?></th>
        	<th width="10%"><?php echo $strYear?></th>
			<th width="5%"><?php echo $strPersonalTime?></th>
			<th width="5%"><?php echo $strWorkFromHome?></th>
			<th width="5%"><?php echo $strFieldWork?></th>
			<th width="5%"><?php echo $strOffice?></th>
			<th width="5%"><?php echo $strOther?></th>
			<th width="45%"><?php echo $strDetails?></th>
			<th width="5%"><?php echo $strAdd?></th>
			<th width="5%"><?php echo $strDelete?></th>
        </tr>
		</thead>
<?php
 for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
	 echo "<tr><td colspan=\"10\">" . $dayname. ", este zi nelucrătoare.<td></tr>";
 }
 Else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
		$query="SELECT * FROM administrative_pontaje WHERE pontaj_zi='$i' AND pontaj_luna='$month' AND pontaj_an='$year' AND pontaj_user='$code'";
		$result=ezpub_query($conn,$query);
		$numar=ezpub_num_rows($result,$query);
		$row=ezpub_fetch_array($result);
		echo ezpub_error($conn);

		if ($numar==0)
{
?>

	<tr>
	<form Method="post" Action="workingdays.php?mode=new&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?>" >	
	  <td><input name="pontaj_zi" Type="text" value="<?php echo $i?>" readonly></td>
	  <td><input name="month" Type="text" value="<?php echo $month?>" readonly></td>
	  <td><input name="year" Type="text" value="<?php echo $year?>" readonly></td>
	  <td><input name="pontaj_CO" Type="text"  size="4"  value=""  /></td>
	  <td><input name="pontaj_ore_WFH" Type="text"  size="4"  value=""  /></td>
	  <td><input name="pontaj_ore_T" Type="text"  size="4"  value=""/></td>
	  <td><input name="pontaj_ore_B" Type="text"  size="4"  value=""/></td>
	  <td><input name="pontaj_ore_A" Type="text"  size="4"  value=""/></td>
	  <td><input name="pontaj_observatii" Type="text"  size="4"  value=""/></td>
	  <td><input Type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
	  <td><p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p></td>
	    </form>
		</tr>
<?php
 }
 Else
 {?>
	 	
	<tr> 
	<form Method="post" Action="workingdays.php?mode=edit&cID=<?php echo $row["pontaj_ID"]?>&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?>" >
	  	  <td><input name="pontaj_zi" Type="text" value="<?php echo $i?>" readonly></td>
	  <td><input name="month" Type="text" value="<?php echo $month?>" readonly></td>
	  <td><input name="year" Type="text" value="<?php echo $year?>" readonly></td>
	  <td><input name="pontaj_CO" Type="text"  size="4"  value="<?php echo $row["pontaj_CO"]?>"  /></td>
	  <td><input name="pontaj_ore_WFH" Type="text"  size="4"  value="<?php echo $row["pontaj_ore_WFH"]?>"/></td>
	  <td><input name="pontaj_ore_T" Type="text"  size="4"  value="<?php echo $row["pontaj_ore_T"]?>"/></td>
	  <td><input name="pontaj_ore_B" Type="text"  size="4"  value="<?php echo $row["pontaj_ore_B"]?>"/></td>
	  <td><input name="pontaj_ore_A" Type="text"  size="4"  value="<?php echo $row["pontaj_ore_A"]?>"/></td>
	  <td><input name="pontaj_observatii" Type="text"  size="4"  value="<?php echo $row["pontaj_observatii"]?>"/></td>
	  <td><input Type="submit" Value="<?php echo $strModify?>" class="button" name="Submit"></td>
	  <td>
	  <a href="workingdays.php?mode=delete&cID=<?php echo $row["pontaj_ID"]?>$month=<?php echo $month?>&year=<?php echo $year?>" class="ask button" OnClick="return confirm('<?php echo $strConfirmDelete?>');">
	  <i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></a></td>
	    </form>
		</tr>

 <?php }
 }
 }
 ?>
 </tbody><tfoot><tr><td></td><td  colspan="9"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
 </div>
 </div>
 <?php
 }
 ElseIf (IsSet($_GET['mode']) AND $_GET['mode']=="show")
 {
	$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$dd=0;
for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
	 $dd=$dd;
 }
 Else
 {
	 		$dd=$dd+1;
 }
 }


$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);

echo $strNumberOfDaysInMonth." ".$monthname." " .$strInYear." ".$year." ".$strWas." ".$d. "<br />";
echo $strNumberOfWorkingDaysInMonth." ".$monthname." " .$strInYear." ".$year." ".$strWas." ".$dd. "<br />";
?>
<table width="100%">
    <thead>
    	<tr>
        	<th><?php echo $strDay?></th>
			<th><?php echo $strPersonalTime?></th>
			<th><?php echo $strWorkFromHome?></th>
			<th><?php echo $strFieldWork?></th>
			<th><?php echo $strOffice?></th>
			<th><?php echo $strOther?></th>
			<th><?php echo $strDetails?></th>
        </tr>
		</thead>
<?php
 for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 IF (in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
	 echo "<tr><td colspan=\"6\"><strong>" . $dayname. ", este zi nelucrătoare.</strong><td></tr>";
 }
 Else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
		$query="SELECT * FROM administrative_pontaje WHERE pontaj_zi='$i' AND pontaj_luna='$month' AND pontaj_an='$year' AND pontaj_user='$code'";
		$result=ezpub_query($conn,$query);
		$numar=ezpub_num_rows($result,$query);
		$row=ezpub_fetch_array($result);
		echo ezpub_error($conn);
		if (isset ($row)){;?>
	<tr> 
	  <td><?php echo $i?></td>
	  <td><?php echo $row["pontaj_CO"]?></td>
	  <td><?php echo $row["pontaj_ore_WFH"]?></td>
	  <td><?php echo $row["pontaj_ore_T"]?></td>
	  <td><?php echo $row["pontaj_ore_B"]?></td>
	  <td><?php echo $row["pontaj_ore_A"]?></td>
	  <td><?php echo $row["pontaj_observatii"]?></td>
		</tr>
 <?php 
		}
		Else 
		{?>
				<tr> 
	  <td><?php echo $i?></td>
	  <td><?php echo $strNotFilled?></td>
	  <td><?php echo $strNotFilled?></td>
	  <td><?php echo $strNotFilled?></td>
	  <td><?php echo $strNotFilled?></td>
	  <td><?php echo $strNotFilled?></td>
	  <td><?php echo $strNotFilled?></td>
		</tr>
		<?php }
 }
 }
 }
 ?>
 </tbody><tfoot><tr><td></td><td  colspan="5"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
 <?php
 }
?>
</div>
</div>
<?php
include '../bottom.php';
?>