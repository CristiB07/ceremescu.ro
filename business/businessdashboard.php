<?php
$strPageTitle = "Business Dashboard";
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("Location: ../login/index.php?message=MLF");
    die;
}
?>
<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h1><?php echo $strPageTitle?></h1>
        </div>
    </div>

    <?php
    // ONRC data (prepared statements)
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM od_firme_master");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $r_total = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $total_firme = $r_total['c'] ?? 0;
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM od_firme_master WHERE COD_STATUS=?");
    $status = '1048';
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $r_active = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $total_active = $r_active['c'] ?? 0;
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT FORMA_JURIDICA, COUNT(*) as c FROM od_firme_master GROUP BY FORMA_JURIDICA ORDER BY c DESC LIMIT 50");
    mysqli_stmt_execute($stmt);
    $q_forma = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    // ANAF data
    $stmt = mysqli_prepare($conn, "SELECT COUNT(DISTINCT cui) as c FROM clienti_date_fiscale");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $r_anaf = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $anaf_total = $r_anaf['c'] ?? 0;
    mysqli_stmt_close($stmt);

    // Bilanțuri overview
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM bilanturi");
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $r_b_total = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $bilant_total = $r_b_total['c'] ?? 0;
    mysqli_stmt_close($stmt);

    // Per-year aggregate (read from pre-aggregated summary table)
    $sql_year = "SELECT an, total_firme, cifra_afaceri, firme_pe_profit, profit_total, firme_pe_pierdere, pierdere_total, cifra_zero, numar_angajati FROM od_bilanturi_summary_by_year ORDER BY an DESC";
    $stmt = mysqli_prepare($conn, $sql_year);
    mysqli_stmt_execute($stmt);
    $q_year = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    ?>

    <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-12 cell">
            <div class="callout">
                <h5>ONRC</h5>
                <p><strong>Total firme:</strong> <?php echo romanize($total_firme)?></p>
                <p><strong>Total firme active (COD_STATUS=1048):</strong> <?php echo romanize($total_active)?></p>
                <h6>Structură firme (top 50)</h6>
                <ul>
                <?php while ($row = ezpub_fetch_array($q_forma)){
                    $name = htmlspecialchars($row['FORMA_JURIDICA']);
                    echo "<li>$name: " . romanize($row['c']) . "</li>";
                } ?>
                </ul>
            </div>
        </div>

        <div class="large-4 medium-4 small-12 cell">
            <div class="callout">
                <h5>ANAF</h5>
                <p><strong>Total firme (date fiscale):</strong> <?php echo romanize($anaf_total)?></p>
                <h5>Bilanțuri</h5>
                <p><strong>Total bilanțuri disponibile:</strong> <?php echo romanize($bilant_total)?></p>
            </div>
        </div>

    <?php
    // Yearly firms summary (from pre-aggregated table)
    $stmt = mysqli_prepare($conn, "SELECT an_inmatriculare, firme, active FROM od_firme_summary_by_year ORDER BY an_inmatriculare DESC");
    mysqli_stmt_execute($stmt);
    $q_firms_year = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    ?>


        <div class="large-4 medium-4 small-12 cell">
            <div class="callout">
                <h5>Firme pe ani (sumar)</h5>
                <table class="stack">
                    <thead>
                        <tr>
                            <th>An</th>
                            <th style="text-align:right">Firme</th>
                            <th style="text-align:right">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($fy = ezpub_fetch_array($q_firms_year)){
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($fy['an_inmatriculare']).'</td>';
                        echo '<td align="right">'.romanize($fy['firme']).'</td>';
                        echo '<td align="right">'.romanize($fy['active']).'</td>';
                        echo '</tr>';
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h4>Bilanțuri pe ani</h4>
            <table class="stack">
                <thead>
                    <tr>
                        <th>AN</th>
                        <th>Total firme</th>
                        <th>Cifră de afaceri</th>
                        <th>Firme pe profit</th>
                        <th>Profit</th>
                        <th>Firme pe pierdere</th>
                        <th>Pierdere</th>
                        <th>Cifră afaceri = 0</th>
                        <th>Număr angajați</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($y = ezpub_fetch_array($q_year)){
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($y['an']).'</td>';
                    echo '<td align="right">'.romanize($y['total_firme']).'</td>';
                    echo '<td align="right">'.romanize($y['cifra_afaceri']).'</td>';
                    echo '<td align="right">'.romanize($y['firme_pe_profit']).'</td>';
                    echo '<td align="right">'.romanize($y['profit_total']).'</td>';
                    echo '<td align="right">'.romanize($y['firme_pe_pierdere']).'</td>';
                    echo '<td align="right">'.romanize($y['pierdere_total']).'</td>';
                    echo '<td align="right">'.romanize($y['cifra_zero']).'</td>';
                    echo '<td align="right">'.romanize($y['numar_angajati']).'</td>';
                    echo '</tr>';
                } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

