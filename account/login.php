<?php
//update 16.07.2025
include_once  __DIR__ .'/../settings.php';
include_once  __DIR__ . '/../classes/common.php';
$strPageTitle="Intrare cont";
$strKeywords="Accesare cont site " .$strSiteName;
$strDescription="Pagina de accesare cont pe" .$strSiteName;
$strPageTitle="Accesare cont pe " .$strSiteName;


if(!isset($_SESSION)) 
    { 
        session_start(); 
	}
		
if (!isSet($_SESSION['$lang'])) {
	$_SESSION['$lang']="RO";
	$lang=$_SESSION['$lang'];
}
else
{
	$lang=$_SESSION['$lang'];
}
if ($lang=="RO") {
include __DIR__ .'../../lang/language_RO.php';
}
else
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
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell text-center">
        <form method="POST" action="validate.php">
            <fieldset>
                <legend>
                    <h2><?php echo $strLoginForm ?></h2>
                </legend>
                <?php
if (empty($_SESSION['_token'])) {
  $_SESSION['_token'] = bin2hex(random_bytes(32));
$_SESSION["token_expire"] = time() + 1800; // 30 minutes = 1800 secs
}
$csrf_error = "";
$token = $_SESSION['_token'];
$token_expire = $_SESSION["token_expire"];

// Validate message parameter to prevent XSS
$allowed_messages = ['WP', 'ER', 'AC', 'NL'];
$message = isset($_GET['message']) ? $_GET['message'] : '';

If ($message == "WP"){
echo "<div class=\"callout alert\">$strWrongCredentials</div>" ;
}
elseIf ($message == "ER"){
echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
elseIf ($message == "AC"){
echo "<div class=\"callout success\">$strAccountActivated</div>" ;
}
elseIf ($message == "NL"){
echo "<div class=\"callout alert\">$strNotLogedIn</div>" ;
}?>
                <div class="grid-x grid-margin-x">
                    <div class="large-4 medium-4 small-4 cell">

                    </div>
                    <div class="large-4 medium-4 small-4 cell text-center">
                        <div class="callout secondary">
                            <label>
                                <h3><?php echo $strUserName ?></h3>
                                <input type="text" id="username" name="username" placeholder="<?php echo $strUserName ?>" />
                                <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                            </label>
                            <label>
                                <h3><?php echo $strPassword ?></h3>
                                <input type="password" id="password" name="password" placeholder="<?php echo $strPassword ?>" />
                            </label>
                            <p><input type="submit" class="button" value="<?php echo $strLogin ?>" /></p>
                            <p><a href="forgotpassword.php" class="button"><?php echo $strForgotPassword ?></a>
                                <a href="createaccount.php" class="button"><?php echo $strAddNewAccount?></a>
                            </p>
                        </div>
                    </div>
                    <div class="large-4 medium-4 small-4 cell">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
<?php
include '../bottom.php';
?>