<?php
//update 18.07.2025
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
	header("location:$strSiteURL/elearning/login.php?message=MLF");
}
$uid=$_SESSION['uid'];
?>
    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
  <div class="callout warning">
  <h1>Atenție</h1>
  <p>Un test poate fi dat o singură dată. Nu accesați testul decât atunci când doriți să-l finalizați.</p>
  <p>Dacă sunteți decis să finalizați acum testul, apăsați butonul „Sunt de acord”. Dacă nu, apăsați butonul „Înapoi”.</p>
  </div>
  </div>
  <div class="large-12 medium-12 small-12 cell"><p align="center">
  <a class="large button" href="student_sitetests.php?tID=<?php echo $_GET["tID"] ?>"><?php echo $strIAgree?></a>
  <a class="large button" href="student_mycourses.php?cID=<?php echo $_GET["cID"] ?>"><?php echo $strBack?></a></p>
  </div>
  </div>
</div>
<?php
include '../bottom.php';
?>
