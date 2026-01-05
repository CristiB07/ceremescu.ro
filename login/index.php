<?php // Last Modified Time: Saturday, August 23, 2025 at 5:24:36 PM Eastern European Summer Time ?>
<?php
// Verificare IP whitelist
include __DIR__ . '/ip_check.php';

include  __DIR__ .'/../settings.php';
include  __DIR__ . '/../classes/common.php';
$strPageTitle="Intrare cont";

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
include __DIR__ .'../../lang/language_RO.php';
}
Else
{
	include __DIR__ . '../../lang/language_EN.php';
}
if (empty($_SESSION['_token'])) {
  $_SESSION['_token'] = bin2hex(random_bytes(32));
$_SESSION["token_expire"] = time() + 1800; // 30 minutes = 1800 secs
}
$csrf_error = "";
$token = $_SESSION['_token'];
$token_expire = $_SESSION["token_expire"];
?>
<!doctype html>

<head>
    <!--Start Header-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $strSiteName ?>: <?php echo $strPageTitle ?></title>
    <meta name="rating" content="General" />
    <meta name="author" content="Consaltis Consultanţă şi Audit" />
    <meta name="language" content="romanian, RO" />
    <meta name="revisit-after" content="7 days" />
    <meta name="robots" content="noindex">
    <meta http-equiv="expires" content="never" />
    <link rel="shortcut icon" type="image/favicon" href="<?php echo $strSiteURL ?>/favicon.ico" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Insert this within your head tag and after foundation.css -->
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/foundation.css" />
    <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/<?php echo $cssname?>.css" />
    <link rel="shortcut icon" type="image/favicon" href="favicon.ico" />

    <!-- IE Fix for HTML5 Tags -->
    <!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>

<body>
    <div class="grid-container">
        <div class="grid-x grid-margin-x">
            <div class="large-12 cell text-center">
                <?php

If ((isSet($_GET['message'])) AND $_GET['message']=="WP"){
echo "<div class=\"callout alert\">$strWrongCredentials</div>" ;
}
If ((isSet($_GET['message'])) AND $_GET['message']=="MLF"){
echo "<div class=\"callout alert\">$strMustLoginFirst</div>" ;
}
If ((isSet($_GET['message'])) AND $_GET['message']=="NL"){
echo "<div class=\"callout alert\">$strNotLogedIn</div>" ;}

If ((isSet($_GET['message'])) AND $_GET['message']=="ER"){
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}?>

                <h1><?php echo $strLoginForm ?></h1>
                <form method="POST" action="<?php echo $strSiteURL ?>/login/validate.php">
                    <div class="grid-container">
                        <div class="grid-x grid-margin-x">
                            <div class="large-4 medium-4 small-4 cell">

                            </div>
                            <div class="large-4 medium-4 small-4 cell text-center">
                                <div class="callout secondary">
                                    <label><?php echo $strUserName ?>
                                        <input type="text" id="username" name="username"
                                            placeholder="<?php echo $strUserName ?>" />
                                        <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                                    </label>
                                    <label><?php echo $strPassword ?>
                                        <input type="password" id="password" name="password"
                                            placeholder="<?php echo $strPassword ?>" />
                                    </label>
                                    <input type="submit" Value="<?php echo $strLogin ?>" name="Submit"
                                        class="button success" />
                                </div>
                            </div>
                            <div class="large-4 medium-4 small-4 cell">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>