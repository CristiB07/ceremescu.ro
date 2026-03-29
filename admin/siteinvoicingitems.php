<?php
// admin page for managing nomenclature articles
// update 23.02.2026
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
$strPageTitle = "Administrare articole nomenclator";
include '../dashboard/header.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin'])) {
    header("location:$strSiteURL/login/index.php?message=MLF");
    exit();
}
// only ADMIN users may access
if (!isset($_SESSION['clearence']) || $_SESSION['clearence'] != 'ADMIN') {
    header("location:$strSiteURL/index.php?message=unauthorized");
    exit();
}
// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "<h1>$strPageTitle</h1>";

// handle delete
if (isset($_GET['mode']) && $_GET['mode'] == 'delete') {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }
    if (!isset($_GET['aID']) || !filter_var($_GET['aID'], FILTER_VALIDATE_INT)) {
        die('Invalid ID');
    }
    $aID = (int)$_GET['aID'];
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_nomenclator WHERE articol_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $aID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<div class="callout success">' . $strRecordDeleted . '</div>';
    echo "<script>setTimeout(function(){ window.location='siteinvoicingitems.php'; },1500);</script>";
    include '../bottom.php';
    exit();
}

// insert / update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    check_inject();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('<div class="callout alert">Invalid CSRF token</div>');
    }

    // collect values
    $denumire = $_POST['articol_denumire'] ?? '';
    $detaliu = $_POST['articol_detaliu'] ?? '';
    $codcpv = $_POST['articol_codcpv'] ?? '';
    $codnc8 = $_POST['articol_codnc8'] ?? '';
    $codean = $_POST['articol_codean'] ?? '';
    $procent_tva_raw = $_POST['articol_procent_tva'] ?? '0';
    $pret_raw = $_POST['articol_pret'] ?? '0';

    // normalize numbers
    $procent_tva = parseRomanianNumber($procent_tva_raw);
    $pret = parseRomanianNumber($pret_raw);

    if (!is_numeric($procent_tva) || !is_numeric($pret)) {
        die('Invalid numeric values');
    }

    $procent_tva = (int)$procent_tva;
    $pret = (float)$pret;

    if (isset($_GET['mode']) && $_GET['mode'] == 'new') {
        $stmt = mysqli_prepare($conn, "INSERT INTO facturare_nomenclator 
            (articol_denumire, articol_detaliu, articol_codcpv, articol_codnc8,
             articol_codean, articol_procent_tva, articol_pret) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssid',
            $denumire, $detaliu, $codcpv, $codnc8, $codean, $procent_tva, $pret);
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">' . $strRecordAdded . '</div>';
        echo "<script type=\"text/javascript\">\n" .
             "function delayer(){ window.location = 'siteinvoicingitems.php'; }\n" .
             "</script>\n<body onLoad=\"setTimeout('delayer()',1500)\">";
        include '../bottom.php';
        exit();
    } elseif (isset($_GET['mode']) && $_GET['mode'] == 'edit') {
        if (!isset($_GET['aID']) || !filter_var($_GET['aID'], FILTER_VALIDATE_INT)) {
            die('Invalid ID');
        }
        $aID = (int)$_GET['aID'];
        $stmt = mysqli_prepare($conn, "UPDATE facturare_nomenclator SET 
            articol_denumire=?, articol_detaliu=?, articol_codcpv=?, articol_codnc8=?,
            articol_codean=?, articol_procent_tva=?, articol_pret=? 
            WHERE articol_id=?");
        mysqli_stmt_bind_param($stmt, 'sssssidi',
            $denumire, $detaliu, $codcpv, $codnc8, $codean, $procent_tva, $pret, $aID);
        if (!mysqli_stmt_execute($stmt)) {
            die('Error: ' . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">' . $strRecordModified . '</div>';
        echo "<script type=\"text/javascript\">\n" .
             "function delayer(){ window.location = 'siteinvoicingitems.php'; }\n" .
             "</script>\n<body onLoad=\"setTimeout('delayer()',1500)\">";
        include '../bottom.php';
        exit();
    }
}

// after processing, always show the list and form

// --- pagination & fetch existing records ---
// count total
$query = "SELECT COUNT(*) AS cnt FROM facturare_nomenclator";
$res_count = ezpub_query($conn, $query);
$rowcnt = ezpub_fetch_array($res_count);
$numar = (int)$rowcnt['cnt'];

$pages = new Pagination;
$pages->items_total = $numar;
$pages->mid_range = 5;
$pages->paginate();

$query2 = "SELECT * FROM facturare_nomenclator ORDER BY articol_denumire ASC " . $pages->limit;
$result = ezpub_query($conn, $query2);

?>

<script>
// helpers for cpv/nc8 search
function setupSearch(inputId, suggestionId, endpoint, selectFuncName) {
    document.addEventListener('DOMContentLoaded', function() {
        const searchBox = document.getElementById(inputId);
        const suggestionBox = document.getElementById(suggestionId);
        if (!searchBox) return;

        function showLoader() {
            searchBox.style.background = '#FFF url(../img/LoaderIcon.gif) no-repeat 165px';
        }
        function hideLoader() {
            searchBox.style.background = '#FFF';
        }

        searchBox.addEventListener('keyup', function() {
            const keyword = searchBox.value;
            if (!suggestionBox) return;
            showLoader();
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'keyword=' + encodeURIComponent(keyword)
            })
            .then(r => r.text())
            .then(data => {
                suggestionBox.style.display = '';
                suggestionBox.innerHTML = data;
                hideLoader();
            })
            .catch(err => {
                console.error(err);
                hideLoader();
            });
        });
    });
}

