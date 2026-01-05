<?php
//update 16.07.2025
include '../settings.php';
include '../classes/common.php';
$strKeywords="Creare cont site " .$strSiteName;
$strDescription="Pagina de creare cont pe" .$strSiteName;
$strPageTitle="Creare cont pe " .$strSiteName;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../vendor/autoload.php';

include '../header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
$d = date("Y-m-d H:i:s");
 {
?>
<div class="grid-x grid-padding-x">
    <div class="large-12 medium-12 small-12 cell">

        <h1><?php echo $strPageTitle?></h1>

        <?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();

// Validate required fields
if (!isset($_POST['account_first_name'], $_POST['account_last_name'], $_POST['account_email'], 
    $_POST['account_phone'], $_POST['account_address'], $_POST['account_city'], $_POST['account_county'],
    $_POST['Password'], $_POST['ConfirmPassword'], $_POST['account_company'])) {
    die('<div class="callout alert">All fields are required!</div>');
}

// Validate email format
$email = filter_var(trim($_POST['account_email']), FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('<div class="callout alert">Invalid email format!</div>');
}

// Validate passwords match
if ($_POST['Password'] !== $_POST['ConfirmPassword']) {
    die('<div class="callout alert">Passwords do not match!</div>');
}

// Validate password strength (minimum 8 characters)
if (strlen($_POST['Password']) < 8) {
    die('<div class="callout alert">Password must be at least 8 characters!</div>');
}

// Check if email already exists
$stmt_check = $conn->prepare("SELECT account_id FROM site_accounts WHERE account_email=?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    $stmt_check->close();
    die('<div class="callout alert">Email already registered!</div>');
}
$stmt_check->close();

//insert new user
$password=password_hash($_POST["Password"], PASSWORD_BCRYPT);
$randnum = rand(1111111111,9999999999);
$secret=generateRandomString(10);
$role="CLIENT";
if ($shop==1 && $elearning==1) {
    $function="BOTH";
}
elseif ($shop==1 && $elearning==0) {
    $function="SHOP";
}
elseif ($shop==0 && $elearning==1) {
    $function="ELEARNING";
}
	
	// Sanitize input data
	$first_name = trim($_POST["account_first_name"]);
	$last_name = trim($_POST["account_last_name"]);
	$address = trim($_POST["account_address"]);
	$phone = trim($_POST["account_phone"]);
	$city = trim($_POST["account_city"]);
	$county = trim($_POST["account_county"]);
	
	// Use prepared statement to prevent SQL injection
	$stmt = $conn->prepare("INSERT INTO site_accounts(account_first_name, account_last_name, account_address, account_email, account_phone, account_city, account_county, account_password, account_activation, account_active, account_secret, account_function, account_role, account_enrollment) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, '0', ?, ?, ?, ?)");
	$stmt->bind_param("ssssssssissss", $first_name, $last_name, $address, $email, $phone, $city, $county, $password, $randnum, $secret, $function, $role, $d);
	
//It executes the SQL
if (!$stmt->execute())
  {
  $stmt->close();
  die('Error: ' . $conn->error);
  }

$companysiteaccount = $conn->insert_id;
$stmt->close();

//insert company if needed
	if ($_POST["account_company"]==0) {
		// Validate required company fields
		if (!isset($_POST['company_name'], $_POST['company_ro'], $_POST['company_VAT'], 
		    $_POST['company_reg'], $_POST['company_address'], $_POST['company_city'], $_POST['company_county'])) {
		    die('<div class="callout alert">All company fields are required!</div>');
		}
		
		// Sanitize company data
		$company_name = trim($_POST["company_name"]);
		$company_ro = trim($_POST["company_ro"]);
		$company_VAT = trim($_POST["company_VAT"]);
		$company_reg = trim($_POST["company_reg"]);
		$company_address = trim($_POST["company_address"]);
		$company_city = trim($_POST["company_city"]);
		$company_county = trim($_POST["company_county"]);
		$company_bank = isset($_POST["company_bank"]) ? trim($_POST["company_bank"]) : '';
		$company_IBAN = isset($_POST["company_IBAN"]) ? trim($_POST["company_IBAN"]) : '';
		
		// Use prepared statement for company insert
		$stmt_company = $conn->prepare("INSERT INTO site_companies(company_name, company_ro, company_VAT, company_reg, company_address, company_siteaccount, company_city, company_county, company_bank, company_IBAN) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt_company->bind_param("sssssissss", $company_name, $company_ro, $company_VAT, $company_reg, $company_address, $companysiteaccount, $company_city, $company_county, $company_bank, $company_IBAN);
		
		if (!$stmt_company->execute()) {
		  $stmt_company->close();
		  die('Error: ' . $conn->error);
		}
		$stmt_company->close();
	}


/// write and send email
//Cumpărător

$emailto = $email;
$emailtoname = htmlspecialchars($first_name . " " . $last_name, ENT_QUOTES, 'UTF-8');

If ($_POST["account_company"]==1) {
$cumparator="Date facturare: ".htmlspecialchars($last_name . " ". $first_name, ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Adresa: ".htmlspecialchars($address, ENT_QUOTES, 'UTF-8') . "<br />";
}
else {
$cumparator="Date facturare: ".htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "CUI: ".htmlspecialchars($company_VAT, ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Reg. Comert.: ".htmlspecialchars($company_reg, ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "Adresa: ".htmlspecialchars($company_address, ENT_QUOTES, 'UTF-8') . ", ".htmlspecialchars($company_city, ENT_QUOTES, 'UTF-8').", ".htmlspecialchars($company_city, ENT_QUOTES, 'UTF-8').".<br />";
$cumparator=$cumparator . "Banca: ".htmlspecialchars($company_bank, ENT_QUOTES, 'UTF-8') . "<br />";
$cumparator=$cumparator . "IBAN: ".htmlspecialchars($company_IBAN, ENT_QUOTES, 'UTF-8') . "<br />";
}
$emailbody="<html>";
$emailbody=$emailbody . "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-condensed-v21-latin-ext_latin-300.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<link href='".$siteCompanyWebsite."/fonts/open-sans-v27-latin-ext_latin-regular.woff' rel='stylesheet' type='text/css'>";
$emailbody=$emailbody . "<style>body {margin-top: 10px; margin-bottom: 10px; margin-left: 10px; margin-right: 10px; font-size: 1.1em; font-family: 'Open Sans',sans-serif; padding: 0px; color: " . $color ."}";
$emailbody=$emailbody . "h1,h2,h3,h4,h5 {font-family:'Open Sans',sans-serif; font-weight: bold; color: " . $color .";}";
$emailbody=$emailbody . "td {font-size: 1em; font-family: 'Open Sans',sans-serif; color: " . $color ."; padding: 3px;  font-weight: normal; border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . "th {font-size: 1.1em; font-family: 'Open Sans',sans-serif; color: #ffffff; background-color: " . $color .";  font-weight: normal;}";
$emailbody=$emailbody . "table {border-collapse:collapse; border: 1px solid " . $color .";}";
$emailbody=$emailbody . ".button {background-color: " . $color . "; border: none; color: white; padding: 15px 32px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer; }";
$emailbody=$emailbody . "</style>";
$emailbody=$emailbody . "</head><body>";
$emailbody=$emailbody . "<a href=\"$siteCompanyWebsite\"><img src=\"".$siteCompanyWebsite."/img/logo.png\" title=\"$strSiteOwner\" width=\"150\" height=\"auto\"/></a>";
$emailbody=$emailbody . "<p>Stimate " .htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'). " ".htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'). ",<br>";
$emailbody=$emailbody . "Acesta este un mesaj de confirmare a înscrierii făcute de dumneavoastră pe site-ul ". $siteCompanyWebsite.". După activarea înscrierii, veți putea modifica informațiile din contul dumneavoastră. </p>";
$emailbody=$emailbody . "<p><strong>Pentru a activa contul, vă rugăm să accesați următorul link:</strong><br />
<a href=\"" . $siteCompanyWebsite . "/account/activate.php?hash=" . $secret . "\" class=\"button\">" . $strClickToActivate . "</a></p>";
$emailbody=$emailbody . "<p>
Vă mulțumim,<br />
<strong>$siteCompanyLegalName</strong><br />
$siteCompanyLegalAddress <br />
$siteCompanyPhones<br />
$siteCompanyEmail<br />
$siteCompanyWebsite <br />
";

$emailbody=$emailbody . "</body>";
$emailbody=$emailbody . "</html>";
$body=$emailbody;
//Create a new PHPMailer instance
$mail = new PHPMailer();
//Set PHPMailer to use the sendmail transport
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
//Enable SMTP debugging
//SMTP::DEBUG_OFF = ON (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server
$mail->Host = $SmtpServer;
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = $SmtpPort;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = $SmtpUser;
//Password to use for SMTP authentication
$mail->Password = $SmtpPass;
//Set who the message is to be sent from
//Set who the message is to be sent from
$mail->setFrom($SmtpUser, $strSiteOwner);
//Set an alternative reply-to address
$mail->addReplyTo($SmtpUser, $strSiteOwner);
//Set who the message is to be sent to
$mail->addAddress($emailto, $emailtoname);
$mail->addAddress($SmtpUser, $strSiteOwner);
//Set the subject line
$mail->Subject = 'Înscriere pe site-ul ' . $siteCompanyWebsite;
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//Attach an image file

//send the message, check for errors
if (!$mail->send()) {
    echo "<div class=\"callout alert\">Mailer Error: " . $mail->ErrorInfo ."</div>";
} else {
    echo "<div class=\"callout success\">".$strMessageSentSuccessfully."</div>";
}

echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";

// Redirect after 1.5 seconds
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"index.php\"
}
setTimeout('delayer()', 1500);
//-->
</script>";

include '../bottom.php';
exit();
}
else {

?>
   

<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('btn1');
  const cuiInput = document.getElementById('Cui');
  const responseEl = document.getElementById('response');
  const loaderIcon = document.getElementById('loaderIcon');

  // Helper: caută o cheie în obiect, indiferent de majuscule, cu aliasuri
  function getKeyInsensitive(obj, keys) {
    if (!obj || typeof obj !== 'object') return '';
    const map = Object.create(null);
    for (const k of Object.keys(obj)) map[k.toLowerCase()] = obj[k];
    for (const key of keys) {
      const v = map[String(key).toLowerCase()];
      if (v !== undefined && v !== null) return v;
    }
    return '';
  }

  // Helper: setează valoarea unui element <input>/<textarea> dacă există
  function setValueById(id, value) {
    const el = document.getElementById(id);
    if (!el) {
      console.warn(`[WARN] Element cu id="#${id}" nu a fost găsit în DOM.`);
      return;
    }
    // Pentru input/textarea
    if ('value' in el) {
      el.value = value;
    } else {
      // fallback pentru elemente non-input
      el.textContent = value;
    }
  }

  // Dacă butonul e într-un <form>, prevenim trimiterea
  btn.addEventListener('click', function (e) {
    e.preventDefault(); // IMPORTANT: oprește submit-ul/navigarea

    const cui = (cuiInput?.value || '').trim();
    if (!cui) {
      alert('Introduceți CUI!');
      return;
    }

    if (loaderIcon) loaderIcon.style.display = ''; // arată loader

    const body = new URLSearchParams({ Cui: cui });

    fetch('../common/cui.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': 'application/json'
      },
      body
    })
    .then(async (r) => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
      // În unele cazuri serverul nu setează corect Content-Type-ul.
      // Încercăm să parsăm JSON, dacă eșuează, afișăm textul primit.
      const text = await r.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error('[ERROR] Răspunsul nu a fost JSON valid. Text primit:', text);
        throw new Error('Răspunsul nu este JSON valid.');
      }

      console.log('[DEBUG] JSON primit:', data);

      try {
        // Extrage câmpuri cu toleranță la capitalizare / aliasuri
        const denumire = String(getKeyInsensitive(data, ['denumire', 'nume', 'client'])).toUpperCase();
        const cif      = String(getKeyInsensitive(data, ['cif', 'cui', 'cod_fiscal']));
        const tva      = String(getKeyInsensitive(data, ['tva', 'ro', 'platitor_tva']));
        const adresa   = String(getKeyInsensitive(data, ['adresa', 'adrese']));
        const judet    = String(getKeyInsensitive(data, ['judet', 'județ'])).toUpperCase();
        const oras     = String(getKeyInsensitive(data, ['oras', 'localitate', 'oraș'])).toUpperCase();
        const rc       = String(getKeyInsensitive(data, ['numar_reg_com', 'nr_rc', 'reg_com']));

        console.log('[DEBUG] Valori mapate:', { denumire, cif, tva, adresa, judet, oras, rc });

        // Setează valorile în câmpuri (asigură-te că aceste ID-uri există în HTML)
        setValueById('factura_client_denumire', denumire);
        setValueById('factura_client_CIF', cif);
        setValueById('factura_client_RO', tva);
        setValueById('factura_client_adresa', adresa);
        setValueById('factura_client_judet', judet);
        setValueById('factura_client_localitate', oras);
        setValueById('factura_client_RC', rc);

      } catch (err) {
        console.error(err);
        if (responseEl) responseEl.textContent = err.message || String(err);
      }
    })
    .catch((err) => {
      console.error(err);
      alert('Some error occurred!');
    })
    .finally(() => {
      if (loaderIcon) loaderIcon.style.display = 'none'; // ascunde loader indiferent de rezultat
    });
  });
});
</script>

        
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('search-box');
    const suggestionBox = document.getElementById('suggesstion-box');
    const responseEl = document.getElementById('response');

    if (!searchBox) return; // asigurare

    // Helper pentru afișarea loader-ului în background (ca în jQuery)
    function showLoader() {
      searchBox.style.background = '#FFF url(../img/LoaderIcon.gif) no-repeat 165px';
    }
    function hideLoader() {
      searchBox.style.background = '#FFF';
    }

    searchBox.addEventListener('keyup', function (e) {
      const keyword = searchBox.value;

      // Construim corpul cererii ca form-url-encoded (compatibil PHP + jQuery)
      const body = new URLSearchParams({ keyword });

      showLoader();

      fetch('../common/city_select.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body
      })
      .then(async (r) => {
        if (!r.ok) {
          throw new Error(`HTTP ${r.status}`);
        }
        const data = await r.text();

        try {
          // Arată și populează sugestiile cu HTML-ul returnat de PHP
          suggestionBox.style.display = '';
          suggestionBox.innerHTML = data;
          hideLoader();
        } catch (err) {
          if (responseEl) {
            responseEl.textContent = err.message;
          } else {
            console.error(err);
          }
          hideLoader();
        }
      })
      .catch((err) => {
        console.error(err);
        alert('Some error occurred!');
        hideLoader();
      });
    });
  });

  // Echivalentul lui selectCity(val) din jQuery → Vanilla
  function selectCity(val) {
    const parts = String(val).split(' - ');
    const city = parts[0] || '';
    const judet = parts[1] || '';

    const searchBox = document.getElementById('search-box');
    const judetInput = document.getElementById('judet');
    const suggestionBox = document.getElementById('suggesstion-box');

    if (searchBox) searchBox.value = city;
    if (judetInput) judetInput.value = judet;
    if (suggestionBox) suggestionBox.style.display = 'none';
  }
