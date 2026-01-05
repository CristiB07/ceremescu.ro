<?php
//updated 15.05.2025
session_start(); 
include '../settings.php';
require_once '../classes/common.php';
$strPageTitle="Intrare cont";
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
include '../lang/language_RO.php';
}
else
{
include '../lang/language_EN.php';
}

// Input validation and sanitization
if (isSet($_GET['hash']) && $_GET['hash']!="" && isSet($_GET['reset']) && $_GET['reset']!="") {
    // Validate hash and reset parameters (should be alphanumeric)
  
    if (!preg_match('/^[a-zA-Z0-9]+$/', $_GET['hash']) || !preg_match('/^[a-zA-Z0-9]+$/', $_GET['reset'])) {
        header("location:$strSiteURL/account/login.php?message=ER");
        exit();
    }
if ($_SERVER["REQUEST_METHOD"] == "POST") {


// Validate and sanitize POST data
if (!isset($_POST['account_activation']) || !preg_match('/^[0-9]{10}$/', $_POST['account_activation'])) {
    header("location:$strSiteURL/account/login.php?message=ER");
    exit();
}

// username and password sent from form
$confirmationcode=$_POST['account_activation'];
$hash = $_GET['hash'];
$reset = $_GET['reset'];

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM site_accounts WHERE account_activation=? AND account_secret=? AND account_reset_hash=? AND account_reset_expire >= NOW()");
$stmt->bind_param("sss", $confirmationcode, $hash, $reset);
$stmt->execute();
$resetquery = $stmt->get_result();
$resetcount = $resetquery->num_rows;
// If result matched $confirmationcode, table row must be 1 row		
if($resetcount==1){
$row = $resetquery->fetch_assoc();


$_SESSION['uid'] = $row['account_id']; // store session data
$_SESSION['userlogedin']="Yes";
$_SESSION['clearence'] = $row['account_role']; // store session data
$_SESSION['function']=$row['account_function'];
$_SESSION['userlogedin']="Yes";
$d = date("Y-m-d H:i:s");

// Use prepared statement to prevent SQL injection
$stmt_update = $conn->prepare("UPDATE site_accounts SET account_lastlogin=? WHERE account_id=?");
$stmt_update->bind_param("si", $d, $row['account_id']);
$stmt_update->execute();
$stmt_update->close();

$stmt->close();
header("location:$strSiteURL". "/dashboard/dashboard.php");
exit();
}

else {
    $stmt->close();
    echo "No user match";
header("location:$strSiteURL". "/account/login.php?message=WP");
exit();
}
} //end post
else { 	?>
<!doctype html>

<head>
    <!--Start Header-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $strSiteName ?>: <?php echo $strPageTitle ?></title>
    <meta name="rating" content="General" />
    <meta name="author" content="<?php echo $strSiteOwner ?>" />
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
<style>
    :root {
        --accent: #2f6fed;
        --border: #cfd8dc;
        --error: #d32f2f;
        --ok: #2e7d32;
    }

    body {
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        margin: 0;
        padding: 24px;
        background: #f7f9fc;
        color: #0f172a;
    }

    .container {
        max-width: 75%;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        padding: 24px;
    }

    h1 {
        font-size: 1.25rem;
        margin: 0 0 8px;
    }

    p.helper {
        margin: 0 0 16px;
        color: #475569;
    }

    .inputs {
        display: grid;
        grid-template-columns: repeat(10, 1fr);
        gap: 10px;
        margin: 20px 0;
    }

    .inputs input {
        width: 80%;
        aspect-ratio: 1 / 1;
        text-align: center;
        font-size: clamp(16px, 6vw, 24px);
        border: 2px solid var(--border);
        border-radius: 10px;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        background: #fbfdff;
    }

    .inputs input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.15);
        background: #fff;
    }

    .inputs input.valid {
        border-color: #9ccc65;
    }

    .inputs input.error {
        border-color: var(--error);
        animation: shake 120ms linear 2;
    }

    @keyframes shake {
        0% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-2px);
        }

        50% {
            transform: translateX(2px);
        }

        75% {
            transform: translateX(-2px);
        }

        100% {
            transform: translateX(0);
        }
    }

    .actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    button {
        appearance: none;
        border: none;
        background: var(--accent);
        color: #fff;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: filter 0.15s, transform 0.02s;
    }

    button:hover {
        filter: brightness(1.07);
    }

    button:active {
        transform: translateY(1px);
    }

    .message {
        min-height: 1.5em;
        font-size: 0.95rem;
    }

    .message.error {
        color: var(--error);
    }

    .message.ok {
        color: var(--ok);
    }

    .visually-hidden {
        position: absolute !important;
        height: 1px;
        width: 1px;
        overflow: hidden;
        clip: rect(1px, 1px, 1px, 1px);
        white-space: nowrap;
        border: 0;
        padding: 0;
        margin: -1px;
    }
</style>

