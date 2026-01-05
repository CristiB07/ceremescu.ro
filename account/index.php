<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Intrare cont";
include '../header.php';

?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell text-center">
        <form method="POST" action="validate.php<?php if(isset($_GET['redirect'])) echo '?redirect=' . urlencode($_GET['redirect']); ?>">
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
$allowed_messages = ['WP', 'NL'];
$message = isset($_GET['message']) ? $_GET['message'] : '';

If ($message == "WP"){
echo "<div class=\"callout alert\">$strWrongCredentials</div>" ;
}
ElseIf ($message == "NL"){
echo "<div class=\"callout alert\">$strNotLogedIn</div>" ;
}?>
                <div class="grid-x grid-margin-x">
                    <div class="large-4 medium-4 small-4 cell">

                    </div>
                    <div class="large-4 medium-4 small-4 cell text-center">
                        <div class="callout secondary">
                            <label>
                                <h3><?php echo $strUserName ?></h3>
                                <input type="text" id="username" name="username"
                                    placeholder="<?php echo $strUserName ?>" />
                                <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                                <?php if(isset($_GET['redirect'])): ?>
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8'); ?>" />
                                <?php endif; ?>
                            </label>
                            <label>
                                <h3><?php echo $strPassword ?></h3>
                                <input type="password" id="password" name="password"
                                    placeholder="<?php echo $strPassword ?>" />
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