</script>

        
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

        <script>
      document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('email');
    const unameResponse = document.getElementById('uname_response');

    // Ascundem mesajul la început (opțional)
    if (unameResponse) unameResponse.style.display = 'none';

    emailInput.addEventListener('change', function () {
        const uname = emailInput.value.trim();

        if (uname !== '') {
            // Arată elementul cu răspuns
            if (!unameResponse) return;
            unameResponse.style.display = '';
            unameResponse.textContent = 'Verific...';
            unameResponse.style.color = '#555';

            // Pregătim corpul cererii ca form-url-encoded (ca în jQuery)
            const body = new URLSearchParams({ uname });

            fetch('../common/checkemail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body
            })
                .then(async (r) => {
                    if (!r.ok) {
                        throw new Error(`HTTP ${r.status}`);
                    }
                    // jQuery compara `response > 0`; presupunem răspuns numeric sau text numeric
                    const text = await r.text();
                    const num = Number(text.trim());

                    if (!Number.isNaN(num) && num > 0) {
                        // Email already exists
                        unameResponse.textContent = 'Acest email este deja înregistrat!';
                        unameResponse.style.color = 'red';
                    } else {
                        // Email is available
                        unameResponse.textContent = 'Email disponibil!';
                        unameResponse.style.color = 'green';
                    }
                })
                .catch((err) => {
                    console.error('Error checking email:', err);
                    unameResponse.textContent = 'Eroare la verificare. Încercați din nou.';
                    unameResponse.style.color = 'red';
                });
        } else {
            // Hide message if input is empty
            if (unameResponse) unameResponse.style.display = 'none';
        }
    });
});
</script>
        <form method="post" id="form1" Action="createaccount.php">

            <div class="grid-x grid-padding-x">
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strFirstName?>
                        <input name="account_first_name" type="text" required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strLastName?>
                        <input name="account_last_name" type="text" required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strPhone?>
                        <input name="account_phone" type="text" required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strAddress?>
                        <input name="account_address" type="text" required />
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCity?>
                        <input type="text" name="account_city" id="search-box" placeholder="<?php echo $strCity?>"
                            required />
                        <div id="suggesstion-box" class="suggesstion-box"></div>
                    </label>
                </div>
                <div class="large-2 medium-2 small-2 cell">
                    <label><?php echo $strCounty?>
                        <input type="text" name="account_county" id="judet" placeholder="<?php echo $strCounty?>"
                            required />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-4 cell">
                    <label><?php echo $strEmail?>
                        <input name="account_email" type="email" id="email" required />
                        <div id="uname_response" class="response"></div>
                    </label>
                </div>
                <div class="large-4 cell">
              <label for="Password"><?php echo $strPassword?></label>
                <input type="password" id="Password" name="Password" autocomplete="new-password" required />
                                    <!-- Indicator rezistență parolă -->
                <div class="pwd-strength" aria-live="polite">
                    <div class="pwd-meter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Rezistență parolă">
                    <div class="pwd-bar"></div>
                </div>
                     <p id="pwdStrengthLabel" class="pwd-label">Introduceți o parolă.</p>
                    <ul class="pwd-hints" id="pwdHints"></ul>
                </div>
                </div>
                <div class="large-4 cell">
                    <label for="ConfirmPassword"><?php echo $strConfirmPassword?></label>
                    <input type="password" id="ConfirmPassword" name="ConfirmPassword" autocomplete="new-password" />
                    <p id="CheckPasswordMatch" aria-live="polite"></p>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-12 cell">

                    <label><input type="radio" name="account_company" class="button1" value="1"
                            onclick='document.getElementById("iframe1").style.display="none";'> Facturare pe persoană
                        fizică</label>
                    <label><input type="radio" name="account_company" class="button1" value="0"
                            onclick='document.getElementById("iframe1").style.display="block";'>Facturare pe persoană
                        juridică</label>
                    <div id="iframe1" style="display:none">

                        <div class="grid-x grid-padding-x">
                            <div class="large-6 cell">
                                <div id="response"></div>
                                <div class="input-group">
                                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                                        placeholder="<?php echo $strEnterVATNumber?>">
                                    <div class="input-group-button">
                                        <button id="btn1" class="button success"><i
                                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid-x grid-padding-x ">
                            <div class="large-4 medium-4 small-4 cell">
                                <label><?php echo $strCompany?>
                                    <input type="text" name="company_name" id="factura_client_denumire" value="" required />
                                </label>
                            </div>
                            <div class="large-1 medium-1 small-1 cell">
                                <label><?php echo $strCompanyFA?>
                                    <input type="text" name="company_ro" id="factura_client_RO" value="" />
                                </label>
                            </div>
                            <div class="large-3 medium-3 small-1 cell">
                                <label><?php echo $strCompanyVAT?>
                                    <input type="text" name="company_VAT" id="factura_client_CIF" value="" required />
                                </label>
                            </div>
                            <div class="large-4 medium-4 cell">
                                <label><?php echo $strCompanyRC?>
                                    <input type="text" name="company_reg" id="factura_client_RC" value="" required />
                                </label>
                            </div>
                        </div>
                        <div class="grid-x grid-padding-x ">
                            <div class="large-4 medium-4 cell">
                                <label><?php echo $strAddress?>
                                    <input type="text" name="company_address" id="factura_client_adresa" value="" required />
                                </label>
                            </div>
                            <div class="large-4 medium-4 cell">
                                <label><?php echo $strCity?>
                                    <input type="text" name="company_city" id="factura_client_localitate" value="" required />
                                </label>
                            </div>
                            <div class="large-4 medium-4 cell">
                                <label><?php echo $strCounty?>
                                    <input type="text" name="company_county" id="factura_client_judet" value="" required />
                                </label>
                            </div>
                        </div>
                        <div class="grid-x grid-padding-x ">
                            <div class="large-6 medium-6 cell">
                                <label><?php echo $strBank?>
                                    <input type="text" name="company_bank" id="company_bank" value="" />
                                </label>
                            </div>
                            <div class="large-6 medium-6 cell">
                                <label><?php echo $strCompanyIBAN?>
                                    <input type="text" name="company_IBAN" id="company_IBAN" value="" />
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="grid-x grid-margin-x">
                        <div class="large-12 medium-12 small-12 cell">

                            Am citit şi sunt de acord cu <a href="termeni.php"
                                title="Termeni şi condiţii de utilizare">Termenii şi condiţiile de utilizare</a>
                            <input type="checkbox" id="strAcord"
                                onclick="javascript:document.getElementById('btn_submit').style.display='block'; javascript:document.getElementById('strAcord').style.display='none';" />
                            <p align="center"><input type="submit" class="button" id="btn_submit" style="display:none;"
                                    value="Trimite" /></p>
                        </div>
                    </div>
        </form>
    </div>
</div>
<?php
}
 }
include '../bottom.php';
?>