<?php
// Vizualizator tranzacții bancare importate
include_once '../settings.php';
include_once '../classes/common.php';
include_once '../classes/paginator.class.php';
$strPageTitle = "Tranzacții bancare importate";
include '../dashboard/header.php';

// Filtrare
$luna = isset($_GET['luna']) ? (int)$_GET['luna'] : 0;
$an = isset($_GET['an']) ? (int)$_GET['an'] : 0;
$iban_extras = isset($_GET['iban_extras']) ? trim($_GET['iban_extras']) : '';
$tip = isset($_GET['tip']) ? trim($_GET['tip']) : '';

$where = [];
if ($luna > 0) $where[] = "MONTH(data_tranzactie) = '$luna'";
if ($an > 0) $where[] = "YEAR(data_tranzactie) = '$an'";
if ($iban_extras != '') $where[] = "iban_extras = '".mysqli_real_escape_string($conn, $iban_extras)."'";
if ($tip != '') $where[] = "tip = '".mysqli_real_escape_string($conn, $tip)."'";
$where_sql = count($where) ? ('WHERE '.implode(' AND ', $where)) : '';

$sql_count = "SELECT COUNT(*) as total FROM tranzactii_bancare $where_sql";
$res_count = ezpub_query($conn, $sql_count);
$row_count = ezpub_fetch_array($res_count);
$numar = $row_count['total'] ?? 0;

$pages = new Pagination;
$pages->items_total = $numar;
$pages->mid_range = 7;
$pages->paginate();

$sql = "SELECT * FROM tranzactii_bancare $where_sql ORDER BY data_tranzactie DESC, id DESC $pages->limit";
$result = ezpub_query($conn, $sql);
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1><?php echo htmlspecialchars($strPageTitle)?></h1>
<a href="importtransactions.php" class="button"><?php echo htmlspecialchars($strAdd) ?>&nbsp;<i class="fa-xl fa fa-plus" title="<?php echo htmlspecialchars($strAdd) ?>"></i></a>
</div>
</div>
        <div class="grid-x grid-margin-x">
        <div class="large-3 medium-3 small-12 cell">
<form method="get" style="margin-bottom:10px">
    <label>Luna:
        <select name="luna" onchange="this.form.submit()">
            <option value="0">Toate</option>
            <?php for($i=1;$i<=12;$i++) echo '<option value="'.$i.'"'.($luna==$i?' selected':'').'>'.$i.'</option>'; ?>
        </select>
    </label>
</div>
        <div class="large-3 medium-3 small-12 cell">
    <label>An:
        <select name="an" onchange="this.form.submit()">
            <option value="0">Toți</option>
            <?php $an_curent = date('Y');
            for($i=$an_curent; $i>=2015; $i--) echo '<option value="'.$i.'"'.($an==$i?' selected':'').'>'.$i.'</option>'; ?>
        </select>
    </label>
</div>
        <div class="large-3 medium-3 small-12 cell">
    <label>IBAN extras:
        <select name="iban_extras" onchange="this.form.submit()">
            <option value="">Toate</option>
            <?php
            $qib = ezpub_query($conn, "SELECT DISTINCT iban_extras FROM tranzactii_bancare WHERE iban_extras IS NOT NULL AND iban_extras != '' ORDER BY iban_extras ASC");
            while($r = ezpub_fetch_array($qib)) {
                $val = $r['iban_extras'];
                echo '<option value="'.htmlspecialchars($val).'"'.($iban_extras==$val?' selected':'').'>'.htmlspecialchars($val).'</option>';
            }
            ?>
        </select>
    </label>
</div>
        <div class="large-3 medium-3 small-12 cell">
    <label>Tip tranzacție:
        <select name="tip" onchange="this.form.submit()">
            <option value="">Toate</option>
            <option value="C"<?=($tip=='C'?' selected':'')?>>Credit</option>
            <option value="D"<?=($tip=='D'?' selected':'')?>>Debit</option>
        </select>
    </label>
</form>
</div>
</div>
        <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
<div class="paginate"><?=$pages->display_pages()?></div>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Data</th>
        <th>Tip</th>
        <th>Suma</th>
        <th>Moneda</th>
        <th>IBAN extras</th>
        <th>IBAN tranzacție</th>
        <th>Detalii</th>
        <th>Referință</th>
    </tr>
    </thead>
    <?php while($row = ezpub_fetch_array($result)) { ?>
    <tr class="<?=(($row['tip']??'')==='C'?'paid':(($row['tip']??'')==='D'?'notpaid':''))?>">
        <td><?=htmlspecialchars($row['id'] ?? '')?></td>
        <td><?php 
            $d = $row['data_tranzactie'] ?? '';
            if ($d && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $md)) {
                echo $md[3].'.'.$md[2].'.'.$md[1];
            } else {
                echo htmlspecialchars($d);
            }
        ?></td>
        <td><?=htmlspecialchars($row['tip'] ?? '')?></td>
        <td align="right"><?php echo romanize($row['suma'] ?? 0); ?></td>
        <td><?=htmlspecialchars($row['moneda'] ?? '')?></td>
        <td><?=htmlspecialchars($row['iban_extras'] ?? '')?></td>
        <td><?=htmlspecialchars($row['iban'] ?? '')?></td>
        <td><?=htmlspecialchars($row['detalii'] ?? '')?></td>
        <td><?=htmlspecialchars($row['referinta'] ?? '')?></td>
    </tr>
    <?php } ?>
    <tfoot>
    <tr><td colspan="10">&nbsp;</td></tr>
    </tfoot>
</table>
<div class="paginate"><?=$pages->display_pages()?></div>
        </div>
        </div>
<?php
include '../bottom.php';
?>