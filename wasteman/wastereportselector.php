<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Raportare deșeuri";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$coduri = [];
$sql = "SELECT cd_id, cd_01, cd_02, cd_03, cd_description FROM deseuri_coduri";
$res = ezpub_query($conn, $sql);
while ($row = ezpub_fetch_array($res)) {
    $coduri[] = $row;
}
?>
<script>
const coduri = <?php echo json_encode($coduri); ?>;
</script>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h2>Raportare coduri de deșeuri</h2>
<form id="wasteForm" onsubmit="return goToReporting();">
    <div class="grid-x grid-margin-x">
        <div class="large-2 medium-2 small-3 cell">
    <label>Cod complet de deșeu:
        <input type="text" id="cod_deseu" name="cod_deseu" oninput="populateFromManual()" class="required" />
    </label>
    <input type="hidden" id="cod_id" name="cod_id" />
    <div style="font-size:0.95em;color:#666;margin-bottom:8px;">Introduceți manual codul complet sau selectați din opțiunile de mai jos.</div>
    <div id="cod_error" class="callout alert" style="display:none;margin-top:8px;padding:6px 10px;font-size:0.95em;"></div>
</div>
<div class="large-2 medium-2 small-3 cell">
    <label>Categorie:
        <select id="cd_01" onchange="populateCd02(); updateFinalCode();">
            <option value="">Alege...</option>
        </select>
    </label>
</div>
<div class="large-2 medium-2 small-3 cell">
    <label>Subcategorie:
        <select id="cd_02" onchange="populateCd03(); updateFinalCode();">
            <option value="">Alege...</option>
        </select>
    </label>
</div>
<div class="large-2 medium-2 small-3 cell">
    <label>Tip deșeu:
        <select id="cd_03" onchange="updateFinalCode();">
            <option value="">Alege...</option>
        </select>
    </label>
