<?php
/*
 * PANEL - Analiză Financiară
 *
 * Integrare: includeți în interiorul unui <div class="tabs-panel"> din contextul apelant.
 *
 * Dependențe: $conn (conexiune DB), $cui_numeric SAU $bilanturi pre-populat.
 * Dacă $bilanturi nu este setat, panelul îl extrage automat din DB pe baza $cui_numeric.
 */

// Auto-fetch $bilanturi dacă nu a fost pre-populat de contextul apelant
if (!isset($bilanturi) || !is_array($bilanturi)) {
    $bilanturi = [];
    if (!empty($cui_numeric) && is_numeric($cui_numeric)) {
        $stmt_bil = mysqli_prepare($conn, "SELECT * FROM bilanturi WHERE cui = ? ORDER BY an DESC LIMIT 50");
        mysqli_stmt_bind_param($stmt_bil, "s", $cui_numeric);
        mysqli_stmt_execute($stmt_bil);
        $res_bil = mysqli_stmt_get_result($stmt_bil);
        while ($b = $res_bil->fetch_assoc()) {
            $bilanturi[] = $b;
        }
        mysqli_stmt_close($stmt_bil);
    }
}
?>
<?php if (count($bilanturi) == 0): ?>
    <div class="callout alert">Nu există date de bilanț pentru a calcula indicatorii financiari.</div>
<?php else:
    // ─── Pregătire date (ordonate crescător după an pentru grafice) ───────────
    $bil_asc = array_reverse($bilanturi); // $bilanturi e DESC din query

    // ─── Funcții helper ───────────────────────────────────────────────────────
    function div_safe($a, $b, $decimals = 4) {
        if ($b == 0 || $b === null) return null;
        return round($a / $b, $decimals);
    }

    function fmt_pct($v, $decimals = 2) {
        if ($v === null) return '<span class="fi-na">N/A</span>';
        return number_format($v, $decimals, ',', '.') . '%';
    }

    function fmt_ratio($v, $decimals = 2) {
        if ($v === null) return '<span class="fi-na">N/A</span>';
        return number_format($v, $decimals, ',', '.');
    }

    function fmt_ron($v) {
        if ($v === null || $v === '' || $v == 0) return '<span class="fi-na">-</span>';
        return number_format((int)$v, 0, ',', '.') . ' RON';
    }

    // Semafor: returnează clasa CSS în funcție de valoare și praguri
    // $dir: 'up' = mai mare e mai bine, 'down' = mai mic e mai bine
    function semafor($v, $prag_bun, $prag_ok, $dir = 'up') {
        if ($v === null) return 'fi-neutral';
        if ($dir === 'up') {
            if ($v >= $prag_bun) return 'fi-bun';
            if ($v >= $prag_ok)  return 'fi-ok';
            return 'fi-rau';
        } else {
            if ($v <= $prag_bun) return 'fi-bun';
            if ($v <= $prag_ok)  return 'fi-ok';
            return 'fi-rau';
        }
    }

    // ─── Calcul indicatori per an ─────────────────────────────────────────────
    $indicatori = [];
    foreach ($bil_asc as $b) {
        $an  = (int)$b['an'];
        $ca  = isset($b['cifra_afaceri_net'])  ? (int)$b['cifra_afaceri_net']  : 0;
        $vt  = isset($b['venituri_totale'])     ? (int)$b['venituri_totale']    : 0;
        $ct  = isset($b['cheltuieli_totale'])   ? (int)$b['cheltuieli_totale']  : 0;
        $pb  = isset($b['profit_brut'])         ? (int)$b['profit_brut']        : 0;
        $pn  = isset($b['profit_net'])          ? (int)$b['profit_net']         : 0;
        $pib = isset($b['pierdere_bruta'])      ? (int)$b['pierdere_bruta']     : 0;
        $pin = isset($b['pierdere_neta'])       ? (int)$b['pierdere_neta']      : 0;
        $cap = isset($b['capitaluri_total'])    ? (int)$b['capitaluri_total']   : 0;
        $ai  = isset($b['active_imobilizate'])  ? (int)$b['active_imobilizate'] : 0;
        $ac  = isset($b['active_circulante'])   ? (int)$b['active_circulante']  : 0;
        $stoc= isset($b['stocuri'])             ? (int)$b['stocuri']            : 0;
        $cr  = isset($b['creante'])             ? (int)$b['creante']            : 0;
        $cb  = isset($b['casa_banci'])          ? (int)$b['casa_banci']         : 0;
        $dat = isset($b['datorii'])             ? (int)$b['datorii']            : 0;
        $sal = isset($b['numar_salariati'])     ? (int)$b['numar_salariati']    : 0;

        $total_active = $ai + $ac;
        // Profit/pierdere net: pozitiv = profit, negativ = pierdere
        $rezultat_net = ($pn != 0) ? $pn : ($pin != 0 ? -$pin : 0);
        $rezultat_brut = ($pb != 0) ? $pb : ($pib != 0 ? -$pib : 0);

        $indicatori[$an] = [
            'an'  => $an,
            // Profitabilitate
            'marja_profit_net'   => div_safe($rezultat_net, $ca) * 100,
            'marja_profit_brut'  => div_safe($rezultat_brut, $ca) * 100,
            'roe'                => div_safe($rezultat_net, $cap) * 100,
            'roa'                => div_safe($rezultat_net, $total_active) * 100,
            // Lichiditate
            'lich_generala'      => div_safe($ac, $dat),
            'lich_rapida'        => div_safe($ac - $stoc, $dat),
            'lich_imediata'      => div_safe($cb, $dat),
            // Îndatorare
            'rata_indatorarii'   => div_safe($dat, $cap),
            'grad_indatorare'    => div_safe($dat, $total_active) * 100,
            // Eficiență
            'rata_cheltuieli'    => div_safe($ct, $vt),
            'prod_salariat'      => ($sal > 0) ? div_safe($ca, $sal) : null,
            'ven_salariat'       => ($sal > 0) ? div_safe($vt, $sal) : null,
            // Structura active
            'pond_ai'            => div_safe($ai, $total_active) * 100,
            'pond_ac'            => div_safe($ac, $total_active) * 100,
            'pond_stoc'          => div_safe($stoc, $ac) * 100,
            'pond_creante'       => div_safe($cr, $ac) * 100,
            'pond_cb'            => div_safe($cb, $ac) * 100,
            // Raw pentru grafice
            '_rezultat_net'      => $rezultat_net,
            '_ca'                => $ca,
            '_sal'               => $sal,
        ];
    }

    $ani_json         = json_encode(array_keys($indicatori));
    $indicatori_json  = json_encode(array_values($indicatori));
