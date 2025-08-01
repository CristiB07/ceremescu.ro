<?php 
//updated 29.07.2025
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Salvare bază de date";
include '../dashboard/header.php';
if(!isset($_SESSION)) 
{ 
	session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
	header("location:$strSiteURL/login/login.php?message=MLF");
}
?>
<script language="JavaScript" type="text/JavaScript">
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
	document.getElementById('loader').style.display = 'none';
  }
</script>
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">
<?php
echo "<h1>$strPageTitle</h1>";
?>
<script src="../js/foundation/jquery.js"></script>
<script>
$('#myIframe').on('load', function(){
    $('#loader').fadeOut();
});
</script>
<div class="row align-center">
<div class="large-12 medium-12 small-12 cell">
<div id="loader" class="center"></div>
</div>
</div>
<iframe width="100%" height="600" src="backupdb.php" frameBorder="0" scrolling="no" onload="resizeIframe(this)" id="myIframe"></iframe>

<?php
include '../bottom.php';
?>