<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Acces test";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/account/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
if (isSet($_GET["tID"])) {
	$test = (int)$_GET["tID"]; // Convert to integer for security
}
else
{
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = document.referrer
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, "SELECT * FROM elearning_tests_takes WHERE take_stud_id = ? AND take_test_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $uid, $test);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$numar = mysqli_num_rows($result);
if ($numar==0)
{
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <div class="callout warning">
            <h1>Atenție</h1>
            <p>Un test poate fi dat o singură dată. Nu accesați testul decât atunci când doriți să-l finalizați.</p>
            <p>Dacă sunteți decis să finalizați acum testul, apăsați butonul „Sunt de acord”. Dacă nu, apăsați butonul
                „Înapoi”.</p>
        </div>
    </div>
    <div class="large-12 medium-12 small-12 text-center cell">
        <a class="large button" href="student_sitetests.php?tID=<?php echo (int)$_GET['tID'] ?>"><?php echo $strIAgree?></a>
        <a class="large button" href="student_mycourses.php?cID=<?php echo (int)$_GET['cID'] ?>#panel2d"><?php echo $strBack?></a>
    </div>
</div>
</div>
<?php
} else {
    ?>
    <div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
    echo "<div class=\"callout alert\"><p>$strYouHaveAlreadyTakenThisTest</p><p align=\"center\"><a class=\"button\" href=\"student_sitetests.php?tID=" . (int)$_GET['tID'] . "\"><i class=\"fas fa-search fa-xl\"></i> $strViewResults</a>   <a href=\"javascript:history.go(-1)\" class=\"button\"><i class=\"fa fa-backward fa-xl\"></i> $strBack</a></p></div>";
    
include '../bottom.php';
die;
}
?>
    </div>
    </div>
<?php
include '../bottom.php';
?>