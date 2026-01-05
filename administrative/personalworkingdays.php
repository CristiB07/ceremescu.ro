<?php // Last Modified Time: Monday, August 11, 2025 at 10:57:35 PM Eastern European Summer Time ?>
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
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$month= date('m');
$year=date('Y');
$lastmonth = date('m', strtotime(date('Y-m')." -1 month"));
$lastyear = date('Y', strtotime(date('Y-m')." -1 month"));
 //Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $month);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$currentmonthname = $formatter->format($dateObj);			
$dateObj   = DateTime::createFromFormat('!m', $lastmonth);
$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$lastmonthname = $formatter->format($dateObj);

// Validate message parameter
$allowed_messages = ['Error', 'Success'];
$message = isset($_GET['message']) && in_array($_GET['message'], $allowed_messages, true) ? $_GET['message'] : '';

if ($message === "Error") {
    echo "<div class=\"callout alert\">" . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
}
if ($message === "Success") {
    echo "<div class=\"callout success\">" . htmlspecialchars($strMessageSent, ENT_QUOTES, 'UTF-8') . "</div></div></div>";
}
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <h1><?php echo htmlspecialchars($strPageTitle, ENT_QUOTES, 'UTF-8')?></h1>
        <form method="post" id="users" Action="pwd2excel.php">
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <h3><?php echo htmlspecialchars($strSendWD, ENT_QUOTES, 'UTF-8')?></h3>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <h3><?php echo htmlspecialchars($strFillWD, ENT_QUOTES, 'UTF-8')?></h3>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <h3><?php echo htmlspecialchars($strShowWD, ENT_QUOTES, 'UTF-8')?></h3>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?>
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
    			echo "<option value=\"" . htmlspecialchars($m, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($monthname, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select> </label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <label><?php echo htmlspecialchars($strYear, ENT_QUOTES, 'UTF-8')?>
                        <select name="year">
                            <option value="0000" selected>--</option>
                            <?php
		$cy=date("Y");
		$fy=$cy+1;
		$py=$cy-1;
		for ( $y = $py; $y <= $fy; $y ++) {
    	echo "<option value=\"" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($y, ENT_QUOTES, 'UTF-8') . "</option>";} 
			?>
                        </select></label>
                </div>
                <div class="large-1 medium-1 small-1 cell">
                    <p align="right"><input type="submit" Value="<?php echo htmlspecialchars($strSend, ENT_QUOTES, 'UTF-8')?>" name="Submit" class="button">
                    </p>
                </div>

        </form>
        <script language="JavaScript" type="text/JavaScript">
            <!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
        </script>

        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?>
                <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                    <option value="00" selected>--</option>
                    <option value="workingdays.php?mode=fill&month=<?php echo htmlspecialchars($lastmonth, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($lastyear, ENT_QUOTES, 'UTF-8')?>">
                        <?php echo htmlspecialchars($lastmonthname . " " . $lastyear, ENT_QUOTES, 'UTF-8')?></option>
                    <option value="workingdays.php?mode=fill&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                        <?php echo htmlspecialchars($currentmonthname . " " . $year, ENT_QUOTES, 'UTF-8')?></option>
                </select> </label>
        </div>


        <div class="large-4 medium-4 small-4 cell">
            <label><?php echo htmlspecialchars($strMonth, ENT_QUOTES, 'UTF-8')?>
                <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                    <option value="00" selected>--</option>
                    <option value="workingdays.php?mode=show&month=<?php echo htmlspecialchars($lastmonth, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($lastyear, ENT_QUOTES, 'UTF-8')?>">
                        <?php echo htmlspecialchars($lastmonthname . " " . $lastyear, ENT_QUOTES, 'UTF-8')?></option>
                    <option value="workingdays.php?mode=show&month=<?php echo htmlspecialchars($month, ENT_QUOTES, 'UTF-8')?>&year=<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8')?>">
                        <?php echo htmlspecialchars($currentmonthname . " " . $year, ENT_QUOTES, 'UTF-8')?></option>
                </select> </label>
        </div>
    </div>
</div>
</div>
<hr />
<?php
include '../bottom.php';
?>