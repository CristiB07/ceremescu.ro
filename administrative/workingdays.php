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
	header("location:$strSiteURL/login/login.php?message=MLF");
	exit();
}

$uid = $_SESSION['uid'];
$code = $_SESSION['code'];

// Validate month parameter
if (!isset($_GET['month']) || !is_numeric($_GET['month'])) {
    header("location: $strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
$month = intval($_GET['month']);
if ($month < 1 || $month > 12) {
    header("location: $strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
if ($month < 10) {
    $month = "0" . $month;
}

// Validate year parameter
if (!isset($_GET['year']) || !is_numeric($_GET['year'])) {
    header("location: $strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}
$year = intval($_GET['year']);
if ($year < 2000 || $year > 2100) {
    header("location: $strSiteURL/administrative/personalworkingdays.php?message=Error");
    exit();
}

// Validate mode parameter
$allowed_modes = ['delete', 'new', 'edit', 'fill', 'show'];
$mode = isset($_GET['mode']) && in_array($_GET['mode'], $allowed_modes, true) ? $_GET['mode'] : '';

if ($mode === "delete") {
    // Validate cID
    if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
        header("location: workingdays.php?mode=fill&month=$month&year=$year&message=Error");
        exit();
    }
    $cID = intval($_GET['cID']);
    
    // Check authorization - user can only delete their own records
    $stmt_check = $conn->prepare("SELECT pontaj_ID FROM administrative_pontaje WHERE pontaj_ID=? AND pontaj_user=?");
    $stmt_check->bind_param("is", $cID, $code);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        $stmt_check->close();
        header("location: workingdays.php?mode=fill&month=$month&year=$year&message=Error");
        exit();
    }
    $stmt_check->close();
    
    // Perform deletion
    $stmt_del = $conn->prepare("DELETE FROM administrative_pontaje WHERE pontaj_ID=?");
    $stmt_del->bind_param("i", $cID);
    $stmt_del->execute();
    $stmt_del->close();
    
    echo "<div class=\"callout success\">" . htmlspecialchars($strRecordDeleted, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
    echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
    include '../bottom.php';
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode === "new") {
        // Validate inputs
        if (!isset($_POST["pontaj_zi"]) || !is_numeric($_POST["pontaj_zi"])) {
            header("location: workingdays.php?mode=fill&month=$month&year=$year");
            exit();
        }
        
        $pontaj_zi = intval($_POST["pontaj_zi"]);
        $pontaj_CO = isset($_POST["pontaj_CO"]) ? trim($_POST["pontaj_CO"]) : '';
        $pontaj_ore_WFH = isset($_POST["pontaj_ore_WFH"]) ? trim($_POST["pontaj_ore_WFH"]) : '';
        $pontaj_ore_T = isset($_POST["pontaj_ore_T"]) ? trim($_POST["pontaj_ore_T"]) : '';
        $pontaj_ore_B = isset($_POST["pontaj_ore_B"]) ? trim($_POST["pontaj_ore_B"]) : '';
        $pontaj_ore_A = isset($_POST["pontaj_ore_A"]) ? trim($_POST["pontaj_ore_A"]) : '';
        $pontaj_observatii = isset($_POST["pontaj_observatii"]) ? trim($_POST["pontaj_observatii"]) : '';
        
        // Insert with prepared statement
        $stmt = $conn->prepare("INSERT INTO administrative_pontaje(pontaj_user, pontaj_zi, pontaj_luna, pontaj_an, pontaj_CO, pontaj_ore_WFH, pontaj_ore_T, pontaj_ore_B, pontaj_ore_A, pontaj_observatii) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssssss", $code, $pontaj_zi, $month, $year, $pontaj_CO, $pontaj_ore_WFH, $pontaj_ore_T, $pontaj_ore_B, $pontaj_ore_A, $pontaj_observatii);
        
        if (!$stmt->execute()) {
            $stmt->close();
            header("location: workingdays.php?mode=fill&month=$month&year=$year");
            exit();
        }
        $stmt->close();
        
        echo "<div class=\"callout success\">" . htmlspecialchars($strRecordAdded, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        exit();
    }
elseif ($mode === "edit") {
        // Validate cID
        if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
            header("location: workingdays.php?mode=fill&month=$month&year=$year");
            exit();
        }
        $cID = intval($_GET['cID']);
        
        // Check authorization - user can only edit their own records
        $stmt_check = $conn->prepare("SELECT pontaj_ID FROM administrative_pontaje WHERE pontaj_ID=? AND pontaj_user=?");
        $stmt_check->bind_param("is", $cID, $code);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            $stmt_check->close();
            header("location: workingdays.php?mode=fill&month=$month&year=$year");
            exit();
        }
        $stmt_check->close();
        
        $pontaj_observatii = isset($_POST["pontaj_observatii"]) ? trim($_POST["pontaj_observatii"]) : '';
        $pontaj_CO = isset($_POST["pontaj_CO"]) ? trim($_POST["pontaj_CO"]) : '';
        $pontaj_ore_WFH = isset($_POST["pontaj_ore_WFH"]) ? trim($_POST["pontaj_ore_WFH"]) : '';
        $pontaj_ore_B = isset($_POST["pontaj_ore_B"]) ? trim($_POST["pontaj_ore_B"]) : '';
        $pontaj_ore_A = isset($_POST["pontaj_ore_A"]) ? trim($_POST["pontaj_ore_A"]) : '';
        $pontaj_ore_T = isset($_POST["pontaj_ore_T"]) ? trim($_POST["pontaj_ore_T"]) : '';
        
        // Update with prepared statement
        $stmt = $conn->prepare("UPDATE administrative_pontaje SET pontaj_observatii=?, pontaj_CO=?, pontaj_ore_WFH=?, pontaj_ore_B=?, pontaj_ore_A=?, pontaj_ore_T=? WHERE pontaj_ID=?");
        $stmt->bind_param("ssssssi", $pontaj_observatii, $pontaj_CO, $pontaj_ore_WFH, $pontaj_ore_B, $pontaj_ore_A, $pontaj_ore_T, $cID);
        
        if (!$stmt->execute()) {
            $stmt->close();
            header("location: workingdays.php?mode=fill&month=$month&year=$year");
            exit();
        }
        $stmt->close();
        
        echo "<div class=\"callout success\">" . htmlspecialchars($strRecordModified, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
        echo "<script type=\"text/javascript\">
<!--
function delayer(){
        window.history.go(-1);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
        include '../bottom.php';
        exit();
    }
}

else
{
	if ($mode === "fill")
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

echo htmlspecialchars($strNumberOfDaysInMonth, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($strInYear, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($strWas, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($d, ENT_QUOTES, 'UTF-8') . "<br />";
echo htmlspecialchars($strNumberOfWorkingDaysInMonth, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($strInYear, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($strWas, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($dd, ENT_QUOTES, 'UTF-8') . "<br />";
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <p><a href="personalworkingdays.php" class="button"><?php echo htmlspecialchars($strBack, ENT_QUOTES, 'UTF-8')?>&nbsp;<i
                    class="fas fa-backward fa-xl"></i></a></p>
    </div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <table width="100%" class="fixed-header">
            <thead>
                <tr>
                    <th width="5%"><?php echo htmlspecialchars($strDay, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="10%"><?php echo htmlspecialchars($strYear, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strPersonalTime, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strWorkFromHome, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strFieldWork, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strOffice, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strOther, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="45%"><?php echo htmlspecialchars($strDetails, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8')?></th>
                    <th width="5%"><?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?></th>
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
	 echo "<tr><td colspan=\"10\">" . htmlspecialchars($dayname, ENT_QUOTES, 'UTF-8') . ", este zi nelucrătoare.<td></tr>";
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
		
		// Use prepared statement for SELECT query
		$stmt_day = $conn->prepare("SELECT * FROM administrative_pontaje WHERE pontaj_zi=? AND pontaj_luna=? AND pontaj_an=? AND pontaj_user=?");
		$stmt_day->bind_param("isss", $i, $month, $year, $code);
		$stmt_day->execute();
		$result_day = $stmt_day->get_result();
		$numar = $result_day->num_rows;
		$row = $result_day->fetch_assoc();
		$stmt_day->close();

		if ($numar==0)
{
?>

            <tr>
                <form method="post"
                    Action="workingdays.php?mode=new&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                    <td><input name="pontaj_zi" type="text" value="<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="month" type="text" value="<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="year" type="text" value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="pontaj_CO" type="text" size="4" value="" /></td>
                    <td><input name="pontaj_ore_WFH" type="text" size="4" value="" /></td>
                    <td><input name="pontaj_ore_T" type="text" size="4" value="" /></td>
                    <td><input name="pontaj_ore_B" type="text" size="4" value="" /></td>
                    <td><input name="pontaj_ore_A" type="text" size="4" value="" /></td>
                    <td><input name="pontaj_observatii" type="text" size="4" value="" /></td>
                    <td><input type="submit" Value="<?php echo htmlspecialchars($strAdd, ENT_QUOTES, 'UTF-8')?>" class="button" name="Submit"></td>
                    <td>
                        <p class="button"><i class="fa fa-eraser fa-xl" title="<?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?>"></i></p>
                    </td>
                </form>
            </tr>
            <?php
 }
 else
 {?>

            <tr>
                <form method="post"
                    Action="workingdays.php?mode=edit&cID=<?php echo htmlspecialchars($row["pontaj_ID"], ENT_QUOTES, 'UTF-8')?>&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                    <td><input name="pontaj_zi" type="text" value="<?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="month" type="text" value="<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="year" type="text" value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>" readonly></td>
                    <td><input name="pontaj_CO" type="text" size="4" value="<?php echo htmlspecialchars($row["pontaj_CO"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input name="pontaj_ore_WFH" type="text" size="4" value="<?php echo htmlspecialchars($row["pontaj_ore_WFH"], ENT_QUOTES, 'UTF-8')?>" />
                    </td>
                    <td><input name="pontaj_ore_T" type="text" size="4" value="<?php echo htmlspecialchars($row["pontaj_ore_T"], ENT_QUOTES, 'UTF-8')?>" />
                    </td>
                    <td><input name="pontaj_ore_B" type="text" value="<?php echo htmlspecialchars($row["pontaj_ore_B"], ENT_QUOTES, 'UTF-8')?>" />
                    </td>
                    <td><input name="pontaj_ore_A" type="text" size="4" value="<?php echo htmlspecialchars($row["pontaj_ore_A"], ENT_QUOTES, 'UTF-8')?>" />
                    </td>
                    <td><input name="pontaj_observatii" type="text" size="4"
                            value="<?php echo htmlspecialchars($row["pontaj_observatii"], ENT_QUOTES, 'UTF-8')?>" /></td>
                    <td><input type="submit" Value="<?php echo htmlspecialchars($strModify, ENT_QUOTES, 'UTF-8')?>" class="button" name="Submit"></td>
                    <td>
                        <a href="workingdays.php?mode=delete&cID=<?php echo htmlspecialchars($row["pontaj_ID"], ENT_QUOTES, 'UTF-8')?>&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>"
                            class="ask button" OnClick="return confirm('<?php echo htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8')?>');">
                            <i class="fa fa-eraser fa-xl" title="<?php echo htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8')?>"></i></a>
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
                    <td colspan="9"><em></em></td>
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

echo htmlspecialchars($strNumberOfDaysInMonth, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($strInYear, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($year, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($strWas, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($d, ENT_QUOTES, 'UTF-8'). "<br />";
echo htmlspecialchars($strNumberOfWorkingDaysInMonth, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($strInYear, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($year, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($strWas, ENT_QUOTES, 'UTF-8')." ".htmlspecialchars($dd, ENT_QUOTES, 'UTF-8'). "<br />";
?>
<table width="100%">
    <thead>
        <tr>
            <th><?php echo htmlspecialchars($strDay, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strPersonalTime, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strWorkFromHome, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strFieldWork, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strOffice, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strOther, ENT_QUOTES, 'UTF-8')?></th>
            <th><?php echo htmlspecialchars($strDetails, ENT_QUOTES, 'UTF-8')?></th>
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
	 echo "<tr><td colspan=\"6\"><strong>" . htmlspecialchars($dayname, ENT_QUOTES, 'UTF-8'). ", este zi nelucrătoare.</strong><td></tr>";
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
		$stmt = $conn->prepare("SELECT * FROM administrative_pontaje WHERE pontaj_zi=? AND pontaj_luna=? AND pontaj_an=? AND pontaj_user=?");
		$stmt->bind_param("isss", $i, $month, $year, $code);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		if (isset ($row)){;?>
    <tr>
        <td><?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_CO"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_ore_WFH"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_ore_T"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_ore_B"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_ore_A"], ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($row["pontaj_observatii"], ENT_QUOTES, 'UTF-8')?></td>
    </tr>
    <?php 
		$stmt->close();
		}
		else 
		{?>
    <tr>
        <td><?php echo htmlspecialchars($i, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
        <td><?php echo htmlspecialchars($strNotFilled, ENT_QUOTES, 'UTF-8')?></td>
    </tr>
    <?php 
		$stmt->close();
		}
 }
 }
 }
 ?>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td colspan="5"><em></em></td>
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