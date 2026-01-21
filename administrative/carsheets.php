<?php
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
	header("location:$strSiteURL/login/index.php?message=MLF");
}

$uid=$_SESSION['uid'];
$code=$_SESSION['code'];

// Validate month and year parameters
if (!isset($_GET['month']) || !is_numeric($_GET['month']) || $_GET['month'] < 1 || $_GET['month'] > 12) {
    die('<div class="callout alert">Invalid month parameter</div>');
}
if (!isset($_GET['year']) || !is_numeric($_GET['year']) || $_GET['year'] < 2000 || $_GET['year'] > 2100) {
    die('<div class="callout alert">Invalid year parameter</div>');
}

$month = intval($_GET["month"]);
$year = intval($_GET["year"]);

// Use prepared statement
$stmt = $conn->prepare("SELECT utilizator_Carplate FROM date_utilizatori WHERE utilizator_Code=?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$carplate = $row["utilizator_Carplate"];


If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid record ID</div>');
}

$cID = intval($_GET['cID']);

// Authorization check: verify record belongs to current user
$stmt = $conn->prepare("SELECT fp_aloc FROM administrative_foi_parcurs WHERE fp_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record || $record['fp_aloc'] !== $code) {
    die('<div class="callout alert">Unauthorized access</div>');
}

// Delete record using prepared statement
$stmt = $conn->prepare("DELETE FROM administrative_foi_parcurs WHERE fp_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$stmt->close();

echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST'){

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
//insert new data

// Validate required fields
if (!isset($_POST['fp_zi'], $_POST['fp_numar'], $_POST['fp_plecare'], $_POST['fp_sosire'], 
    $_POST['fp_km'], $_POST['fp_km_init'], $_POST['fp_km_final'], $_POST['fp_detalii'])) {
    die('<div class="callout alert">All fields are required</div>');
}

// Sanitize and validate inputs
$fp_zi = intval($_POST["fp_zi"]);
$fp_numar = trim($_POST["fp_numar"]);
$fp_plecare = trim($_POST["fp_plecare"]);
$fp_sosire = trim($_POST["fp_sosire"]);
$fp_km = floatval($_POST["fp_km"]);
$fp_km_init = floatval($_POST["fp_km_init"]);
$fp_km_final = floatval($_POST["fp_km_final"]);
$fp_detalii = trim($_POST["fp_detalii"]);

// Use prepared statement
$stmt = $conn->prepare("INSERT INTO administrative_foi_parcurs(fp_aloc, fp_zi, fp_luna, fp_an, fp_numar, fp_plecare, fp_sosire, fp_km, fp_km_init, fp_km_final, fp_detalii) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siissssddds", $code, $fp_zi, $month, $year, $fp_numar, $fp_plecare, $fp_sosire, $fp_km, $fp_km_init, $fp_km_final, $fp_detalii);
			
//It executes the SQL
if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
else{
	$stmt->close();
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}}
elseIf	(IsSet($_GET['mode']) AND $_GET['mode']=="edit")
{// edit
// Validate cID parameter
if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
    die('<div class="callout alert">Invalid record ID</div>');
}

$cID = intval($_GET['cID']);

// Authorization check: verify record belongs to current user
$stmt = $conn->prepare("SELECT fp_aloc FROM administrative_foi_parcurs WHERE fp_ID=?");
$stmt->bind_param("i", $cID);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record || $record['fp_aloc'] !== $code) {
    die('<div class="callout alert">Unauthorized access</div>');
}

// Validate required fields
if (!isset($_POST['fp_detalii'], $_POST['fp_plecare'], $_POST['fp_sosire'], 
    $_POST['fp_km'], $_POST['fp_km_init'], $_POST['fp_km_final'])) {
    die('<div class="callout alert">All fields are required</div>');
}

// Sanitize inputs
$fp_detalii = trim($_POST["fp_detalii"]);
$fp_plecare = trim($_POST["fp_plecare"]);
$fp_sosire = trim($_POST["fp_sosire"]);
$fp_km = floatval($_POST["fp_km"]);
$fp_km_final = floatval($_POST["fp_km_final"]);
$fp_km_init = floatval($_POST["fp_km_init"]);

// Use prepared statement
$stmt = $conn->prepare("UPDATE administrative_foi_parcurs SET fp_detalii=?, fp_plecare=?, fp_sosire=?, fp_km=?, fp_km_final=?, fp_km_init=? WHERE fp_ID=?");
$stmt->bind_param("sssdddi", $fp_detalii, $fp_plecare, $fp_sosire, $fp_km, $fp_km_final, $fp_km_init, $cID);

if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }
else{
$stmt->close();
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
        window.history.go(-1);
}
setTimeout('delayer()', 1500);
//-->
</script>";
include '../bottom.php';
exit();
}
}
}

