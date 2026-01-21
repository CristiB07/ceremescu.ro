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
            $clientSql = "SELECT DISTINCT clienti_date.ID_Client, clienti_abonamente.abonament_client_ID, abonament_client_aloc, Client_Denumire, Client_CUI, Client_Localitate, Client_Judet FROM clienti_date, clienti_abonamente 
                          WHERE abonament_client_aloc='$code' 
                          AND  clienti_date.ID_Client=clienti_abonamente.abonament_client_ID OR  clienti_date.Client_HQ=clienti_abonamente.abonament_client_ID 
                          AND abonament_client_activ=0
                          ORDER BY Client_Denumire ASC";

            $clientRes = ezpub_query($conn, $clientSql);
            while ($clientRow = ezpub_fetch_array($clientRes)) {
                echo '<option value="' . htmlspecialchars($clientRow['ID_Client']) . '">' . htmlspecialchars($clientRow['Client_Denumire']) . '</option>';
            }
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

// Completează selecturile pe baza codului introdus manual
function populateFromManual() {
    const code = document.getElementById('cod_deseu').value;
    if (code.length >= 6) {
        document.getElementById('cd_01').value = code.substring(0,2);
        populateCd02();
        document.getElementById('cd_02').value = code.substring(2,4);
        populateCd03();
        document.getElementById('cd_03').value = code.substring(4,6);
    }
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