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
		$query="SELECT utilizator_Carplate FROM date_utilizatori WHERE utilizator_Code='$code'";
		$result=ezpub_query($conn,$query);
		$row=ezpub_fetch_array($result);
		$carplate=$row["utilizator_Carplate"];


If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM administrative_foi_parcurs WHERE fp_ID=" .$_GET['cID']. ";";
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

	$mSQL = "INSERT INTO administrative_foi_parcurs(";
	$mSQL = $mSQL . "fp_aloc,";
	$mSQL = $mSQL . "fp_zi,";
	$mSQL = $mSQL . "fp_luna,";
	$mSQL = $mSQL . "fp_an,";
	$mSQL = $mSQL . "fp_numar,";
	$mSQL = $mSQL . "fp_plecare,";
	$mSQL = $mSQL . "fp_sosire,";
	$mSQL = $mSQL . "fp_km,";
	$mSQL = $mSQL . "fp_km_init,";
	$mSQL = $mSQL . "fp_km_final,";
	$mSQL = $mSQL . "fp_detalii)";

	$mSQL = $mSQL . "Values(";
	$mSQL = $mSQL . "'" .$code . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_zi"] . "', ";
	$mSQL = $mSQL . "'" .$month . "', ";
	$mSQL = $mSQL . "'" .$year . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_numar"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_plecare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_sosire"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_km"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_km_init"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_km_final"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["fp_detalii"] . "') ";
			
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
Else{
	
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
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
$strWhereClause = " WHERE administrative_foi_parcurs.fp_ID=" . $_GET["cID"] . ";";
$query= "UPDATE administrative_foi_parcurs SET administrative_foi_parcurs.fp_detalii='" .$_POST["fp_detalii"] . "' ," ;
$query= $query . " administrative_foi_parcurs.fp_plecare='" .$_POST["fp_plecare"] . "' ," ;
$query= $query . " administrative_foi_parcurs.fp_sosire='" .$_POST["fp_sosire"] .   "' ," ;
$query= $query . " administrative_foi_parcurs.fp_km='" .$_POST["fp_km"] .   "' ," ;
$query= $query . " administrative_foi_parcurs.fp_km_final='" .$_POST["fp_km_final"] .   "' ," ;
$query= $query . " administrative_foi_parcurs.fp_km_init='" .$_POST["fp_km_init"] . "' "; 
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
			  <p><a href="personalcarsheets.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<script>
function calculate(rowIndex) {
                        var myBox1 = document.getElementById('fp_km_init_' + rowIndex).value; 
                        var myBox2 = document.getElementById('fp_km_' + rowIndex).value;
                        var fp_km_final = document.getElementById('fp_km_final_' + rowIndex);  					
                        var myResult = parseInt(myBox1) + parseInt(myBox2);
                        fp_km_final.value = myResult;
}
      </script>
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<table width="100%">
    <thead>
    	<tr>
        	<th width="5%"><?php echo $strDay?></th>
        	<th width="5%"><?php echo $strMonth?></th>
        	<th width="10%"><?php echo $strYear?></th>
        	<th width="10%"><?php echo $strCarPlate?></th>
			<th width="15%"><?php echo $strStarting?></th>
			<th width="15%"><?php echo $strArriving?></th>
			<th width="5%"><?php echo $strInitialKm?></th>
			<th width="5%"><?php echo $strWorkingKm?></th>
			<th width="5%"><?php echo $strEndKm?></th>
			<th width="35%"><?php echo $strDetails?></th>
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
	 echo "<tr><td colspan=\"11\">" . $dayname. ", este zi nelucrătoare.<td></tr>";
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
		$query="SELECT * FROM administrative_foi_parcurs WHERE fp_zi='$i' AND fp_luna='$month' AND fp_an='$year' AND fp_aloc='$code'";
		$result=ezpub_query($conn,$query);
		$numar=ezpub_num_rows($result,$query);
		$row=ezpub_fetch_array($result);
		echo ezpub_error($conn);
		if ($numar==0)
{
?>

	<tr>
	<form Method="post" Action="carsheets.php?mode=new&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?>" >	
	  <td><input name="fp_zi" Type="text" value="<?php echo $i?>" readonly></td>
	  <td><input name="month" Type="text" value="<?php echo $month?>" readonly></td>
	  <td><input name="year" Type="text" value="<?php echo $year?>" readonly></td>
	  <td><input name="fp_numar" Type="text"  size="4"  value="<?php echo $carplate?>" /></td>
	  <td><input name="fp_plecare" Type="text"  size="4"  value=""  /></td>
	  <td><input name="fp_sosire" Type="text"  size="4"  value=""  /></td>
	  <td><input name="fp_km_init" id="fp_km_init_<?php echo $i?>" Type="text"  size="4"  value="" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="fp_km" Type="text"  id="fp_km_<?php echo $i?>" size="4"  value="" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="fp_km_final" id="fp_km_final_<?php echo $i?>"" Type="text"  size="4"  value=""/></td>
	  <td><input name="fp_detalii" Type="text"  size="4"  value=""/></td>
	  <td><input Type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
	  <td><p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p></td>
	    </form>
		</tr>
<?php
 }
 Else
 {?>
	 	
	<tr> 
	<form Method="post" Action="carsheets.php?mode=edit&cID=<?php echo $row["fp_ID"]?>&month=<?php echo $_GET["month"]?>&year=<?php echo $_GET["year"]?>" >
	  	  <td><input name="fp_zi" Type="text" value="<?php echo $i?>" readonly></td>
	  <td><input name="month" Type="text" value="<?php echo $month?>" readonly></td>
	  <td><input name="year" Type="text" value="<?php echo $year?>" readonly></td>
	  <td><input name="fp_numar" Type="text"  size="4"  value="<?php echo $row["fp_numar"]?>"  /></td>
	  <td><input name="fp_plecare" Type="text"  size="4"  value="<?php echo $row["fp_plecare"]?>"  /></td>
	  <td><input name="fp_sosire" Type="text"  size="4"  value="<?php echo $row["fp_sosire"]?>"/></td>
	  <td><input name="fp_km_init" Type="text" id="fp_km_init_<?php echo $i?>" size="4"  value="<?php echo $row["fp_km_init"]?>" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="fp_km" Type="text"  id="fp_km_<?php echo $i?>"  size="4"  value="<?php echo $row["fp_km"]?>" oninput="calculate(<?php echo $i?>)"/></td>
	  <td><input name="fp_km_final" id="fp_km_final" Type="text"  size="4"  value="<?php echo $row["fp_km_final"]?>"/></td>
	  <td><input name="fp_detalii" Type="text"  size="4"  value="<?php echo $row["fp_detalii"]?>"/></td>
	  <td><input Type="submit" Value="<?php echo $strModify?>" class="button" name="Submit"></td>
	  <td>
	  <a href="carsheets.php?mode=delete&cID=<?php echo $row["fp_ID"]?>$month=<?php echo $month?>&year=<?php echo $year?>" class="ask button" OnClick="return confirm('<?php echo $strConfirmDelete?>');">
	  <i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></a></td>
	    </form>
		</tr>

 <?php }
 }
 }
 ?>
 </tbody><tfoot><tr><td></td><td colspan="10"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
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
<div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
			  <p><a href="personalcarsheets.php" class="button"><?php echo $strBack?></a></p>