?>

<style>
/* ── Semafor ──────────────────────────────────────────────────────────── */
.fi-bun     { color: #2d7d46; font-weight: 600; }
.fi-ok      { color: #b45309; font-weight: 600; }
.fi-rau     { color: #b91c1c; font-weight: 600; }
.fi-neutral { color: #6b7280; }
.fi-na      { color: #9ca3af; font-style: italic; font-size: 0.85em; }

/* ── Secțiuni ─────────────────────────────────────────────────────────── */
.fi-section {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.3rem;
    border-bottom: 2px solid #e5e7eb;
    font-size: 1rem;
    font-weight: 700;
    color: #1e3a5f;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

/* ── Badge semafor lângă titlu ────────────────────────────────────────── */
.fi-badge {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-right: 4px;
    vertical-align: middle;
}
.fi-badge-bun  { background: #2d7d46; }
.fi-badge-ok   { background: #b45309; }
.fi-badge-rau  { background: #b91c1c; }

/* ── Tabel indicatori ─────────────────────────────────────────────────── */
.fi-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    margin-bottom: 1rem;
}
.fi-table th {
    background: #f1f5f9;
    color: #374151;
    padding: 6px 10px;
    text-align: right;
    font-weight: 600;
    border-bottom: 2px solid #cbd5e1;
    white-space: nowrap;
}
.fi-table th:first-child { text-align: left; }
.fi-table td {
    padding: 5px 10px;
    text-align: right;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}
.fi-table td:first-child { text-align: left; font-weight: 500; color: #374151; }
.fi-table tr:hover td { background: #f8fafc; }

/* ── Legendă semafor ──────────────────────────────────────────────────── */
.fi-legenda {
    font-size: 0.78rem;
    color: #6b7280;
    margin-bottom: 1rem;
    display: flex;
    gap: 1.2em;
    flex-wrap: wrap;
}
.fi-legenda span { display: flex; align-items: center; gap: 4px; }

/* ── Canvas grafice ───────────────────────────────────────────────────── */
.fi-chart-wrap {
    position: relative;
    margin: 0.5rem 0 1.5rem 0;
}
</style>

<p style="color:#6b7280; font-size:0.88rem; margin-bottom:1rem;">
    Indicatorii sunt calculați din datele de bilanț disponibile (<?php echo min(array_keys($indicatori)); ?>–<?php echo max(array_keys($indicatori)); ?>).
    Valorile monetare sunt exprimate în RON.
</p>

<div class="fi-legenda">
    <span><span class="fi-badge fi-badge-bun"></span> Bun</span>
    <span><span class="fi-badge fi-badge-ok"></span> Acceptabil</span>
    <span><span class="fi-badge fi-badge-rau"></span> Atenție</span>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     1. PROFITABILITATE
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="fi-section">Profitabilitate</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table class="fi-table">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <?php foreach ($indicatori as $ind): ?>
                    <th><?php echo $ind['an']; ?></th>
                    <?php endforeach; ?>
                    <th>Prag bun</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td title="Profit net / Cifră de afaceri × 100">Marjă profit net (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['marja_profit_net'], 10, 3, 'up'); ?>">
                        <?php echo fmt_pct($ind['marja_profit_net']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 10%</td>
                </tr>
                <tr>
                    <td title="Profit brut / Cifră de afaceri × 100">Marjă profit brut (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['marja_profit_brut'], 15, 5, 'up'); ?>">
                        <?php echo fmt_pct($ind['marja_profit_brut']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 15%</td>
                </tr>
                <tr>
                    <td title="Profit net / Capitaluri totale × 100 — Rentabilitatea capitalurilor proprii">ROE (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['roe'], 15, 5, 'up'); ?>">
                        <?php echo fmt_pct($ind['roe']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 15%</td>
                </tr>
                <tr>
                    <td title="Profit net / Active totale × 100 — Rentabilitatea activelor">ROA (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['roa'], 8, 2, 'up'); ?>">
                        <?php echo fmt_pct($ind['roa']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 8%</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-12 cell fi-chart-wrap">
        <canvas id="chartProfitabilitate" height="120"></canvas>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     2. LICHIDITATE
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="fi-section">Lichiditate</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table class="fi-table">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <?php foreach ($indicatori as $ind): ?>
                    <th><?php echo $ind['an']; ?></th>
                    <?php endforeach; ?>
                    <th>Prag bun</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td title="Active circulante / Datorii — capacitatea de a acoperi datoriile pe termen scurt">Lichiditate generală</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['lich_generala'], 2, 1, 'up'); ?>">
                        <?php echo fmt_ratio($ind['lich_generala']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 2,0</td>
                </tr>
                <tr>
                    <td title="(Active circulante - Stocuri) / Datorii">Lichiditate rapidă</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['lich_rapida'], 1, 0.7, 'up'); ?>">
                        <?php echo fmt_ratio($ind['lich_rapida']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 1,0</td>
                </tr>
                <tr>
                    <td title="Casa și conturi la bănci / Datorii">Lichiditate imediată</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['lich_imediata'], 0.5, 0.2, 'up'); ?>">
                        <?php echo fmt_ratio($ind['lich_imediata']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&gt; 0,5</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-12 cell fi-chart-wrap">
        <canvas id="chartLichiditate" height="120"></canvas>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     3. ÎNDATORARE
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="fi-section">Îndatorare</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table class="fi-table">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <?php foreach ($indicatori as $ind): ?>
                    <th><?php echo $ind['an']; ?></th>
                    <?php endforeach; ?>
                    <th>Prag bun</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td title="Datorii / Capitaluri totale — optim sub 1">Rata îndatorării</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['rata_indatorarii'], 0.5, 1, 'down'); ?>">
                        <?php echo fmt_ratio($ind['rata_indatorarii']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&lt; 0,5</td>
                </tr>
                <tr>
                    <td title="Datorii / Active totale × 100">Grad îndatorare active (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['grad_indatorare'], 30, 60, 'down'); ?>">
                        <?php echo fmt_pct($ind['grad_indatorare']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&lt; 30%</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-12 cell fi-chart-wrap">
        <canvas id="chartIndatorare" height="100"></canvas>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     4. EFICIENȚĂ OPERAȚIONALĂ
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="fi-section">Eficiență operațională</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table class="fi-table">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <?php foreach ($indicatori as $ind): ?>
                    <th><?php echo $ind['an']; ?></th>
                    <?php endforeach; ?>
                    <th>Prag bun</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td title="Cheltuieli totale / Venituri totale — optim sub 0,9">Rata cheltuielilor</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td class="<?php echo semafor($ind['rata_cheltuieli'], 0.8, 0.95, 'down'); ?>">
                        <?php echo fmt_ratio($ind['rata_cheltuieli']); ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">&lt; 0,80</td>
                </tr>
                <tr>
                    <td title="Cifră de afaceri / Număr salariați">Productivitate / salariat (RON)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_ron($ind['prod_salariat']); ?></td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">—</td>
                </tr>
                <tr>
                    <td title="Venituri totale / Număr salariați">Venituri / salariat (RON)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_ron($ind['ven_salariat']); ?></td>
                    <?php endforeach; ?>
                    <td class="fi-neutral">—</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-12 cell fi-chart-wrap">
        <canvas id="chartEficienta" height="100"></canvas>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     5. STRUCTURA ACTIVELOR
     ═══════════════════════════════════════════════════════════════════════ -->
<div class="fi-section">Structura activelor</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table class="fi-table">
            <thead>
                <tr>
                    <th>Indicator</th>
                    <?php foreach ($indicatori as $ind): ?>
                    <th><?php echo $ind['an']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Active imobilizate / Total active (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_pct($ind['pond_ai']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Active circulante / Total active (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_pct($ind['pond_ac']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Stocuri / Active circulante (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_pct($ind['pond_stoc']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Creanțe / Active circulante (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_pct($ind['pond_creante']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Casă și bănci / Active circulante (%)</td>
                    <?php foreach ($indicatori as $ind): ?>
                    <td><?php echo fmt_pct($ind['pond_cb']); ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="large-12 cell fi-chart-wrap">
        <canvas id="chartStructura" height="140"></canvas>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     GRAFICE - Chart.js (reutilizăm instanța deja încărcată din panel3)
     ═══════════════════════════════════════════════════════════════════════ -->
<script>
(function() {
    const ani        = <?php echo $ani_json; ?>;
    const indicatori = <?php echo $indicatori_json; ?>;

    const get = (key) => indicatori.map(r => r[key] !== null ? parseFloat(r[key]) : null);

    const optBase = {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { grid: { color: '#f1f5f9' } },
            y: { grid: { color: '#f1f5f9' },
                 ticks: { callback: v => v !== null ? v.toLocaleString('ro-RO') : '' } }
        }
    };

    // ── 1. Profitabilitate ──────────────────────────────────────────────
    new Chart(document.getElementById('chartProfitabilitate'), {
        type: 'line',
        data: {
            labels: ani,
            datasets: [
                { label: 'Marjă profit net (%)',  data: get('marja_profit_net'),  borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)', fill: true, tension: 0.3, spanGaps: true },
                { label: 'Marjă profit brut (%)', data: get('marja_profit_brut'), borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.08)',  fill: true, tension: 0.3, spanGaps: true },
                { label: 'ROE (%)',                data: get('roe'),               borderColor: '#9333ea', backgroundColor: 'rgba(147,51,234,0.08)', fill: false, tension: 0.3, spanGaps: true, borderDash: [4,3] },
                { label: 'ROA (%)',                data: get('roa'),               borderColor: '#ea580c', backgroundColor: 'rgba(234,88,12,0.08)',  fill: false, tension: 0.3, spanGaps: true, borderDash: [4,3] },
            ]
        },
        options: {
            ...optBase,
            plugins: { ...optBase.plugins, tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y !== null ? ctx.parsed.y.toFixed(2) + '%' : 'N/A') } } }
        }
    });

    // ── 2. Lichiditate ─────────────────────────────────────────────────
    new Chart(document.getElementById('chartLichiditate'), {
        type: 'line',
        data: {
            labels: ani,
            datasets: [
                { label: 'Lichiditate generală', data: get('lich_generala'), borderColor: '#0891b2', tension: 0.3, spanGaps: true },
                { label: 'Lichiditate rapidă',   data: get('lich_rapida'),   borderColor: '#d97706', tension: 0.3, spanGaps: true },
                { label: 'Lichiditate imediată', data: get('lich_imediata'), borderColor: '#dc2626', tension: 0.3, spanGaps: true },
            ]
        },
        options: {
            ...optBase,
            plugins: {
                ...optBase.plugins,
                annotation: { /* praguri vizuale dacă plugin-ul e disponibil */ },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y !== null ? ctx.parsed.y.toFixed(2) : 'N/A') } }
            }
        }
    });

    // ── 3. Îndatorare ──────────────────────────────────────────────────
    new Chart(document.getElementById('chartIndatorare'), {
        type: 'bar',
        data: {
            labels: ani,
            datasets: [
                { label: 'Rata îndatorării',       data: get('rata_indatorarii'), backgroundColor: 'rgba(220,38,38,0.7)', yAxisID: 'y' },
                { label: 'Grad îndatorare active (%)', data: get('grad_indatorare'), backgroundColor: 'rgba(234,88,12,0.5)', yAxisID: 'y2', type: 'line', borderColor: '#ea580c', tension: 0.3, spanGaps: true },
            ]
        },
        options: {
            ...optBase,
            scales: {
                x:  { grid: { color: '#f1f5f9' } },
                y:  { grid: { color: '#f1f5f9' }, title: { display: true, text: 'Rată' }, position: 'left' },
                y2: { grid: { drawOnChartArea: false }, title: { display: true, text: '%' }, position: 'right' }
            }
        }
    });

    // ── 4. Eficiență ───────────────────────────────────────────────────
    new Chart(document.getElementById('chartEficienta'), {
        type: 'line',
        data: {
            labels: ani,
            datasets: [
                { label: 'Rata cheltuielilor', data: get('rata_cheltuieli'), borderColor: '#7c3aed', tension: 0.3, spanGaps: true, yAxisID: 'y' },
                { label: 'Productivitate / salariat (RON)', data: get('prod_salariat'), borderColor: '#059669', tension: 0.3, spanGaps: true, yAxisID: 'y2', borderDash: [5,3] },
            ]
        },
        options: {
            ...optBase,
            scales: {
                x:  { grid: { color: '#f1f5f9' } },
                y:  { position: 'left',  title: { display: true, text: 'Rată cheltuieli' }, grid: { color: '#f1f5f9' } },
                y2: { position: 'right', title: { display: true, text: 'RON / salariat' }, grid: { drawOnChartArea: false },
                      ticks: { callback: v => v !== null ? v.toLocaleString('ro-RO') : '' } }
            }
        }
    });

    // ── 5. Structura activelor (stacked bar) ───────────────────────────
    new Chart(document.getElementById('chartStructura'), {
        type: 'bar',
        data: {
            labels: ani,
            datasets: [
                { label: 'Active imobilizate (%)', data: get('pond_ai'), backgroundColor: 'rgba(37,99,235,0.75)' },
                { label: 'Stocuri (%)',            data: get('pond_stoc').map((v, i) => v !== null ? v * (get('pond_ac')[i] / 100) : null), backgroundColor: 'rgba(16,185,129,0.75)' },
                { label: 'Creanțe (%)',            data: get('pond_creante').map((v, i) => v !== null ? v * (get('pond_ac')[i] / 100) : null), backgroundColor: 'rgba(245,158,11,0.75)' },
                { label: 'Casă și bănci (%)',      data: get('pond_cb').map((v, i) => v !== null ? v * (get('pond_ac')[i] / 100) : null), backgroundColor: 'rgba(16,185,129,0.35)' },
            ]
        },
        options: {
            ...optBase,
            scales: {
                x: { stacked: true, grid: { color: '#f1f5f9' } },
                y: { stacked: true, max: 100, grid: { color: '#f1f5f9' },
                     ticks: { callback: v => v + '%' } }
            },
            plugins: {
                ...optBase.plugins,
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y !== null ? ctx.parsed.y.toFixed(1) + '%' : 'N/A') } }
            }
        }
    });
})();
</script>

<?php endif; ?>
