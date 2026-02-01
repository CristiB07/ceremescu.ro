<?php
include_once '../settings.php';
include_once '../classes/common.php';

$cui_selectat = isset($_GET['cui']) ? (int)$_GET['cui'] : '';
// Preia toate CUI-urile distincte din tabel
$cuiuri = [];
$query = "SELECT DISTINCT cui FROM bilanturi ORDER BY cui ASC";
$result = ezpub_query($conn, $query);
while ($row = ezpub_fetch_array($result)) {
    $cuiuri[] = $row['cui'];
}
// --- Regula ANAF bilanțuri: ---
// Dacă data curentă < 1 iulie, ultimul an disponibil = anul curent -2
// Dacă data curentă >= 1 iulie, ultimul an disponibil = anul curent -1
$today = new DateTime();
$currentYear = (int)$today->format('Y');
$cutoff = new DateTime($currentYear.'-07-01');
if ($today < $cutoff) {
    $expectedYear = $currentYear - 2;
} else {
    $expectedYear = $currentYear - 1;
}

// Dacă avem CUI selectat, verificăm dacă există bilanț pentru anul așteptat
if ($cui_selectat) {
    $query_check = "SELECT COUNT(*) as cnt FROM bilanturi WHERE cui = ? AND an = ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $cui_selectat, $expectedYear);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $row_check = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);
    if (empty($row_check['cnt']) || $row_check['cnt'] == 0) {
        // Nu există bilanț pentru anul așteptat, încearcă să actualizezi
        include_once __DIR__ . '/balancesgetlib.php';
        if (function_exists('getLatestBalanceForCUI')) {
            getLatestBalanceForCUI($cui_selectat, $expectedYear, $conn);
        } elseif (function_exists('import_bilanturi_anaf')) {
            // older function name used in balancesgetlib.php
            import_bilanturi_anaf($cui_selectat, $conn);
        }
    }
}
echo '<form method="get" action="">'
    . '<label for="cui">Selectează CUI:</label>'
    . '<select name="cui" id="cui" required>';
foreach ($cuiuri as $cui) {
    $sel = ($cui == $cui_selectat) ? 'selected' : '';
    echo '<option value="'.htmlspecialchars((string)$cui).'" '.$sel.'>'.htmlspecialchars((string)$cui).'</option>';
}
echo '</select> <button type="submit">Afișează bilanțuri</button></form><br />';

