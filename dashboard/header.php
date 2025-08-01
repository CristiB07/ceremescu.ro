<!doctype html>
<html class="no-js" lang="" dir="ltr">
<!-- update 08.01.2025-->
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $strSiteName ?>: <?php echo $strPageTitle ?></title>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css"/>
<link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname?>.css"/>
<link rel="shortcut icon" type="image/favicon" href="favicon.ico" />
<script language="javascript" type="text/javascript">
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
  }
</script>
</head>
<?php
//set the random id length 
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
Else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include '../lang/language_RO.php';
}
Else
{
	include '../lang/language_EN.php';
}
?>
</head>
<body>
<!-- Cookie alert -->
<!-- Start navigation-->
<div class="title-bar" data-responsive-toggle="responsive-menu" data-hide-for="medium">
  <button class="menu-icon" type="button" data-toggle="responsive-menu"></button>
  <div class="title-bar-title"><?php echo $strMenu?></div>
</div>
 <div class="top-bar" data-sticky data-options="marginTop:0;" style="width:100%">
	<div class="top-bar-left" id="responsive-menu">
		<ul class="dropdown menu" data-dropdown-menu>

			<li><a href="<?php echo $strSiteURL ?>/dashboard/dashboard.php"><i class="fas fa-home"></i></a></li>
 <?php
If (isSet($_SESSION['clearence']) AND $_SESSION['clearence'] == 'ADMIN')
{
	include 'admin.navigation.php';
}
ElseIF (isSet($_SESSION['clearence']) AND $_SESSION['clearence'] == 'USER')
{
	include 'user.navigation.php';
}
ElseIF (isSet($_SESSION['clearence']) AND $_SESSION['clearence'] == 'SHOP')
{
	include 'shop.navigation.php';
}
ElseIF (isSet($_SESSION['clearence']) AND $_SESSION['clearence'] == 'ELEARNING')
{
	include 'elearning.navigation.php';
}
else {

	//he just try to get here directly or something is wrong
header("location:../login/index.php?message=ER");
}
?>
 <!-- end navigation-->
	  <div class="grid-container">
	    <div class="grid-x grid-margin-x">
			  <div class="large-12 medium-12 small-12 cell">&nbsp;
	  </div>
	  	  </div>
	    <div class="grid-x grid-margin-x">
			<div class="large-4 medium-4 small-4 cell">
				<h1><a href="<?php echo $strSiteURL ?>/index.php"><img src="<?php echo $strSiteURL ?>/img/logo.png" width="300px"></a></h1>
			</div>
        <div class="large-8 medium-8 small-8 cell">
        </div>
      </div>
	  <!-- Ends header-->