</div>
        <div class="large-2 medium-2 small-6 cell">
    <label>Selectează client
        <select id="client_id" name="client_id" class="required">
            <option value="">Alege...</option>
            <?php
            // Use a prepared statement and explicit JOIN. Ensure aloc and activ filters apply to the abonament row
            $clientSql = "SELECT DISTINCT c.ID_Client, a.abonament_client_ID, a.abonament_client_aloc, c.Client_Denumire, c.Client_CUI, c.Client_Localitate, c.Client_Judet
                          FROM clienti_date c
                          JOIN clienti_abonamente a ON (c.ID_Client = a.abonament_client_ID OR c.Client_HQ = a.abonament_client_ID)
                          WHERE a.abonament_client_aloc = ? AND a.abonament_client_activ = 0
                          ORDER BY c.Client_Denumire ASC";

            $stmt = mysqli_prepare($conn, $clientSql);
            mysqli_stmt_bind_param($stmt, "s", $code);

           mysqli_stmt_execute($stmt);
            $clientRes = mysqli_stmt_get_result($stmt);
            while ($clientRow = mysqli_fetch_assoc($clientRes)) {
                echo '<option value="' . htmlspecialchars($clientRow['ID_Client'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($clientRow['Client_Denumire'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
            mysqli_stmt_close($stmt);
            ?>
        </select>
    </label>
</div>
        <div class="large-2 medium-2 small-12 cell">
    <label>Anul raportare:
        <select id="an_raportare" name="an_raportare" class="required">
            <?php
            $currentYear = date("Y");
            for ($year = $currentYear; $year >= 2020; $year--) {
                echo '<option value="' . $year . '">' . $year . '</option>';
            }
            ?>
        </select>
    </label>
    <button type="submit" class="button success" style="margin-top:10px;">Trimite raportare</button>
</form>
</div>
</div>
<div id="finalCode"></div>

<script>

window.onload = function() {
    populateCd01();
};

function populateCd01() {
    const cd01Map = new Map();
    coduri.forEach(row => {
        if (row.cd_01 && (!row.cd_02 || row.cd_02 === '') && (!row.cd_03 || row.cd_03 === '')) {
            cd01Map.set(row.cd_01, row.cd_description || row.cd_01);
        }
    });
    const cd01 = document.getElementById('cd_01');
    cd01.innerHTML = '<option value="">Alege...</option>';
    cd01Map.forEach((desc, val) => {
        cd01.innerHTML += `<option value="${val}">${desc}</option>`;
    });
    document.getElementById('cd_02').innerHTML = '<option value="">Alege...</option>';
    document.getElementById('cd_03').innerHTML = '<option value="">Alege...</option>';
    updateFinalCode();
}

function populateCd02() {
    const sel01 = document.getElementById('cd_01').value;
    const cd02Map = new Map();
    coduri.forEach(row => {
        if (row.cd_01 === sel01 && row.cd_02 && row.cd_02 !== '' && (!row.cd_03 || row.cd_03 === '')) {
            cd02Map.set(row.cd_02, row.cd_description || row.cd_02);
        }
    });
    const cd02 = document.getElementById('cd_02');
    cd02.innerHTML = '<option value="">Alege...</option>';
    cd02Map.forEach((desc, val) => {
        cd02.innerHTML += `<option value="${val}">${desc}</option>`;
    });
    document.getElementById('cd_03').innerHTML = '<option value="">Alege...</option>';
    updateFinalCode();
}

function populateCd03() {
    const sel01 = document.getElementById('cd_01').value;
    const sel02 = document.getElementById('cd_02').value;
    const cd03 = document.getElementById('cd_03');
    cd03.innerHTML = '<option value="">Alege...</option>';
    coduri.forEach(row => {
        if (row.cd_01 === sel01 && row.cd_02 === sel02 && row.cd_03 && row.cd_03 !== '') {
            cd03.innerHTML += `<option value="${row.cd_03}">${row.cd_description || row.cd_03}</option>`;
        }
    });
    updateFinalCode();
}

// Helper: find a row in coduri matching predicate
function findCoduriRow(predicate) {
    for (let i = 0; i < coduri.length; i++) {
        const r = coduri[i];
        if (predicate(r)) return r;
    }
    return null;
}

function optionExists(selectId, value) {
    const sel = document.getElementById(selectId);
    if (!sel) return false;
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === value) return true;
    }
    return false;
}

function ensureOption(selectId, value, label) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    if (!optionExists(selectId, value)) {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label || value;
        sel.appendChild(opt);
    }
}

function showCodError(msg) {
    const el = document.getElementById('cod_error');
    if (!el) return;
    el.style.display = 'block';
    el.textContent = msg;
    document.getElementById('cod_deseu').classList.add('is-invalid-input');
}
function clearCodError() {
    const el = document.getElementById('cod_error');
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    document.getElementById('cod_deseu').classList.remove('is-invalid-input');
}

// Completează selecturile pe baza codului introdus manual (reverse selection)
function populateFromManual() {
    clearCodError();
    let raw = document.getElementById('cod_deseu').value.trim();
    if (raw === '') {
        // clear selects
        document.getElementById('cd_01').value = '';
        document.getElementById('cd_02').innerHTML = '<option value="">Alege...</option>';
        document.getElementById('cd_03').innerHTML = '<option value="">Alege...</option>';
        updateFinalCode();
        return;
    }

    // Accept digits only for code parsing
    const code = raw.replace(/\D/g, '');
    if (code.length !== raw.length) {
        // allow users to type spaces or separators but validate actual digits count
        // we won't reject immediately; normalize and continue
    }

    if (![2,4,6].includes(code.length)) {
        showCodError('Codul trebuie să aibă 2, 4 sau 6 cifre (ex: 15, 1501, 150101).');
        return;
    }

    // handle cd_01 (first two digits)
    const c1 = code.substring(0,2);
    const row1 = findCoduriRow(r => r.cd_01 === c1);
    if (!row1) {
        showCodError('Cod categorie invalid: ' + c1);
        return;
    }
    // ensure option exists for cd_01 and set it
    ensureOption('cd_01', c1, row1.cd_description || c1);
    document.getElementById('cd_01').value = c1;
    populateCd02();

    if (code.length >= 4) {
        const c2 = code.substring(2,4);
        const row2 = findCoduriRow(r => r.cd_01 === c1 && r.cd_02 === c2);
        if (!row2) {
            showCodError('Cod subcategorie invalid pentru ' + c1 + ': ' + c2);
            return;
        }
        // ensure option exists and set
        ensureOption('cd_02', c2, row2.cd_description || c2);
        document.getElementById('cd_02').value = c2;
        populateCd03();
    } else {
        document.getElementById('cd_02').value = '';
        document.getElementById('cd_03').innerHTML = '<option value="">Alege...</option>';
    }

    if (code.length === 6) {
        const c3 = code.substring(4,6);
        const row3 = findCoduriRow(r => r.cd_01 === c1 && r.cd_02 === document.getElementById('cd_02').value && r.cd_03 === c3);
        if (!row3) {
            showCodError('Cod tip deșeu invalid pentru ' + c1 + document.getElementById('cd_02').value + ': ' + c3);
            return;
        }
        ensureOption('cd_03', c3, row3.cd_description || c3);
        document.getElementById('cd_03').value = c3;
    } else {
        document.getElementById('cd_03').value = '';
    }

    // success: sync final code and cod_id
    clearCodError();
    updateFinalCode();
}

function updateFinalCode() {
    const code = document.getElementById('cd_01').value +
                 document.getElementById('cd_02').value +
                 document.getElementById('cd_03').value;
    var codFinalEl = document.getElementById('cod_final');
    if (codFinalEl) codFinalEl.value = code;
    var codDeseuEl = document.getElementById('cod_deseu');
    if (codDeseuEl) codDeseuEl.value = code;
    setCodId(code);
}

function setCodId(code) {
    let foundId = '';
    coduri.forEach(row => {
        if ((row.cd_01 + row.cd_02 + row.cd_03) === code) {
            foundId = row.cd_id ? row.cd_id : '';
        }
    });
    document.getElementById('cod_id').value = foundId;
}

function goToReporting() {
    const wID = document.getElementById('cod_deseu').value;
    const client = document.getElementById('client_id').value;
    const year = document.getElementById('an_raportare').value;
    const codId = document.getElementById('cod_id').value;
    // block submit if there is a validation error shown
    var codErr = document.getElementById('cod_error');
    if (codErr && codErr.style.display !== 'none') {
        alert('Codul introdus este invalid. Corectați înainte de a trimite.');
        document.getElementById('cod_deseu').focus();
        return false;
    }
    if (!wID || !client || !year) {
        alert('Completați toate câmpurile!');
        return false;
    }
    // Trimite către wastereporting.php cu parametri querystring, inclusiv mode=fill
    window.location.href = `wastereporting.php?mode=fill&wID=${encodeURIComponent(wID)}&client=${encodeURIComponent(client)}&year=${encodeURIComponent(year)}&cod_id=${encodeURIComponent(codId)}`;
    return false;
}
</script>
</div>
</div>
<?php
include '../bottom.php';
?>