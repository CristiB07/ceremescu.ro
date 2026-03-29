<?php
include '../settings.php';
include '../classes/paginator.class.php';
include '../classes/common.php';
$strPageTitle="Administrare active"; // previously "Administrare bunuri"
include '../dashboard/header.php';

if(!isset($_SESSION)) 
{ 
    session_start(); 
}
if (!isSet($_SESSION['userlogedin']))
{
    header("location:$strSiteURL/login/index.php?message=MLF");
    exit();
}

$uid = (int)$_SESSION['uid'];
$code = $_SESSION['code']; // not really used, but keep for consistency
$role = $_SESSION['clearence'] ?? '';
if ($role !== 'ADMIN') {
    die('<div class="callout alert">Unauthorized</div>');
}

// helper to output safe strings
function h($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}

// show messages
if ((isSet($_GET['message'])) AND $_GET['message']=="Error"){
    echo "<div class=\"callout alert\">$strThereWasAnError</div>" ;
}
if ((isSet($_GET['message'])) AND $_GET['message']=="Success"){
    echo "<div class=\"callout success\">$strMessageSent</div>" ;
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
        echo "<h1>$strPageTitle</h1>";

        // delete flow
        if (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){
            if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
                die('<div class="callout alert">Invalid record ID</div>');
            }
            $cID = intval($_GET['cID']);
            // verify record exists before deleting
            $stmt = $conn->prepare("SELECT bun_id FROM administrative_bunuri WHERE bun_id=?");
            $stmt->bind_param("i", $cID);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();
            if (!$row) {
                die('<div class="callout alert">Record not found</div>');
            }
            $stmt = $conn->prepare("DELETE FROM administrative_bunuri WHERE bun_id=?");
            $stmt->bind_param("i", $cID);
            $stmt->execute();
            $stmt->close();
            echo "<div class=\"callout success\">$strRecordDeleted</div></div></div>";
            echo "<script type=\"text/javascript\">\nfunction delayer(){\n    window.location.href='siteassets.php';\n}\nsetTimeout('delayer()', 1500);\n</script>";
            include '../bottom.php';
            exit();
        }

        // POST handling (insert / update)
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            // common sanitization
            $bun_categorie = trim($_POST['bun_categorie'] ?? '');
            $bun_denumire = trim($_POST['bun_denumire'] ?? '');
            $bun_descriere = trim($_POST['bun_descriere'] ?? '');
            $bun_locatie = trim($_POST['bun_locatie'] ?? '');
            $bun_adresa = trim($_POST['bun_adresa'] ?? '');
            $bun_proprietar = intval($_POST['bun_proprietar'] ?? $uid);
            $bun_mobil = isset($_POST['bun_mobil']) && $_POST['bun_mobil']=='1' ? 1 : 0;
            $bun_utilizat_extern = isset($_POST['bun_utilizat_extern']) && $_POST['bun_utilizat_extern']=='1' ? 1 : 0;
            $bun_date = trim($_POST['bun_date'] ?? '');
            $bun_licente = trim($_POST['bun_licente'] ?? '');
            $bun_securitate = trim($_POST['bun_securitate'] ?? '');
            $bun_riscuri_asociate = trim($_POST['bun_riscuri_asociate'] ?? '');
            $bun_nivel_risc = intval($_POST['bun_nivel_risc'] ?? 0);
            $bun_valoareC = parseRomanianNumber($_POST['bun_valoareC'] ?? '0');
            $bun_valoareA = parseRomanianNumber($_POST['bun_valoareA'] ?? '0');
            $bun_dataO = trim($_POST['bun_dataO'] ?? '');
            $bun_dataA = trim($_POST['bun_dataA'] ?? '');
            $bun_amortizare = parseRomanianNumber($_POST['bun_amortizare'] ?? '0');

            if ($_GET['mode'] == "new"){
                // insert
                $stmt = $conn->prepare("INSERT INTO administrative_bunuri
                    (bun_categorie,bun_denumire,bun_descriere,bun_locatie,bun_adresa,bun_proprietar,
                     bun_mobil,bun_utilizat_extern,bun_date,bun_licente,bun_securitate,bun_riscuri_asociate,
                     bun_nivel_risc,bun_valoareC,bun_valoareA,bun_dataO,bun_dataA,bun_amortizare)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("sssssiiissssiddssd",
                    $bun_categorie,$bun_denumire,$bun_descriere,$bun_locatie,$bun_adresa,$bun_proprietar,
                    $bun_mobil,$bun_utilizat_extern,$bun_date,$bun_licente,$bun_securitate,$bun_riscuri_asociate,
                    $bun_nivel_risc,$bun_valoareC,$bun_valoareA,$bun_dataO,$bun_dataA,$bun_amortizare);
                if (!$stmt->execute()){
                    $stmt->close();
                    die('Error: ' . $conn->error);
                }
                $stmt->close();
                echo "<div class=\"callout success\">$strRecordAdded</div>";
                echo "<script type=\"text/javascript\">\nfunction delayer(){\n    window.location.href='siteassets.php';\n}\nsetTimeout('delayer()', 1500);\n</script>";
                include '../bottom.php';
                exit();
            } else {
                // edit
                if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
                    die('<div class="callout alert">Invalid record ID</div>');
                }
                $cID = intval($_GET['cID']);
                // check ownership
                // verify record exists
                $stmt = $conn->prepare("SELECT bun_id FROM administrative_bunuri WHERE bun_id=?");
                $stmt->bind_param("i", $cID);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stmt->close();
                if (!$row) {
                    die('<div class="callout alert">Record not found</div>');
                }
                // perform update using prepared statement with correct types
                $stmt = $conn->prepare("UPDATE administrative_bunuri SET
                    bun_categorie=?,bun_denumire=?,bun_descriere=?,bun_locatie=?,bun_adresa=?,bun_proprietar=?,
                    bun_mobil=?,bun_utilizat_extern=?,bun_date=?,bun_licente=?,bun_securitate=?,bun_riscuri_asociate=?,
                    bun_nivel_risc=?,bun_valoareC=?,bun_valoareA=?,bun_dataO=?,bun_dataA=?,bun_amortizare=?
                    WHERE bun_id=?");
                $stmt->bind_param("sssssiiissssiddssdi",
                    $bun_categorie,$bun_denumire,$bun_descriere,$bun_locatie,$bun_adresa,$bun_proprietar,
                    $bun_mobil,$bun_utilizat_extern,$bun_date,$bun_licente,$bun_securitate,$bun_riscuri_asociate,
                    $bun_nivel_risc,$bun_valoareC,$bun_valoareA,$bun_dataO,$bun_dataA,$bun_amortizare,$cID);
                if (!$stmt->execute()){
                    $stmt->close();
                    die('Error: ' . $conn->error);
                }
                $stmt->close();
                echo "<div class=\"callout success\">$strRecordModified</div>" ;
                echo "<script type=\"text/javascript\">\nfunction delayer(){\n    window.location.href='siteassets.php';\n}\nsetTimeout('delayer()', 1500);\n</script>";
                include '../bottom.php';
                exit();
            }
        }

        // show form or listing
        if (IsSet($_GET['mode']) AND ($_GET['mode']=="new" OR $_GET['mode']=="edit")){
            $record = array();
            if ($_GET['mode']=="edit"){
                if (!isset($_GET['cID']) || !is_numeric($_GET['cID'])) {
                    die('<div class="callout alert">Invalid record ID</div>');
                }
                $cID = intval($_GET['cID']);
                $stmt = $conn->prepare("SELECT * FROM administrative_bunuri WHERE bun_id=?");
                $stmt->bind_param("i", $cID);
                $stmt->execute();
                $res = $stmt->get_result();
                $record = $res->fetch_assoc();
                $stmt->close();
                if (!$record) {
                    die('<div class="callout alert">Unauthorized access</div>');
                }
            }

            // owner dropdown
            $owners = array();
            $sql = "SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori ORDER BY utilizator_Nume ASC";
            $res = $conn->query($sql);
            while($r = $res->fetch_assoc()){
                $owners[] = $r;
            }
            ?>
        <form method="post" action="siteassets.php?mode=<?php echo ($_GET['mode']=='new'?'new':'edit');
                if(isset($cID)) echo '&cID=' . intval($cID); ?>">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strCategory ?? 'Categorie';?>
                        <input name="bun_categorie" type="text" value="<?php echo h($record['bun_categorie'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strName ?? 'Denumire';?>
                        <input name="bun_denumire" type="text" value="<?php echo h($record['bun_denumire'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strPurchaseDate ?? 'Data achiziției';?>
                        <input name="bun_dataO" type="date" value="<?php echo h($record['bun_dataO'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strOwner ?? 'Proprietar';?>
                        <select name="bun_proprietar">
                            <option value="0">--</option>
                            <?php foreach($owners as $o){
                                $sel = ($record['bun_proprietar'] ?? $uid) == $o['utilizator_ID'] ? ' selected' : '';
                                echo "<option value=\"" . h($o['utilizator_ID']) . "\"$sel>" . h($o['utilizator_Nume'] . ' ' . $o['utilizator_Prenume']) . "</option>";
                            } ?>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strValue ?? 'Valoare cost';?>
                        <input name="bun_valoareC" type="text" value="<?php echo h($record['bun_valoareC'] ?? ''); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDescription ?? 'Descriere';?>
                        <textarea name="bun_descriere"><?php echo h($record['bun_descriere'] ?? ''); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strInformationContained ?? 'Date';?>
                        <textarea name="bun_date"><?php echo h($record['bun_date'] ?? ''); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strLicenses ?? 'Licențe';?>
                        <textarea name="bun_licente"><?php echo h($record['bun_licente'] ?? ''); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strLocation ?? 'Locație';?>
                        <input name="bun_locatie" type="text" value="<?php echo h($record['bun_locatie'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strAddress ?? 'Adresă';?>
                        <input name="bun_adresa" type="text" value="<?php echo h($record['bun_adresa'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strMobile ?? 'Mobil';?><br />
                        <input type="radio" name="bun_mobil" value="1" <?php if(($record['bun_mobil'] ?? 0)==1) echo 'checked'; ?>> <?php echo $strYes ?? 'Da';?>
                        <input type="radio" name="bun_mobil" value="0" <?php if(($record['bun_mobil'] ?? 0)==0) echo 'checked'; ?>> <?php echo $strNo ?? 'Nu';?>
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strExtern ?? 'Utilizat extern';?><br />
                        <input type="radio" name="bun_utilizat_extern" value="1" <?php if(($record['bun_utilizat_extern'] ?? 0)==1) echo 'checked'; ?>> <?php echo $strYes ?? 'Da';?>
                        <input type="radio" name="bun_utilizat_extern" value="0" <?php if(($record['bun_utilizat_extern'] ?? 0)==0) echo 'checked'; ?>> <?php echo $strNo ?? 'Nu';?>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strSecurity ?? 'Securitate';?>
                        <input name="bun_securitate" type="text" value="<?php echo h($record['bun_securitate'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strRiskLevel ?? 'Nivel risc';?>
                        <input name="bun_nivel_risc" type="number" value="<?php echo h($record['bun_nivel_risc'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strValueAmort ?? 'Valoare amortizare';?>
                        <input name="bun_valoareA" type="text" value="<?php echo h($record['bun_valoareA'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-12 cell">
                    <label><?php echo $strAmortDate ?? 'Data amortizării';?>
                        <input name="bun_dataA" type="date" value="<?php echo h($record['bun_dataA'] ?? ''); ?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strAmort ?? 'Amortizare';?>
                        <input name="bun_amortizare" type="text" value="<?php echo h($record['bun_amortizare'] ?? ''); ?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strAssociatedRisks ?? 'Riscuri asociate';?>
                        <textarea name="bun_riscuri_asociate"><?php echo h($record['bun_riscuri_asociate'] ?? ''); ?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center">
                    <input type="submit" value="<?php echo ($_GET['mode']=='new'?$strAddNew:$strModify)?>" name="Submit" class="button">
                </div>
            </div>
        </form>
        <?php
        } else {
            // listing
            // prepare filter values
            $filter_category = trim($_GET['filter_category'] ?? '');
            $filter_owner = isset($_GET['filter_proprietar']) ? intval($_GET['filter_proprietar']) : 0;
            $qs = ''; // query string for export link

            // validate category against list from database to avoid odd inputs
            $validCats = [];
            $catRes = $conn->query("SELECT DISTINCT bun_categorie FROM administrative_bunuri");
            while ($catRow = $catRes->fetch_assoc()) {
                $validCats[] = $catRow['bun_categorie'];
            }
            if ($filter_category !== '' && !in_array($filter_category, $validCats, true)) {
                // invalid value came from tampering, ignore it
                $filter_category = '';
            }

            // owner id will be validated later when building the drop‑down; non‑numeric already stripped

            // build where clause
            $where = [];
            $types = '';
            $params = [];
            if ($filter_category !== '') {
                $where[] = 'bun_categorie = ?';
                $types .= 's';
                $params[] = $filter_category;
                $qs .= '&filter_category=' . urlencode($filter_category);
            }
            if ($filter_owner > 0) {
                $where[] = 'bun_proprietar = ?';
                $types .= 'i';
                $params[] = $filter_owner;
                $qs .= '&filter_proprietar=' . urlencode($filter_owner);
            }
            $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // filters form
            echo '<form method="get" action="siteassets.php" class="grid-x grid-margin-x">';
            echo '<div class="large-3 medium-3 small-12 cell"><label>' . ($strCategory ?? 'Categorie') . '<select name="filter_category"><option value="">--</option>';
            $catRes = $conn->query("SELECT DISTINCT bun_categorie FROM administrative_bunuri ORDER BY bun_categorie");
            while ($catRow = $catRes->fetch_assoc()) {
                $sel = ($catRow['bun_categorie'] === $filter_category) ? ' selected' : '';
                echo '<option value="' . h($catRow['bun_categorie']) . '"' . $sel . '>' . h($catRow['bun_categorie']) . '</option>';
            }
            echo '</select></label></div>';
            echo '<div class="large-3 medium-3 small-12 cell"><label>' . ($strOwner ?? 'Proprietar') . '<select name="filter_proprietar"><option value="0">--</option>';
            $ownStmt = $conn->prepare("SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori ORDER BY utilizator_Nume ASC");
            $ownStmt->execute();
            $ownRes = $ownStmt->get_result();
            while ($o = $ownRes->fetch_assoc()) {
                $val = $o['utilizator_ID'];
                $name = $o['utilizator_Nume'] . ' ' . $o['utilizator_Prenume'];
                $sel = ($filter_owner == $val) ? ' selected' : '';
                echo '<option value="' . h($val) . '"' . $sel . '>' . h($name) . '</option>';
            }
            $ownStmt->close();
            echo '</select></label></div>';
            echo '<div class="large-2 medium-2 small-12 cell"><label>&nbsp;</label><button type="submit" class="button">' . ($strFilter ?? 'Filtrează') . '</button></div>';
            echo '<div class="large-2 medium-2 small-12 cell"><label>&nbsp;</label><a href="siteassets.php" class="button">' . ($strClearAllFilters ?? 'Reset') . '</a></div>';
            echo '</form><br />';

            echo "<a href=\"siteassets.php?mode=new\" class=\"button\">$strAddNew <i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a>&nbsp;<a href=\"siteassets_export.php?" . ltrim($qs, '&') . "\" class=\"button\"><i class=\"fa-xl fas fa-file-excel\"></i>&nbsp;Export Excel</a><br />";
            // first get total count for pagination
            // retrieve all assets
            $sql = "SELECT * FROM administrative_bunuri $where_sql ORDER BY bun_id DESC";
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $numar = $result->num_rows;
            $pages = new Pagination;
            $pages->items_total = $numar;
            $pages->mid_range = 5;
            $pages->paginate();

            if ($numar==0){
                $stmt->close();
                echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
            } else {
                // re-run query applying limit clause generated by paginator (with same filters)
                $stmt->close();
                $sql2 = "SELECT * FROM administrative_bunuri $where_sql ORDER BY bun_id DESC " . $pages->limit;
                $stmt = $conn->prepare($sql2);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
        <h3><?php echo $strAssets ?? ($strBunuri ?? 'Bunuri');?></h3>
        <div class="paginate">
            <?php
            // display assets count
            echo $strTotal . " " .$numar." " . ($strAssets ?? ($strBunuri ?? 'bunuri'));
            echo " <br /><br />";
            echo $pages->display_pages() . " <a href=\"siteassets.php\" title=\"$strClearAllFilters\">$strShowAll</a>&nbsp;";
            echo " <br /><br />";
            ?>
        </div>
        <!-- export button -->
        <?php
        // preserve filter parameters in export link
        $qs = '';
        if ($filter_category !== '') {
            $qs .= '&filter_category=' . urlencode($filter_category);
        }
        if ($filter_owner > 0) {
            $qs .= '&filter_proprietar=' . urlencode($filter_owner);
        }
        ?>
        <br />
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strCategory ?? 'Categorie';?></th>
                    <th><?php echo $strName ?? 'Denumire';?></th>
                    <th><?php echo $strLocation ?? 'Locație';?></th>
                    <th><?php echo $strOwner ?? 'Proprietar';?></th>
                    <th><?php echo $strValue ?? 'Valoare';?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row=$result->fetch_assoc()){
                    echo "<tr>";
                    echo "<td>".h($row['bun_categorie'])."</td>";
                    echo "<td>".h($row['bun_denumire'])."</td>";
                    echo "<td>".h($row['bun_locatie'])."</td>";
                    // owner name lookup
                    $ownname = '';
                    if ($row['bun_proprietar']){
                        $stmt2 = $conn->prepare("SELECT utilizator_Nume, utilizator_Prenume FROM date_utilizatori WHERE utilizator_ID=?");
                        $stmt2->bind_param("i", $row['bun_proprietar']);
                        $stmt2->execute();
                        $r2 = $stmt2->get_result()->fetch_assoc();
                        $stmt2->close();
                        if ($r2) $ownname = $r2['utilizator_Nume'].' '.$r2['utilizator_Prenume'];
                    }
                    echo "<td>".h($ownname)."</td>";
                    echo "<td>".h($row['bun_valoareC'])."</td>";
                    echo "<td><a href=\"siteassets.php?mode=edit&cID=".h($row['bun_id'])."\" ><i class=\"far fa-edit fa-xl\" title=\"".h($strEdit)."\"></i></a></td>";
                    echo "<td><a href=\"siteassets.php?mode=delete&cID=".h($row['bun_id'])."\"  OnClick=\"return confirm('".h($strConfirmDelete)."');\"><i class=\"fa fa-eraser fa-xl\" title=\"".h($strDelete)."\"></i></a></td>";
                    echo "</tr>";
                }
                $stmt->close();
                echo "</tbody><tfoot><tr><td></td><td colspan=\"5\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
            }
        }
        ?>
    </div>
</div>
<hr />
<?php
include '../bottom.php';
?>