else
{
	If (IsSet($_GET['mode']) AND $_GET['mode']=="fill")
{	
$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$dd=0;
for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 
 if(in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
	 $dd=$dd;
 }
 else
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
        <p><a href="personalcarsheets.php" class="button"><?php echo $strBack?>&nbsp;<i
                    class="fas fa-backward fa-xl"></i></a></p>
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
 
 if(in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
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
 else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
		// Use prepared statement
		$stmt = $conn->prepare("SELECT * FROM administrative_foi_parcurs WHERE fp_zi=? AND fp_luna=? AND fp_an=? AND fp_aloc=?");
		$stmt->bind_param("iiis", $i, $month, $year, $code);
		$stmt->execute();
		$result = $stmt->get_result();
		$numar = $result->num_rows;
		$row = $result->fetch_assoc();
		$stmt->close();
		if ($numar==0)
{
?>

            <tr>
                <form method="post"
                    Action="carsheets.php?mode=new&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                    <td><input name="fp_zi" type="text" value="<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="month" type="text" value="<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="year" type="text" value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="fp_numar" type="text" size="4" value="<?php echo htmlspecialchars($carplate, ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="fp_plecare" type="text" size="4" value="" /></td>
                    <td><input name="fp_sosire" type="text" size="4" value="" /></td>
                    <td><input name="fp_km_init" id="fp_km_init_<?php echo $i?>" type="text" size="4" value=""
                            oninput="calculate(<?php echo $i?>)" /></td>
                    <td><input name="fp_km" type="text" id="fp_km_<?php echo $i?>" size="4" value=""
                            oninput="calculate(<?php echo $i?>)" /></td>
                    <td><input name="fp_km_final" id="fp_km_final_<?php echo $i?>"" type=" text" size="4" value="" />
                    </td>
                    <td><input name="fp_detalii" type="text" size="4" value="" /></td>
                    <td><input type="submit" Value="<?php echo $strAdd?>" class="button" name="Submit"></td>
                    <td>
                        <p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></p>
                    </td>
                </form>
            </tr>
            <?php
 }
 else
 {?>

            <tr>
                <form method="post"
                    Action="carsheets.php?mode=edit&cID=<?php echo htmlspecialchars($row["fp_ID"], ENT_QUOTES, 'UTF-8')?>&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                    <td><input name="fp_zi" type="text" value="<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="month" type="text" value="<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="year" type="text" value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="fp_numar" type="text" size="4" value="<?php echo htmlspecialchars($row["fp_numar"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="fp_plecare" type="text" size="4" value="<?php echo htmlspecialchars($row["fp_plecare"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="fp_sosire" type="text" size="4" value="<?php echo htmlspecialchars($row["fp_sosire"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="fp_km_init" type="text" id="fp_km_init_<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" size="4"
                            value="<?php echo htmlspecialchars($row["fp_km_init"], ENT_QUOTES, 'UTF-8')?>" oninput="calculate(<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>)" /></td>
                    <td><input name="fp_km" type="text" id="fp_km_<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" size="4"
                            value="<?php echo htmlspecialchars($row["fp_km"], ENT_QUOTES, 'UTF-8')?>" oninput="calculate(<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>)" /></td>
                    <td><input name="fp_km_final" id="fp_km_final" type="text" size="4"
                            value="<?php echo htmlspecialchars($row["fp_km_final"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="fp_detalii" type="text" size="4" value="<?php echo htmlspecialchars($row["fp_detalii"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input type="submit" Value="<?php echo $strModify?>" class="button" name="Submit"></td>
                    <td>
                        <a href="carsheets.php?mode=delete&cID=<?php echo htmlspecialchars($row["fp_ID"], ENT_QUOTES, 'UTF-8')?>&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>"
                            class="ask button" OnClick="return confirm('<?php echo htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8')?>');">
                            <i class="fa fa-eraser fa-xl" title="<?php echo $strDelete?>"></i></a>
                    </td>
                </form>
            </tr>

            <?php }
 }
 }
 ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td colspan="10"><em></em></td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php
 }
 elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="show")
 {
	$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$dd=0;
for ( $i = 1; $i <= $d; $i ++) {
 $monthday=$i;
 $dayofmonth=$year."-".$month."-".$i;
 $namedayofthemonth= date('D', strtotime($dayofmonth));
 
 
 if(in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
 {
	 $dd=$dd;
 }
 else
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
        <p><a href="personalcarsheets.php" class="button"><?php echo $strBack?>&nbsp;<i
                    class="fas fa-backward fa-xl"></i></a></p>
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
 
 if(in_Array($dayofmonth, $holidays) OR in_array($namedayofthemonth, $skipdays))
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
 else
 {
	 		$dateObj   = DateTime::createFromFormat('Y-m-d', $dayofmonth);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'EEEE, d MMMM Y');
		$dayname = $formatter->format($dateObj);
		// Use prepared statement
		$stmt = $conn->prepare("SELECT * FROM administrative_foi_parcurs WHERE fp_zi=? AND fp_luna=? AND fp_an=? AND fp_aloc=?");
		$stmt->bind_param("iiis", $i, $month, $year, $code);
		$stmt->execute();
		$result = $stmt->get_result();
		$numar = $result->num_rows;
		$row = $result->fetch_assoc();
		$stmt->close();
	
		if (isset ($row)){;?>
    <tr>
        <td><?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["fp_plecare"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["fp_sosire"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["fp_km_init"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["fp_km_final"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["fp_detalii"], ENT_QUOTES, 'UTF-8')?></td>
    </tr>
    <?php 
		}
		else 
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
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td colspan="4"><em></em></td>
            <td>&nbsp;</td>
        </tr>
    </tfoot>
</table>
<?php
 }
?>
</div>
</div>
<?php
include '../bottom.php';
?>