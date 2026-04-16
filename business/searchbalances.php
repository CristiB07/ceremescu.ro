<?php
$strPageTitle = "Search Balances";
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("Location: ../login/index.php?message=MLF");
    die;
}
$relative_header = dirname(__FILE__) . '/../dashboard/header.php';
include $relative_header;

// fetch filter options
$stmt = mysqli_prepare($conn, "SELECT DISTINCT an FROM bilanturi ORDER BY an DESC LIMIT 50");
mysqli_stmt_execute($stmt);
$years_res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT DISTINCT county FROM generale_localitati ORDER BY county ASC");
mysqli_stmt_execute($stmt);
$counties_res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$year = isset($_GET['an']) ? intval($_GET['an']) : 0;
$county = isset($_GET['county']) ? trim($_GET['county']) : '';
$caen = isset($_GET['caen']) ? trim($_GET['caen']) : '';
$min_ca = isset($_GET['min_ca']) ? intval($_GET['min_ca']) : 0;
$min_ang = isset($_GET['min_ang']) ? intval($_GET['min_ang']) : 0;

?>
<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h1><?php echo $strPageTitle?></h1>
        </div>
    </div>

    <form method="get">
    <div class="grid-x grid-margin-x">
        <div class="large-2 cell">
            <label>An bilanț (obligatoriu)</label>
            <select name="an">
                <option value="">Selectați</option>
                <?php while($y=ezpub_fetch_array($years_res)){
                    $val = intval($y['an']);
                    $sel = ($val==$year)?'selected':'';
                    echo "<option value=\"$val\" $sel>$val</option>";
                } ?>
            </select>
        </div>

        <div class="large-3 cell">
            <label>Județ</label>
            <select name="county">
                <option value="">Toate</option>
                <?php while($c=ezpub_fetch_array($counties_res)){
                    $raw = trim($c['county']);
                    $v = htmlspecialchars($raw, ENT_QUOTES);
                    $sel = ($raw == $county)?'selected':'';
                    echo "<option value=\"$v\" $sel>$v</option>";
                } ?>
            </select>
        </div>

        <div class="large-2 cell">
            <label>Cod CAEN</label>
            <input type="text" name="caen" value="<?php echo htmlspecialchars($caen)?>">
        </div>

        <div class="large-2 cell">
            <label>Cifră afaceri &gt;=</label>
            <input type="number" name="min_ca" value="<?php echo htmlspecialchars($min_ca)?>">
        </div>

        <div class="large-2 cell">
            <label>Angajați &gt;=</label>
            <input type="number" name="min_ang" value="<?php echo htmlspecialchars($min_ang)?>">
        </div>

        <div class="large-1 cell" style="align-self:flex-end">
            <input type="submit" class="button" value="Caută">
        </div>
    </div>
    </form>

    <?php
    if ($year>0) {
        // build where clauses
        // build dynamic prepared statement with parameters
        $clauses = [];
        $types = '';
        $params = [];

        $clauses[] = 'b.an = ?';
        $types .= 'i';
        $params[] = $year;

        if ($county !== '') {
            // Use LIKE with trimmed value to match different stored formats (e.g. extra words/spacing)
            // Special-case: treat any form of București / Municipiul București as 'Bucure' to match registry values
            $county_trim = trim($county);
            $county_normal = strtolower($county_trim);
            $county_normal = str_replace(array('ș','ş','ț','ţ'), array('s','s','t','t'), $county_normal);
            if (strpos($county_normal, 'bucur') !== false) {
                $param = '%Bucure%';
            } else {
                $param = '%'.$county_trim.'%';
            }
            $clauses[] = 'TRIM(o.ADR_JUDET) LIKE ?';
            $types .= 's';
            $params[] = $param;
        }
        if ($caen !== '') {
            $clauses[] = 'b.caen = ?';
            $types .= 's';
            $params[] = $caen;
        }
        if ($min_ca > 0) {
            $clauses[] = 'b.cifra_afaceri_net >= ?';
            $types .= 'i';
            $params[] = $min_ca;
        }
        if ($min_ang > 0) {
            $clauses[] = 'b.numar_salariati >= ?';
            $types .= 'i';
            $params[] = $min_ang;
        }

        $where_sql = implode(' AND ', $clauses);
        $join = 'LEFT JOIN od_firme_master o ON o.CUI = b.cui';

        // count total using prepared statement
        $count_sql = "SELECT COUNT(*) as cnt FROM bilanturi b $join WHERE $where_sql";
        $stmt = mysqli_prepare($conn, $count_sql);
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $cres = mysqli_stmt_get_result($stmt);
        $crow = mysqli_fetch_array($cres, MYSQLI_ASSOC);
        $numar = intval($crow['cnt']);
        mysqli_stmt_close($stmt);

        require_once dirname(__FILE__) . '/../classes/paginator.class.php';
        $pages = new Pagination;
        $pages->items_total = $numar;
        $pages->mid_range = 5;
        $pages->paginate();

        // Now fetch paginated data (append LIMIT from paginator)
        $data_sql = "SELECT b.deni, b.cui, b.caen, b.cifra_afaceri_net, b.numar_salariati, b.profit_net, b.pierdere_neta, o.ADR_JUDET
            FROM bilanturi b $join WHERE $where_sql ORDER BY b.cifra_afaceri_net DESC " . $pages->limit;

        $stmt = mysqli_prepare($conn, $data_sql);
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    ?>
      <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
<div class="paginate"><?php echo $pages->display_pages()?></div>
</div>
</div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <p>Rezultate: <?php echo number_format($numar)?>.</p>
            <table class="stack">
                <thead>
                    <tr><th>Denumire</th><th>CUI</th><th>CAEN</th><th>Cifră afaceri</th><th>Angajați</th><th>Profit/Pierdere</th><th>Vizualizează</th></tr>
                </thead>
                <tbody>
                <?php while ($row = ezpub_fetch_array($res)){
                    $profit = 0;
                    if (!empty($row['profit_net']) && $row['profit_net']!=0) { $profit = intval($row['profit_net']); }
                    else { $profit = -1 * intval($row['pierdere_neta'] ?? 0); }
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($row['deni']).'</td>';
                    echo '<td>'.htmlspecialchars($row['cui']).'</td>';
                    echo '<td>'.htmlspecialchars($row['caen']).'</td>';
                    echo '<td align="right">'.romanize($row['cifra_afaceri_net']).'</td>';
                    echo '<td>'.htmlspecialchars($row['numar_salariati']).'</td>';
                    echo '<td align="right">'.romanize($profit).'</td>';
                    $company_link = '/business/companiesinfo.php?cui=' . urlencode($row['cui']);
                    echo '<td><a class="button small" href="'.htmlspecialchars($company_link).'"><i class="fa fa-eye"></i> Vezi</a></td>';
                    echo '</tr>';
                } ?>
                </tbody>
                <tfoot><tr><td colspan="7"></td></tr></tfoot>
            </table>
        </div>
    </div>

    <?php } // end if year provided ?>
      <?php if (isset($pages)) { ?>
      <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
<div class="paginate"><?php echo $pages->display_pages()?></div>
</div>
</div>
      <?php } ?>

<?php
include '../bottom.php';
?>