function selectCPV(val) {
    const parts = String(val).split(' - ');
    const code = parts[0]||'';
    const descr = parts[1]||'';
    const search = document.getElementById('cpv-search');
    const desc = document.getElementById('cpv-description');
    const box = document.getElementById('cpv-suggestion-box');
    if (search) search.value = code;
    if (desc) desc.value = descr;
    if (box) box.style.display = 'none';
}
function selectNC8(val) {
    const parts = String(val).split(' - ');
    const code = parts[0]||'';
    const descr = parts[1]||'';
    const search = document.getElementById('nc8-search');
    const desc = document.getElementById('nc8-description');
    const box = document.getElementById('nc8-suggestion-box');
    if (search) search.value = code;
    if (desc) desc.value = descr;
    if (box) box.style.display = 'none';
}

setupSearch('cpv-search','cpv-suggestion-box','../common/cpv_search.php','selectCPV');
setupSearch('nc8-search','nc8-suggestion-box','../common/nc8_search.php','selectNC8');
</script>

<?php if (!isset($_GET['mode']) || !in_array($_GET['mode'], ['new','edit'])) { ?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h2>Lista articole</h2>
        <?php
        // add button when listing
        echo '<p><a href="siteinvoicingitems.php?mode=new" class="button">' . $strAdd . ' <i class="fa-xl fa fa-plus" title="' . $strAdd . '"></i></a></p>';
        ?>
        <div class="callout">
        <?php
        if ($numar == 0) {
            echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
        } else {
            // pagination controls
            echo '<div class="paginate">';
            echo $strTotal . " " . $numar . " " . $strItems;
            echo "<br><br>";
            echo $pages->display_pages();
            echo ' <a href="siteinvoicingitems.php">' . $strShowAll . '</a>';
            echo '</div>';
        
        ?>
        <table width="100%">
        <thead>
                <tr>
                    <th>Denumire</th>
                    <th>Detaliu</th>
                    <th>CPV</th>
                    <th>NC8</th>
                    <th>EAN</th>
                    <th>TVA %</th>
                    <th>Pret</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
<?php

    while ($row = ezpub_fetch_array($result)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['articol_denumire'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_detaliu'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_codcpv'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_codnc8'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_codean'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_procent_tva'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['articol_pret'], ENT_QUOTES, 'UTF-8') . '</td>';
        $id = (int)$row['articol_id'];
        $token = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
        $confirmMsg = htmlspecialchars($strConfirmDelete, ENT_QUOTES, 'UTF-8');
        $editTitle = htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8');
        $delTitle = htmlspecialchars($strDelete, ENT_QUOTES, 'UTF-8');
        echo '<td><a href="siteinvoicingitems.php?mode=edit&aID=' . $id . '"><i class="fa fa-edit fa-xl" title="' . $editTitle . '"></i></a> ';
        echo '<a href="siteinvoicingitems.php?mode=delete&aID=' . $id . '&csrf_token=' . $token . '" onclick="return confirm(\'' . $confirmMsg . '\');"><i class="fa fa-eraser fa-xl" title="' . $delTitle . '"></i></a></td>';
        echo '</tr>';
    }

?>
            </tbody>
            <tfoot><tr><td></td><td colspan="6"></td><td>&nbsp;</td></tr></tfoot>
        </table>
        <?php
       } // close callout div started above
        echo '</div>'; // end callout
        ?>
    </div>
</div>
<?php } ?>
<?php if (isset($_GET['mode']) && in_array($_GET['mode'], ['new','edit'])) { ?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h2>Adaugă / modifică articol</h2>
        <?php
            echo '<p><a href="siteinvoicingitems.php" class="button">' . $strBack . '&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>';
        ?>
        <?php
        // if editing, load existing values
        $edit = false;
        $values = [
            'articol_denumire'=>'', 'articol_detaliu'=>'', 'articol_codcpv'=>'',
            'articol_codnc8'=>'', 'articol_codean'=>'', 'articol_procent_tva'=>'',
            'articol_pret'=>''
        ];
        if (isset($_GET['mode']) && $_GET['mode']=='edit' && isset($_GET['aID']) && filter_var($_GET['aID'], FILTER_VALIDATE_INT)) {
            $edit = true;
            $aID = (int)$_GET['aID'];
            $stmt2 = mysqli_prepare($conn, "SELECT * FROM facturare_nomenclator WHERE articol_id = ?");
            mysqli_stmt_bind_param($stmt2, 'i', $aID);
            mysqli_stmt_execute($stmt2);
            $res2 = mysqli_stmt_get_result($stmt2);
            if ($row2 = mysqli_fetch_assoc($res2)) {
                foreach ($values as $k => $v) {
                    $values[$k] = $row2[$k] ?? '';
                }
            }
            mysqli_stmt_close($stmt2);
            // try to fetch descriptions for cpv and nc8
            $cpv_descr = '';
            if ($values['articol_codcpv'] !== '') {
                $stmt3 = mysqli_prepare($conn, "SELECT code_romana FROM generale_coduri_cpv WHERE code_cpv = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt3, 's', $values['articol_codcpv']);
                mysqli_stmt_execute($stmt3);
                $r3 = mysqli_stmt_get_result($stmt3);
                $row3 = mysqli_fetch_assoc($r3);
                $cpv_descr = $row3['code_romana'] ?? '';
                mysqli_stmt_close($stmt3);
            }
            $nc8_descr = '';
            if ($values['articol_codnc8'] !== '') {
                $stmt4 = mysqli_prepare($conn, "SELECT DM_RO FROM generale_coduri_nc8 WHERE CN = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt4, 's', $values['articol_codnc8']);
                mysqli_stmt_execute($stmt4);
                $r4 = mysqli_stmt_get_result($stmt4);
                $row4 = mysqli_fetch_assoc($r4);
                $nc8_descr = $row4['DM_RO'] ?? '';
                mysqli_stmt_close($stmt4);
            }
        }
        ?>
        <form method="post" action="siteinvoicingitems.php?mode=<?php echo $edit ? 'edit&aID=' . (int)$aID : 'new'; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />
            <div class="grid-x grid-padding-x">
                <div class="large-4 cell">
                    <label>Denumire<br>
                    <input type="text" name="articol_denumire" value="<?php echo htmlspecialchars($values['articol_denumire'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                </div>
                <div class="large-8 cell">
                    <label>Detaliu<br>
                    <input type="text" name="articol_detaliu" value="<?php echo htmlspecialchars($values['articol_detaliu'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
            </div>
            <div class="grid-x grid-padding-x">
                <div class="large-3 cell">
                    <label>Cod CPV<br>
                    <input type="text" id="cpv-search" name="articol_codcpv" autocomplete="off" value="<?php echo htmlspecialchars($values['articol_codcpv'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <input type="text" id="cpv-description" readonly size="40">
                    <div id="cpv-suggestion-box"></div>
                </div>
                <div class="large-3 cell">
                    <label>Cod NC8<br>
                    <input type="text" id="nc8-search" name="articol_codnc8" autocomplete="off" value="<?php echo htmlspecialchars($values['articol_codnc8'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <input type="text" id="nc8-description" readonly size="40">
                    <div id="nc8-suggestion-box"></div>
                </div>
                <div class="large-3 cell">
                    <label>Cod EAN<br>
                    <input type="text" name="articol_codean" value="<?php echo htmlspecialchars($values['articol_codean'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
                <div class="large-1 cell">
                    <label>Procent TVA<br>
                    <input type="text" name="articol_procent_tva" value="<?php echo htmlspecialchars($values['articol_procent_tva'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
                <div class="large-2 cell">
                    <label>Pret<br>
                    <input type="text" name="articol_pret" value="<?php echo htmlspecialchars($values['articol_pret'], ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>
                </div>
                <div class="grid-x grid-padding-x">
                <div class="large-12 cell text-center">
                    <input type="submit" class="button" value="<?php echo $edit ? $strModify : $strAdd; ?>">
                </div>
            </div>
        </form>
        <?php if ($edit) { ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var cpvdesc = document.getElementById('cpv-description');
            if (cpvdesc) cpvdesc.value = '<?php echo addslashes($cpv_descr); ?>';
            var nc8desc = document.getElementById('nc8-description');
            if (nc8desc) nc8desc.value = '<?php echo addslashes($nc8_descr); ?>';
        });
        </script>
        <?php } ?>
    </div>
</div>
<?php } ?>

<?php
include '../bottom.php';