if ($cui_selectat) {
    $query = "SELECT * FROM bilanturi WHERE cui = ? ORDER BY an DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $cui_selectat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $bilanturi = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bilanturi[] = $row;
    }
    if (count($bilanturi) > 0) {
        // Inversez array-ul pentru comparații și grafic (ordine crescătoare)
        $bilanturi_cresc = array_reverse($bilanturi);
        $latest = $bilanturi[0];
        echo '<div style="margin-bottom:1em;">';
        echo '<strong>Denumire:</strong> '.htmlspecialchars((string)$latest['deni']).'<br />';
        echo '<strong>CUI:</strong> '.htmlspecialchars((string)$latest['cui']).'<br />';
        echo '<strong>CAEN:</strong> '.htmlspecialchars((string)$latest['caen']).'<br />';
        echo '<strong>Denumire CAEN:</strong> '.htmlspecialchars((string)$latest['den_caen']).'<br />';
        echo '</div>';
        $cols = [
            'cifra_afaceri_net' => 'Cifra de afaceri neta',
            'venituri_totale' => 'VENITURI TOTALE',
            'venituri_in_avans' => 'VENITURI IN AVANS',
            'cheltuieli_totale' => 'CHELTUIELI TOTALE',
            'cheltuieli_in_avans' => 'CHELTUIELI IN AVANS',
            'profit_pierdere_brut' => 'Profit/Pierdere brută',
            'profit_pierdere_net' => 'Profit/Pierdere netă',
            'provizioane' => 'PROVIZIOANE',
            'capitaluri_total' => 'CAPITALURI - TOTAL, din care:',
            'capital_subscris' => 'Capital subscris varsat',
            'patrimoniu_regie' => 'Patrimoniul regiei',
            'numar_salariati' => 'Numar mediu de salariati',
            'active_imobilizate' => 'ACTIVE IMOBILIZATE - TOTAL',
            'active_circulante' => 'ACTIVE CIRCULANTE - TOTAL, din care:',
            'stocuri' => 'Stocuri',
            'creante' => 'Creante',
            'casa_banci' => 'Casa si conturi la banci',
            'datorii' => 'DATORII'
        ];
        echo '<table border="1" cellpadding="5" cellspacing="0"><thead><tr><th>An</th>';
        foreach ($cols as $col => $den) {
            echo '<th>'.htmlspecialchars($den).'</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($bilanturi as $row) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars((string)$row['an']).'</td>';
            // Unificare profit/pierdere brută
            $valoare_brut = 0; $style_brut = 'text-align:right;'; $valoare_fmt_brut = '';
            if ($row['profit_brut'] > 0) {
                $valoare_brut = $row['profit_brut'];
                $style_brut = 'color:green;font-weight:bold;text-align:right;';
                $valoare_fmt_brut = number_format($valoare_brut, 0, ',', '.');
            } elseif ($row['pierdere_bruta'] > 0) {
                $valoare_brut = -$row['pierdere_bruta'];
                $style_brut = 'color:red;font-weight:bold;text-align:right;';
                $valoare_fmt_brut = '-'.number_format($row['pierdere_bruta'], 0, ',', '.');
            }
            // Unificare profit/pierdere netă
            $valoare_net = 0; $style_net = 'text-align:right;'; $valoare_fmt_net = '';
            if ($row['profit_net'] > 0) {
                $valoare_net = $row['profit_net'];
                $style_net = 'color:green;font-weight:bold;text-align:right;';
                $valoare_fmt_net = number_format($valoare_net, 0, ',', '.');
            } elseif ($row['pierdere_neta'] > 0) {
                $valoare_net = -$row['pierdere_neta'];
                $style_net = 'color:red;font-weight:bold;text-align:right;';
                $valoare_fmt_net = '-'.number_format($row['pierdere_neta'], 0, ',', '.');
            }
            foreach ($cols as $col => $den) {
                if ($col == 'profit_pierdere_brut') {
                    echo '<td style="'.$style_brut.'">'.$valoare_fmt_brut.'</td>';
                } elseif ($col == 'profit_pierdere_net') {
                    echo '<td style="'.$style_net.'">'.$valoare_fmt_net.'</td>';
                } else {
                    $valoare = $row[$col];
                    $style = 'text-align:right;';
                    $valoare_fmt = '';
                    if (is_numeric($valoare) && $valoare != 0) {
                        $valoare_fmt = number_format($valoare, 0, ',', '.');
                    }
                    echo '<td style="'.$style.'">'.$valoare_fmt.'</td>';
                }
            }
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Definire comparatii o singură dată
        $comparatii = [
            'cifra_afaceri_net' => 'Cifra de afaceri netă',
            'profit_pierdere_brut' => 'Profit/Pierdere brută',
            'profit_pierdere_net' => 'Profit/Pierdere netă',
            'capitaluri_total' => 'Capitaluri proprii',
            'numar_salariati' => 'Număr angajați'
        ];
        $ani = array_map(function($row){return $row['an'];}, $bilanturi_cresc);
        $chart_data = [];
        foreach ($comparatii as $col => $den) {
            $vals = [];
            foreach ($bilanturi_cresc as $row) {
                if ($col == 'profit_pierdere_brut') {
                    $v = ($row['profit_brut'] > 0) ? $row['profit_brut'] : -$row['pierdere_bruta'];
                } elseif ($col == 'profit_pierdere_net') {
                    $v = ($row['profit_net'] > 0) ? $row['profit_net'] : -$row['pierdere_neta'];
                } else {
                    $v = $row[$col];
                }
                $vals[] = $v;
            }
            $chart_data[] = [
                'label' => $den,
                'data' => $vals
            ];
        }
        // Tabel comparativ între ani pentru indicatorii cheie - sub grafic
        echo '<h3>Comparații între ani</h3>';
        echo '<table border="1" cellpadding="5" cellspacing="0"><thead><tr><th>Indicator</th>';
        foreach ($bilanturi_cresc as $row) {
            echo '<th>'.$row['an'].'</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($comparatii as $col => $den) {
            echo '<tr><td>'.htmlspecialchars($den).'</td>';
            $vals = [];
            foreach ($bilanturi_cresc as $row) {
                if ($col == 'profit_pierdere_brut') {
                    $v = ($row['profit_brut'] > 0) ? $row['profit_brut'] : -$row['pierdere_bruta'];
                } elseif ($col == 'profit_pierdere_net') {
                    $v = ($row['profit_net'] > 0) ? $row['profit_net'] : -$row['pierdere_neta'];
                } else {
                    $v = $row[$col];
                }
                $vals[] = $v;
            }
            foreach ($vals as $i => $v) {
                $style = 'text-align:right;';
                $arrow = '';
                $pct = '';
                if ($i > 0 && $vals[$i-1] != 0) {
                    $diff = $v - $vals[$i-1];
                    $pctval = round(($diff / abs($vals[$i-1])) * 100, 1);
                    if ($diff > 0) {
                        $arrow = ' <span style="color:green">&#8593;</span>';
                        $pct = ' <span style="color:green">(+'.$pctval.'%)</span>';
                    } elseif ($diff < 0) {
                        $arrow = ' <span style="color:red">&#8595;</span>';
                        $pct = ' <span style="color:red">('.$pctval.'%)</span>';
                    }
                }
                $v_fmt = number_format($v, 0, ',', '.');
                echo '<td style="'.$style.'">'.$v_fmt.$arrow.$pct.'</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="callout alert">Nu există bilanțuri pentru acest CUI.</div>';
    }
}
        // Chart.js - grafic evoluție indicatori cheie
        echo '<h3>Grafic evoluție indicatori cheie</h3>';
        echo '<canvas id="bilantChart" width="900" height="400"></canvas>';
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
        echo '<script>';
        echo 'const ctx = document.getElementById("bilantChart").getContext("2d");';
        echo 'const bilantChart = new Chart(ctx, {';
        echo '    type: "line",';
        echo '    data: {';
        echo '        labels: '.json_encode($ani).',';
        echo '        datasets: [';
        foreach ($chart_data as $i => $ds) {
            $color = ["#007bff","#28a745","#dc3545","#ffc107","#6610f2"][$i%5];
            echo '{label: "'.addslashes($ds['label']).'", data: '.json_encode($ds['data']).', borderColor: "'.$color.'", backgroundColor: "'.$color.'", fill: false, tension: 0.2},';
        }
        echo '        ]
    },';
        echo '    options: {';
        echo '        responsive: true,';
        echo '        plugins: {legend: {position: "top"}},';
        echo '        scales: {y: {beginAtZero: false, ticks: {callback: function(value){return value.toLocaleString();}}}}';
        echo '    }';
        echo '});';
        echo '</script>';

        
        