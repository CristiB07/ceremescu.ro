<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle="Administrare alte cheltuieli recurente";

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
    $stmt = mysqli_prepare($conn, "DELETE FROM facturare_alte_cheltuieli_recurente_recurente WHERE cheltuiala_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $cID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: '.$_SERVER['PHP_SELF'].'');
    exit;
}

// Handle save (add/edit)
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save_other'])){
    // Normalize suma: remove thousands separators (.) and spaces, convert comma to dot
    $raw_suma = trim($_POST['cheltuiala_suma']);
    $norm = str_replace(['.', ' '], ['', ''], $raw_suma); // remove thousands separator and spaces
    $norm = str_replace(',', '.', $norm); // decimal comma -> dot
    $suma = floatval($norm);
    // Termen is date only (no time)
    $termen = trim($_POST['cheltuiala_termen']) ? date('Y-m-d 00:00:00', strtotime($_POST['cheltuiala_termen'])) : date('Y-m-d 00:00:00');
    $obs = trim($_POST['cheltuiala_observatii']);
    $achitat = isset($_POST['cheltuiala_achitat']) ? 1 : 0;
    if (!empty($_POST['cheltuiala_id'])){
        $id = (int)$_POST['cheltuiala_id'];
        $stmt = mysqli_prepare($conn, "UPDATE facturare_alte_cheltuieli_recurente SET cheltuiala_termen=?, cheltuiala_suma=?, cheltuiala_observatii=?, cheltuiala_achitat=? WHERE cheltuiala_id=?");
        mysqli_stmt_bind_param($stmt, 'sdsii', $termen, $suma, $obs, $achitat, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO facturare_alte_cheltuieli_recurente (cheltuiala_termen, cheltuiala_suma, cheltuiala_observatii, cheltuiala_achitat) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sdsi', $termen, $suma, $obs, $achitat);
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
    $countres = ezpub_query($conn, "SELECT COUNT(*) AS total FROM facturare_alte_cheltuieli_recurente");
    $crow = ezpub_fetch_array($countres);
    $pages->items_total = (int)$crow['total'];
    $pages->paginate();

    $query = "SELECT * FROM facturare_alte_cheltuieli_recurente ORDER BY cheltuiala_termen DESC" . $pages->limit;
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
        $r = ezpub_query($conn, "SELECT * FROM facturare_alte_cheltuieli_recurente WHERE cheltuiala_id='$id'");
        $editdata = ezpub_fetch_array($r);
        $editing = true;
    }
    ?>
    <div class="grid-x grid-margin-x" style="margin-top:20px;">
        <div class="large-12 cell">
            <h4><?php echo $editing? 'Modifică cheltuială':'Adaugă cheltuială'?></h4>
            <div id="other-expense-error" class="callout alert" style="display:none;margin-bottom:10px;"></div>
            <form id="other-expense-form" method="post" action="">
                <input type="hidden" name="cheltuiala_id" value="<?php echo $editing? (int)$editdata['cheltuiala_id'] : ''?>" />
                <div class="grid-x grid-padding-x">
                    <div class="large-3 cell">
                        <label>Data (termen)
                            <input type="datetime-local" name="cheltuiala_termen" value="<?php echo $editing? date('Y-m-d\TH:i', strtotime($editdata['cheltuiala_termen'])): date('Y-m-d\TH:i')?>" />
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
                        <label>&nbsp;<br />
                            <input type="checkbox" name="cheltuiala_achitat" <?php echo ($editing && $editdata['cheltuiala_achitat'])? 'checked' : ''?> /> Achitat
                        </label>
                    </div>
                </div>
                <div style="margin-top:10px">
                    <input type="submit" name="save_other" value="Salvează" class="button" />
                    <a href="?" class="button secondary">Anulează</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}


include(__DIR__.'/../bottom.php');
?>