<body>
  <div class="large-12 medium-12 small-12 cell text-center">
        <title>Introdu codul din 10 cifre</title>

    <main class="container">
        <h1>Introdu codul din 10 cifre</h1>
        <p id="hint" class="helper">Se acceptă doar cifre între 0 și 9.</p>

        <!-- Observație: action include parametrul hash și reset din query,
         iar câmpul hidden 'hash' conține tokenul PHP separat, conform cerinței. -->
        <form id="codeForm" method="POST"
            action="validatelogin.php?hash=<?php echo htmlspecialchars($_GET['hash'], ENT_QUOTES, 'UTF-8'); ?>&reset=<?php echo htmlspecialchars($_GET['reset'], ENT_QUOTES, 'UTF-8'); ?>" novalidate>
       
            <!-- Hidden pentru codul complet (10 cifre) ce va fi trimis la POST) -->
            <input type="hidden" id="account_activation" name="account_activation" />

            <div class="inputs" role="group" aria-labelledby="hint">
                <!-- 10 căsuțe numerice -->
                <input id="cell-0" inputmode="numeric" maxlength="1" aria-label="Cifra 1" />
                <input id="cell-1" inputmode="numeric" maxlength="1" aria-label="Cifra 2" />
                <input id="cell-2" inputmode="numeric" maxlength="1" aria-label="Cifra 3" />
                <input id="cell-3" inputmode="numeric" maxlength="1" aria-label="Cifra 4" />
                <input id="cell-4" inputmode="numeric" maxlength="1" aria-label="Cifra 5" />
                <input id="cell-5" inputmode="numeric" maxlength="1" aria-label="Cifra 6" />
                <input id="cell-6" inputmode="numeric" maxlength="1" aria-label="Cifra 7" />
                <input id="cell-7" inputmode="numeric" maxlength="1" aria-label="Cifra 8" />
                <input id="cell-8" inputmode="numeric" maxlength="1" aria-label="Cifra 9" />
                <input id="cell-9" inputmode="numeric" maxlength="1" aria-label="Cifra 10" />
            </div>

            <div class="actions">
                <button type="button" id="clearBtn">Șterge</button>
                <button type="submit">Verifică codul</button>
            </div>

            <p id="msg" class="message" aria-live="polite"></p>
        </form>
    </main>

    <script>
        // Accept doar cifre
        const ALLOWED = /^[0-9]$/;

        const inputs = [...document.querySelectorAll('.inputs input')];
        const form = document.getElementById('codeForm');
        const msg = document.getElementById('msg');
        const clearBtn = document.getElementById('clearBtn');
        const accountActivation = document.getElementById('account_activation');

        // Focus pe prima căsuță la încărcare
        window.addEventListener('DOMContentLoaded', () => inputs[0].focus(), { once: true });

        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                const raw = e.target.value.replace(/\D/g, ''); // elimină orice non-cifră
                if (!raw) {
                    e.target.value = '';
                    e.target.classList.remove('valid', 'error');
                    return;
                }
                e.target.value = raw[0];
                e.target.classList.add('valid');
                if (idx < 9) inputs[idx + 1].focus();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (input.value === '' && idx > 0) {
                        inputs[idx - 1].value = '';
                        inputs[idx - 1].focus();
                    } else {
                        input.value = '';
                        input.classList.remove('valid');
                    }
                    e.preventDefault();
                }

                // opțional: blochează taste non-numerice (except control)
                if (!['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'].includes(e.key)) {
                    if (!ALLOWED.test(e.key)) {
                        e.preventDefault();
                    }
                }
            });

            // Lipire cod numeric complet
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const data = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                const chars = [...data].slice(0, 10);
                inputs.forEach((inp, i) => {
                    const v = chars[i] || '';
                    inp.value = v;
                    inp.classList.toggle('valid', !!v);
                });
                const next = chars.length < 10 ? chars.length : 9;
                inputs[next].focus();
            });
        });

        clearBtn.addEventListener('click', () => {
            inputs.forEach(inp => {
                inp.value = '';
                inp.classList.remove('valid', 'error');
            });
            msg.textContent = '';
            msg.className = 'message';
            inputs[0].focus();
        });

        form.addEventListener('submit', (e) => {
            // Construiește codul
            const code = inputs.map(i => i.value.replace(/\D/g, '')).join('');

            // Validează lungimea
            if (code.length !== 10) {
                e.preventDefault();
                msg.textContent = 'Codul trebuie să conțină exact 10 cifre.';
                msg.className = 'message error';
                return;
            }

            // Plasează codul în câmpul hidden ce se trimite la POST
            account_activation.value = code;

            // (opțional) feedback vizual înainte de trimitere
            msg.textContent = `Codul introdus: ${code}`;
            msg.className = 'message ok';

            // Nu mai prevenim submit-ul; formularul se va trimite normal
            // către validatelogin.php cu query (hash/reset) + POST (account_activation + hash hidden).
        });
    </script>
</div>
<?php
}}
else {
	//he just try to get here directly or something is wrong
header("location:$strSiteURL". "/account/login.php?message=ER");
exit();
}
include '../bottom.php';
?>