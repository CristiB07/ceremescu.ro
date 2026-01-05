   <?php
   session_start(); 
include '../settings.php';
require_once '../classes/common.php';
$strPageTitle="Schimbare parolă";
include '../header.php';
if (isSet($_GET['hash']) && $_GET['hash']!="" && isSet($_GET['reset']) && $_GET['reset']!="") {
    // Validate hash and reset parameters (should be alphanumeric)
    if (!preg_match('/^[a-zA-Z0-9]+$/', $_GET['hash']) || !preg_match('/^[a-zA-Z0-9]+$/', $_GET['reset'])) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success = false;
    $csrf_error = "";
    
    // Validate CSRF token
    $myhash = isset($_POST['hash']) ? $_POST['hash'] : '';
    if ($myhash != $_SESSION['_token']) {
        $csrf_error = "Invalid CSRF token";
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Validate required POST fields
    if (!isset($_POST['Password']) || !isset($_POST['ConfirmPassword']) || !isset($_POST['account_activation']) || 
        empty($_POST['Password']) || empty($_POST['ConfirmPassword']) || empty($_POST['account_activation'])) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Validate passwords match
    if ($_POST['Password'] !== $_POST['ConfirmPassword']) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Validate password strength (minimum 8 characters)
    if (strlen($_POST['Password']) < 8) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    // Validate activation code format (should be numeric, 10 digits)
    if (!preg_match('/^[0-9]{10}$/', $_POST['account_activation'])) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
    
    $password = password_hash($_POST["Password"], PASSWORD_BCRYPT);
    $activationcode = $_POST["account_activation"];
    $hash = $_GET['hash'];
    $reset = $_GET['reset'];
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_activation=? AND account_active='0' AND account_secret=? AND account_reset_hash=? AND account_reset_expire >= NOW()");
    $stmt->bind_param("sss", $activationcode, $hash, $reset);
    $stmt->execute();
    $changepasswordquery = $stmt->get_result();
    $changepasswordcount = $changepasswordquery->num_rows;
    
    if ($changepasswordcount==1) {
        // Use prepared statement for update
        $stmt_update = $conn->prepare("UPDATE site_accounts SET account_password=?, account_active='1', account_reset_hash='', account_reset_expire='', account_secret='', account_activation='' WHERE account_activation=? AND account_secret=?");
        $stmt_update->bind_param("sss", $password, $activationcode, $hash);
        $changepasswordresult = $stmt_update->execute();
        $stmt_update->close();
        
        if ($changepasswordresult) {
            $stmt->close();
            echo "<div class=\"callout success\"><p>$strPasswordChangedSuccesfully.</p>.
       <p align=\"center\"><a href=\"../account/login.php\" class=\"button\">$strLogin</a></p></div></div></div>";
        }
        else {
            $stmt->close();
            echo "<div class=\"callout alert\"><p>$strThereWasAnError</p>.
       <p align=\"center\"><a href=\"../account/forgotpassword.php\" class=\"button\">$strTryAgain</a></p></div></div></div>";
        }
    }
    else {
        $stmt->close();
        header("location:$strSiteURL/account/login.php?message=WP");
        exit();
    }
} //end post
else {
   if (empty($_SESSION['_token'])) {
  $_SESSION['_token'] = bin2hex(random_bytes(32));
   }
$_SESSION["token_expire"] = time() + 1800; // 30 minutes = 1800 secs
$csrf_error = "";
$token = $_SESSION['_token'];
$token_expire = $_SESSION["token_expire"];
?>
 <script>
document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('Password');
  const confirmInput = document.getElementById('ConfirmPassword');
  const matchMsg = document.getElementById('CheckPasswordMatch');

  const meter = document.querySelector('.pwd-meter');
  const bar = document.querySelector('.pwd-bar');
  const label = document.getElementById('pwdStrengthLabel');
  const hintsEl = document.getElementById('pwdHints');

  // Parole comune (poți extinde lista după nevoie)
  const COMMON = new Set([
    '123456', '123456789', 'password', 'parola', 'qwerty', '12345678',
    '111111', '123123', 'abc123', 'iloveyou', 'admin', 'welcome', '000000'
  ]);

  // Funcție de scor și feedback
  function scorePassword(pwd) {
    const hints = [];

    if (!pwd) {
      return { score: 0, percent: 0, level: 'very-weak', text: 'Introduceți o parolă.', hints };
    }

    const length = pwd.length;
    const hasLower = /[a-z]/.test(pwd);
    const hasUpper = /[A-Z]/.test(pwd);
    const hasDigit = /\d/.test(pwd);
    const hasSymbol = /[^A-Za-z0-9]/.test(pwd);

    // Punctaj de bază: lungime + diversitate
    let score = 0;
    if (length >= 8) score += 25;        // prag minim recomandat
    if (length >= 12) score += 15;       // bonus lungime
    if (hasLower) score += 10;
    if (hasUpper) score += 10;
    if (hasDigit) score += 10;
    if (hasSymbol) score += 15;

    // Penalizări: repetiții, secvențe simple, doar un tip de caracter
    const repeated = /(.)\1{2,}/.test(pwd); // 3+ caractere identice
    if (repeated) {
      score -= 10;
      hints.push('Evitați caractere repetate (ex: „aaa”, „111”).');
    }

    const sequential = /(0123|1234|2345|3456|4567|5678|6789|abcd|qwerty)/i.test(pwd);
    if (sequential) {
      score -= 10;
      hints.push('Evitați secvențe previzibile (ex: „1234”, „abcd”, „qwerty”).');
    }

    const singleClass = (
      (hasLower && !hasUpper && !hasDigit && !hasSymbol) ||
      (hasUpper && !hasLower && !hasDigit && !hasSymbol) ||
      (hasDigit && !hasLower && !hasUpper && !hasSymbol) ||
      (hasSymbol && !hasLower && !hasUpper && !hasDigit)
    );
    if (singleClass) {
      score -= 10;
      hints.push('Folosiți o combinație de litere mari/mici, cifre și simboluri.');
    }

    // Penalizare pentru parole comune
    if (COMMON.has(pwd.toLowerCase())) {
      score -= 20;
      hints.push('Evitați parole comune (ex: „123456”, „password”).');
    }

    // Normalizare scor în 0–100
    score = Math.max(0, Math.min(100, score));

    // Nivel + text
    let level = 'very-weak', text = 'Foarte slabă';
    if (score >= 15 && score < 35) { level = 'weak'; text = 'Slabă'; }
    else if (score >= 35 && score < 60) { level = 'fair'; text = 'Acceptabilă'; }
    else if (score >= 60 && score < 80) { level = 'strong'; text = 'Puternică'; }
    else if (score >= 80) { level = 'excellent'; text = 'Excelentă'; }

    // Recomandări generale
    if (length < 12) hints.push('Măriți lungimea: 12+ caractere este ideal.');
    if (!hasLower || !hasUpper) hints.push('Includeți atât litere mici, cât și litere mari.');
    if (!hasDigit) hints.push('Adăugați cel puțin o cifră.');
    if (!hasSymbol) hints.push('Adăugați simboluri (ex: !@#$%^&*).');

    const percent = score; // deja în 0–100

    return { score, percent, level, text, hints };
  }

  function renderStrength(result) {
    // Update bară
    bar.style.width = result.percent + '%';
    meter.classList.remove('pwd-very-weak', 'pwd-weak', 'pwd-fair', 'pwd-strong', 'pwd-excellent');
    meter.classList.add('pwd-' + result.level);
    meter.setAttribute('aria-valuenow', String(result.percent));
    label.textContent = 'Rezistență parolă: ' + result.text;

    // Hints
    hintsEl.innerHTML = '';
    result.hints.slice(0, 3).forEach(h => {
      const li = document.createElement('li');
      li.textContent = h;
      hintsEl.appendChild(li);
    });
  }

  // Evenimente: evaluare live + potrivire confirmare
  function evaluate() {
    const pwd = passwordInput.value;
    const result = scorePassword(pwd);
    renderStrength(result);

    // Mesaj potrivire confirmare (ca în scriptul anterior)
    const confirmPassword = confirmInput.value;
    if (confirmPassword.length > 0) {
      if (pwd !== confirmPassword) {
        matchMsg.textContent = 'Parola nu se potrivește !';
        matchMsg.style.color = 'red';
      } else {
        matchMsg.textContent = 'Parola se potrivește !';
        matchMsg.style.color = 'green';
      }
    } else {
      matchMsg.textContent = '';
    }
  }

  passwordInput.addEventListener('input', evaluate);
  confirmInput.addEventListener('input', evaluate);

  // Evaluare inițială
  evaluate();
});
</script>
   <div class="large-12 medium-12 small-12 cell text-center">
        <form method="POST" action="changepassword.php?hash=<?php echo htmlspecialchars($_GET['hash'], ENT_QUOTES, 'UTF-8'); ?>&reset=<?php echo htmlspecialchars($_GET['reset'], ENT_QUOTES, 'UTF-8'); ?>">
            <fieldset>
                <legend>
                    <h2><?php echo $strChangePasswordForm ?></h2>
                </legend>
                <div class="grid-x grid-margin-x">
                    <div class="large-4 medium-4 small-4 cell">

                    </div>
                    <div class="large-4 medium-4 small-4 cell text-center">
                        <div class="callout secondary">
                            <div class="large-4 cell">
                 <label for="Password"><?php echo $strNewPassword?></label>
                <input type="password" id="Password" name="Password" autocomplete="new-password" />
                                    <!-- Indicator rezistență parolă -->
                <div class="pwd-strength" aria-live="polite">
                    <div class="pwd-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Rezistență parolă">
                    <div class="pwd-bar"></div>
                </div>
                     <p id="pwdStrengthLabel" class="pwd-label">Introduceți o parolă.</p>
                    <ul class="pwd-hints" id="pwdHints"></ul>
                         <label for="ConfirmPassword"><?php echo $strConfirmPassword?></label>
                    <input type="password" id="ConfirmPassword" name="ConfirmPassword" autocomplete="new-password" />
                    <p id="CheckPasswordMatch" aria-live="polite"></p>
                    </label>
                     <label><?php echo $strConfirmationCode ?>
                                <input type="text" id="account_activation" name="account_activation"
                                    placeholder="<?php echo $strConfirmationCode ?>" />
                                <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                            </label>
                     <p><input type="submit" class="button" value="<?php echo $strChange ?>" /></p>
                     <input type="hidden" id="hash" name="hash" value="<?php echo $token ?>" />
                </div>
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
   }//ends form
   }//ends we have hash
   else
   {	//he just try to get here directly or something is wrong
header("location:$strSiteURL". "/account/login.php?message=ER");
exit();
   }
   include '../bottom.php';
   ?>