</div>
</div>
<table width="100%">
    <thead>
    	<tr>
        	<th><?php echo $strDay?></th>
			<th width="5%"><?php echo $strStarting?></th>
			<th width="5%"><?php echo $strArriving?></th>
			<th width="5%"><?php echo $strInitialKm?></th>
			<th width="5%"><?php echo $strEndKm?></th>
			<th width="45%"><?php echo $strDetails?></th>
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
	 echo "<tr><td colspan=\"5\"><strong>" . $dayname. ", este zi nelucrătoare.</strong><td></tr>";
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
		$query="SELECT * FROM administrative_foi_parcurs WHERE fp_zi='$i' AND fp_luna='$month' AND fp_an='$year' AND fp_aloc='$code'";
		$result=ezpub_query($conn,$query);
		$numar=ezpub_num_rows($result,$query);
		$row=ezpub_fetch_array($result);
		echo ezpub_error($conn);
	
		if (isset ($row)){;?>
	<tr> 
	  <td><?php echo $i?></td>
	  <td><?php echo $row["fp_plecare"]?></td>
	  <td><?php echo $row["fp_sosire"]?></td>
	  <td><?php echo $row["fp_km_init"]?></td>
	  <td><?php echo $row["fp_km_final"]?></td>
	  <td><?php echo $row["fp_detalii"]?></td>
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
		</tr>
		<?php }
 }
 }
 }
 ?>
 </tbody><tfoot><tr><td></td><td  colspan="4"><em></em></td><td>&nbsp;</td></tr></tfoot></table>  
 <?php
 }
?>
</div>
</div>
<?php
include '../bottom.php';
?>