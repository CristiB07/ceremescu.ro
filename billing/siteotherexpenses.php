<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare alte cheltuieli";

if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes")
{
    
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

require_once(__DIR__ . '/../classes/paginator.class.php');


// Handle delete
if ((isset($_GET['mode']) && $_GET['mode']=='delete') && isset($_GET['cID'])){
    $cID = (int)$_GET['cID'];
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_alte_cheltuieli WHERE cheltuiala_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $cID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: '.$_SERVER['PHP_SELF'].'');
    exit;
}

// Handle save (add/edit)
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save_other'])){
    // If user selected recurrent items (multiple), insert them all for current month
    if (!empty($_POST['recur_select']) && is_array($_POST['recur_select'])){
        foreach ($_POST['recur_select'] as $recid){
            $recid = (int)$recid;
            $rrs = ezpub_query($conn, "SELECT * FROM facturare_alte_cheltuieli_recurente WHERE cheltuiala_id='$recid'");
            $rr = ezpub_fetch_array($rrs);
            if (!$rr) continue;
            $origDay = (int)date('j', strtotime($rr['cheltuiala_termen']));
            $year = date('Y');
            $month = date('m');
            $lastDay = date('t', strtotime("$year-$month-01"));
            $desiredDay = min($origDay, (int)$lastDay);
            $desiredDate = date('Y-m-d', strtotime("$year-$month-$desiredDay"));
            $desiredDateTime = $desiredDate . ' 00:00:00';
            $suma = floatval($rr['cheltuiala_suma']);
            $obs = $rr['cheltuiala_observatii'];
            $achitat = 0;
            $stmt = mysqli_prepare($conn, "INSERT INTO facturare_alte_cheltuieli (cheltuiala_termen, cheltuiala_suma, cheltuiala_observatii, cheltuiala_achitat) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sdsi', $desiredDateTime, $suma, $obs, $achitat);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        header('Location: '.$_SERVER['PHP_SELF'].'');
        exit;
    }
    // If this is an edit, preload previous achitat and suma to avoid parsing/overwrite issues
    $existing_achitat = null;
    $existing_suma_db = null;
    if (!empty($_POST['cheltuiala_id'])){
        $checkid = (int)$_POST['cheltuiala_id'];
        $rr = ezpub_query($conn, "SELECT cheltuiala_achitat, cheltuiala_suma FROM facturare_alte_cheltuieli WHERE cheltuiala_id='$checkid'");
        $rrow = ezpub_fetch_array($rr);
        if ($rrow) {
            if (isset($rrow['cheltuiala_achitat'])) $existing_achitat = (int)$rrow['cheltuiala_achitat'];
            if (isset($rrow['cheltuiala_suma'])) $existing_suma_db = (float)$rrow['cheltuiala_suma'];
        }
    }
    // The JS on submit normalizes the value to English decimal format (e.g. '26307.00')
    // so we use floatval() directly — no Romanian parsing needed here
    $raw_suma = trim((string)($_POST['cheltuiala_suma'] ?? ''));
    // fallback: if somehow still contains comma (Romanian), convert manually
    $raw_suma_norm = str_replace(',', '.', str_replace('.', '', $raw_suma));
    if (strpos($raw_suma, ',') !== false) {
        // Romanian format: has comma → remove dots (thousands), comma to dot
        $raw_suma_norm = str_replace(',', '.', str_replace('.', '', $raw_suma));
    } else {
        // English format from JS: use as-is
        $raw_suma_norm = $raw_suma;
    }
    $suma = is_numeric($raw_suma_norm) ? (float)$raw_suma_norm : 0.0;
    // If editing and parsed is zero but DB has a value, prefer DB (parse failure)
    if ($existing_suma_db !== null && $suma == 0.0 && $existing_suma_db != 0.0) {
        error_log("[siteotherexpenses] parsed suma is 0 but DB has {$existing_suma_db}, using DB value");
        $suma = $existing_suma_db;
    }
    if (!is_finite($suma)) {
        $suma = 0.0;
    }
    $suma_str = number_format((float)$suma, 4, '.', '');
    // Termen is date only (no time)
    $termen = trim($_POST['cheltuiala_termen']) ? date('Y-m-d 00:00:00', strtotime($_POST['cheltuiala_termen'])) : date('Y-m-d 00:00:00');
    $obs = trim($_POST['cheltuiala_observatii']);
    $achitat = isset($_POST['cheltuiala_achitat']) ? 1 : 0;
    // Bank selection from form (optional)
    $banca = isset($_POST['cheltuiala_banca']) ? trim($_POST['cheltuiala_banca']) : '';
    $bank_map = array('ING' => 'cash_banca_ING', 'transilvania' => 'cash_banca_transilvania', 'trezorerie' => 'cash_banca_trezorerie');
    // If marked paid now, and previously not paid (or new record), subtract from selected bank
    if ($achitat==1 && ($existing_achitat === null || $existing_achitat==0) && $banca!='' && isset($bank_map[$banca])){
        $col = $bank_map[$banca];
        $amount_str = $suma_str;
        // Wrap in try/catch: if cash_banca column type can't hold result (needs ALTER TABLE),
        // log and skip rather than crashing — the expense record will still be saved
        try {
            $sqlupd = "UPDATE cash_banca SET $col = COALESCE($col,0) - ?";
            $stmtb = mysqli_prepare($conn, $sqlupd);
            if ($stmtb){
                mysqli_stmt_bind_param($stmtb, 's', $amount_str);
                if (!mysqli_stmt_execute($stmtb)) {
                    error_log("[siteotherexpenses] cash_banca update failed: " . mysqli_stmt_error($stmtb) . " (col={$col}, amount={$amount_str})");
                }
                mysqli_stmt_close($stmtb);
            }
        } catch (Exception $e) {
            error_log("[siteotherexpenses] cash_banca update exception: " . $e->getMessage() . " — run ALTER TABLE to fix column type");
        }
    }
    if (!empty($_POST['cheltuiala_id'])){
        $id = (int)$_POST['cheltuiala_id'];
        $stmt = mysqli_prepare($conn, "UPDATE facturare_alte_cheltuieli SET cheltuiala_termen=?, cheltuiala_suma=?, cheltuiala_observatii=?, cheltuiala_achitat=? WHERE cheltuiala_id=?");
        mysqli_stmt_bind_param($stmt, 'sssii', $termen, $suma_str, $obs, $achitat, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO facturare_alte_cheltuieli (cheltuiala_termen, cheltuiala_suma, cheltuiala_observatii, cheltuiala_achitat) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sssi', $termen, $suma_str, $obs, $achitat);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: '.$_SERVER['PHP_SELF'].'');
    exit;
}

// Decide whether to show form or listing
$show_form = false;
if (isset($_GET['mode']) && ($_GET['mode']=='edit' || $_GET['mode']=='new')) {
    $show_form = true;
}

// Include header AFTER any possible redirects (delete/save)
include '../dashboard/header.php';

if (!$show_form) {
    // Listing with paginator
    $pages = new Pagination();
    $countres = ezpub_query($conn, "SELECT COUNT(*) AS total FROM facturare_alte_cheltuieli");
    $crow = ezpub_fetch_array($countres);
    $pages->items_total = (int)$crow['total'];
    $pages->paginate();

    $query = "SELECT * FROM facturare_alte_cheltuieli ORDER BY cheltuiala_termen DESC" . $pages->limit;
    $result = ezpub_query($conn, $query);

    ?>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h3>Alte cheltuieli</h3>
            <div style="margin-bottom:10px;">
                <a href="?mode=new" class="button">Adaugă cheltuială</a>
            </div>
            <table class="unstriped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Suma</th>
                        <th>Observații</th>
                        <th>Achitat</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (ezpub_num_rows($result,$query)==0) { ?>
                        <tr><td colspan="6">Nu există înregistrări</td></tr>
                    <?php } else {
                        while ($row = ezpub_fetch_array($result)){
                            echo '<tr>';
                            echo '<td>'.(int)$row['cheltuiala_id'].'</td>';
                            echo '<td>'.date('d.m.Y H:i', strtotime($row['cheltuiala_termen'])).'</td>';
                            echo '<td align="right">'.romanize($row['cheltuiala_suma']).'</td>';
                            echo '<td>'.htmlspecialchars($row['cheltuiala_observatii'], ENT_QUOTES, 'UTF-8').'</td>';
                            echo '<td>'.($row['cheltuiala_achitat']?'<span class="paid">Da</span>':'<span class="notpaid">Nu</span>').'</td>';
                            echo '<td><a href="?mode=edit&cID='.(int)$row['cheltuiala_id'].'" title="Editează"><i class="fas fa-edit"></i></a> &nbsp; <a href="?mode=delete&cID='.(int)$row['cheltuiala_id'].'" onclick="return confirm(\'Șterge înregistrarea?\')" title="Șterge"><i class="fas fa-trash"></i></a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div style="margin-top:10px">
                <?php echo $pages->display_pages(); ?>
            </div>
        </div>
    </div>
    <?php
} else {
    // Prepare edit data if editing
    $editing = false;
    $editdata = null;
    if (isset($_GET['mode']) && $_GET['mode']=='edit' && isset($_GET['cID'])){
        $id = (int)$_GET['cID'];
        $r = ezpub_query($conn, "SELECT * FROM facturare_alte_cheltuieli WHERE cheltuiala_id='$id'");
        $editdata = ezpub_fetch_array($r);
        $editing = true;
    }
    ?>
    <div class="grid-x grid-margin-x" style="margin-top:20px;">
        <div class="large-12 cell">
            <h4><?php echo $editing? 'Modifică cheltuială':'Adaugă cheltuială'?></h4>
            <div id="other-expense-error" class="callout alert" style="display:none;margin-bottom:10px;"></div>

            <?php
            // Load recurrent expenses for quick prefill (will be rendered inside form)
            $recres = ezpub_query($conn, "SELECT * FROM facturare_alte_cheltuieli_recurente ORDER BY cheltuiala_termen ASC");
            $hasRec = ezpub_num_rows($recres, "SELECT * FROM facturare_alte_cheltuieli_recurente ORDER BY cheltuiala_termen ASC");
            ?>

            <form id="other-expense-form" method="post" action="">
                <?php if ($hasRec) {
                    // render select inside form so values are posted
                    echo '<div style="margin-bottom:10px">';
                    echo '<label>Preia din cheltuieli recurente (select multi):<br />';
                    echo '<select id="recur_select" name="recur_select[]" multiple size="6">';
                    // reset pointer and re-run query
                    $recres = ezpub_query($conn, "SELECT * FROM facturare_alte_cheltuieli_recurente ORDER BY cheltuiala_termen ASC");
                    while ($rr = ezpub_fetch_array($recres)){
                        $day = (int)date('j', strtotime($rr['cheltuiala_termen']));
                        $suma_formatted = number_format($rr['cheltuiala_suma'], 2, ',', '.');
                        $obs = htmlspecialchars($rr['cheltuiala_observatii'], ENT_QUOTES, 'UTF-8');
                        $label = date('d.m', strtotime($rr['cheltuiala_termen'])) . ' - ' . $suma_formatted . ' - ' . $obs;
                        echo '<option value="'.(int)$rr['cheltuiala_id'].'" data-day="'. $day .'" data-suma="'. $suma_formatted .'" data-obs="'. $obs .'">'. $label .'</option>';
                    }
                    echo '</select></label></div>';
                } ?>
                <input type="hidden" name="cheltuiala_id" value="<?php echo $editing? (int)$editdata['cheltuiala_id'] : ''?>" />
                <div class="grid-x grid-padding-x">
                        <div class="large-3 cell">
                            <label>Data (termen)
                                <input type="date" name="cheltuiala_termen" value="<?php echo $editing? date('Y-m-d', strtotime($editdata['cheltuiala_termen'])): date('Y-m-d')?>" />
                            </label>
                        </div>
                    <div class="large-3 cell">
                        <label>Suma
                            <input type="text" name="cheltuiala_suma" value="<?php echo $editing? romanize($editdata['cheltuiala_suma']): ''?>" />
                        </label>
                    </div>
                    <div class="large-4 cell">
                        <label>Observații
                            <input type="text" name="cheltuiala_observatii" value="<?php echo $editing? htmlspecialchars($editdata['cheltuiala_observatii'], ENT_QUOTES, 'UTF-8'): ''?>" />
                        </label>
                    </div>
                                    <div class="large-2 cell">
                                        <label>Banca
                                            <select name="cheltuiala_banca">
                                                <option value="">-- Nicio selectare --</option>
                                                <option value="ING" <?php echo ($editing && isset($editdata['cheltuiala_banca']) && $editdata['cheltuiala_banca']=='ING')? 'selected':''?>>ING</option>
                                                <option value="transilvania" <?php echo ($editing && isset($editdata['cheltuiala_banca']) && $editdata['cheltuiala_banca']=='transilvania')? 'selected':''?>>Banca Transilvania</option>
                                                <option value="trezorerie" <?php echo ($editing && isset($editdata['cheltuiala_banca']) && $editdata['cheltuiala_banca']=='trezorerie')? 'selected':''?>>Trezorerie</option>
                                            </select>
                                        </label>
                                    </div>
                                    <div class="large-2 cell">
                                        <label>&nbsp;<br />
                                            <input type="checkbox" name="cheltuiala_achitat" <?php echo ($editing && $editdata['cheltuiala_achitat'])? 'checked' : ''?> /> Achitat
                                        </label>
                                    </div>
                </div>
                <div style="margin-top:10px">
                    <input type="submit" name="save_other" value="Salvează" class="button" />
                    <a href="?" class="button secondary">Anulează</a>
                </div>
                <div id="recur_preview" style="margin-top:20px; display:none;">
                    <h5>Previzualizare intrări recurente</h5>
                    <div id="recur_preview_content"></div>
                </div>
            </form>
        </div>
    </div>
    <?php
}


include(__DIR__.'/../bottom.php');
?>

<script>
(function(){
    var form = document.getElementById('other-expense-form');
    if(!form) return;
    var err = document.getElementById('other-expense-error');

    form.addEventListener('submit', function(e){
        if(err){ err.style.display='none'; err.innerHTML=''; }
        var sumaEl = form.querySelector('[name="cheltuiala_suma"]');
        var dateEl = form.querySelector('[name="cheltuiala_termen"]');
        var raw = sumaEl.value.trim();
        // Normalize: remove thousands separator '.' and spaces, convert comma to dot
        var norm = raw.replace(/\./g,'').replace(/\s+/g,'').replace(/,/g,'.');
        if(norm === '' || isNaN(norm)){
            if(err){ err.innerHTML = 'Introduceți o sumă validă.'; err.style.display='block'; }
            e.preventDefault();
            return false;
        }
        sumaEl.value = norm;
        if(!dateEl.value){
            if(err){ err.innerHTML = 'Introduceți data termen.'; err.style.display='block'; }
            e.preventDefault();
            return false;
        }
        return true;
    }, false);

    // recurrent select populate
    var sel = document.getElementById('recur_select');
    if(!sel) return;
    sel.addEventListener('change', function(){
        var selected = Array.from(sel.selectedOptions || []).filter(function(o){ return o.value; });
        var dateEl = document.querySelector('[name="cheltuiala_termen"]');
        var sumaEl = document.querySelector('[name="cheltuiala_suma"]');
        var obsEl = document.querySelector('[name="cheltuiala_observatii"]');
        var preview = document.getElementById('recur_preview');
        var previewContent = document.getElementById('recur_preview_content');
        if(selected.length === 0){
            if(preview) preview.style.display='none';
            if(dateEl) dateEl.value = '';
            if(sumaEl) sumaEl.value = '';
            if(obsEl) obsEl.value = '';
            return;
        }

        // Build preview rows for all selected
        var rows = [];
        var today = new Date();
        var year = today.getFullYear();
        var month = today.getMonth(); // 0-based
        function pad(n){ return n<10? '0'+n : ''+n; }

        selected.forEach(function(opt, idx){
            var day = parseInt(opt.dataset.day,10) || 1;
            var suma = opt.dataset.suma || '';
            var obs = opt.dataset.obs || '';
            var lastDay = new Date(year, month+1, 0).getDate();
            var desiredDay = Math.min(day, lastDay);
            var desired = new Date(year, month, desiredDay);
            var displayDate = pad(desired.getDate()) + '.' + pad(desired.getMonth()+1) + '.' + desired.getFullYear();
            rows.push({date: displayDate, suma: suma, obs: obs});
        });

        // If single selected, also populate form fields
        if(selected.length === 1){
            var opt = selected[0];
            var day = parseInt(opt.dataset.day,10);
            var lastDay = new Date(year, month+1, 0).getDate();
            var desiredDay = Math.min(day, lastDay);
            var desired = new Date(year, month, desiredDay);
            var dateStr = desired.getFullYear() + '-' + pad(desired.getMonth()+1) + '-' + pad(desired.getDate());
            if(dateEl) dateEl.value = dateStr;
            if(sumaEl) sumaEl.value = opt.dataset.suma || '';
            if(obsEl) obsEl.value = opt.dataset.obs || '';
        } else {
            if(dateEl) dateEl.value = '';
            if(sumaEl) sumaEl.value = '';
            if(obsEl) obsEl.value = '';
        }

        // Render preview table
        if(previewContent){
            var html = '<table class="stack unstriped"><thead><tr><th>Data</th><th>Suma</th><th>Observații</th></tr></thead><tbody>';
            rows.forEach(function(r){
                html += '<tr><td>'+ r.date +'</td><td style="text-align:right">'+ r.suma +'</td><td>'+ r.obs +'</td></tr>';
            });
            html += '</tbody></table>';
            previewContent.innerHTML = html;
            if(preview) preview.style.display = 'block';
        }
    }, false);

})();
</script>
