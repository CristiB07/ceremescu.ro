<?php
// Funcție reutilizabilă pentru importul bilanțurilor ANAF
function import_bilanturi_anaf($cui, $conn) {
    $today = new DateTime();
    $limit_year = ( ($today->format('n') < 7) ? ($today->format('Y') - 2) : ($today->format('Y') - 1) );
    // Eliminăm limitarea la 2024, pentru a permite importul anilor noi automat
    $years = [];
    for ($y = $limit_year; $y >= 2017; $y--) {
        $years[] = $y;
    }
    // Selectează anii existenți în DB
    $query = "SELECT an FROM bilanturi WHERE cui = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $cui);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing[] = (int)$row['an'];
    }
    mysqli_stmt_close($stmt);
    $imported = 0;
    $skip_existing = (count($existing) > 0);
    foreach ($years as $an) {
        if ($skip_existing && in_array($an, $existing)) continue;
        $url = "https://webservicesp.anaf.ro/bilant?an=$an&cui=$cui";
        sleep(1); // Pauză 1 sec între interogări
        $response = @file_get_contents($url);
        // DEBUG: echo $response;
        if ($response === false) continue;
        $data = json_decode($response, true);
        // Detectăm structuri neconforme (ex: alt tip de societate)
        if (!$data || !isset($data['i']) || !is_array($data['i']) || count($data['i']) == 0 || empty($data['deni']) || empty($data['cui']) || empty($data['caen'])) {
            // Marchează status 3 pentru structură neconformă
            $update = mysqli_prepare($conn, "UPDATE clienti_date SET date_bilant=3 WHERE Client_CIF=?");
            mysqli_stmt_bind_param($update, "s", $cui);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
            // Opțional: loghează structura pentru analiză
            error_log('Structură bilanț neconformă pentru CUI ' . $cui . ': ' . $response);
            // Oprește importul pentru acest CUI
            return 0;
        }
        $map = [
            'Cifra de afaceri neta' => 'cifra_afaceri_net',
            'VENITURI TOTALE' => 'venituri_totale',
            'VENITURI IN AVANS' => 'venituri_in_avans',
            'CHELTUIELI TOTALE' => 'cheltuieli_totale',
            'CHELTUIELI IN AVANS' => 'cheltuieli_in_avans',
            'Profit brut' => 'profit_brut',
            'Pierdere bruta' => 'pierdere_bruta',
            'Profit net' => 'profit_net',
            'Pierdere  neta' => 'pierdere_neta',
            'PROVIZIOANE' => 'provizioane',
            'CAPITALURI - TOTAL, din care:' => 'capitaluri_total',
            'Capital subscris varsat' => 'capital_subscris',
            'Patrimoniul regiei' => 'patrimoniu_regie',
            'Numar mediu de salariati' => 'numar_salariati',
            'ACTIVE IMOBILIZATE - TOTAL ' => 'active_imobilizate',
            'ACTIVE CIRCULANTE - TOTAL, din care:' => 'active_circulante',
            'Stocuri' => 'stocuri',
            'Creante' => 'creante',
            'Casa si conturi la banci' => 'casa_banci',
            'Casa şi conturi la bănci' => 'casa_banci',
            'DATORII ' => 'datorii'
        ];
        $row = [
            'an' => $data['an'],
            'cui' => $data['cui'],
            'deni' => $data['deni'],
            'caen' => $data['caen'],
            'den_caen' => $data['den_caen']
        ];
        foreach ($map as $col) {
            $row[$col] = 0;
        }
        foreach ($data['i'] as $ind) {
            $den = trim($ind['val_den_indicator']);
            if (isset($map[$den])) {
                $row[$map[$den]] = $ind['val_indicator'];
            }
        }
        $cols = array_keys($row);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO bilanturi (' . implode(',', $cols) . ') VALUES (' . $placeholders . ') ON DUPLICATE KEY UPDATE ' . implode(',', array_map(function($c){return "$c=VALUES($c)";}, $cols));
        $stmt = mysqli_prepare($conn, $sql);
        $types = '';
        foreach ($cols as $col) {
            if (in_array($col, ['deni', 'den_caen'])) {
                $types .= 's';
            } else {
                $types .= 'i';
            }
        }
        $values = array_values($row);
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $imported++;
    }
    if ($imported == 0) {
        // Marchează cu 2 dacă nu există bilanț importat pentru niciun an
        $update = mysqli_prepare($conn, "UPDATE clienti_date SET date_bilant=2 WHERE Client_CIF=?");
        mysqli_stmt_bind_param($update, "s", $cui);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    } else {
        // Marchează cu 1 dacă s-a importat cel puțin un bilanț
        $update = mysqli_prepare($conn, "UPDATE clienti_date SET date_bilant=1 WHERE Client_CIF=?");
        mysqli_stmt_bind_param($update, "s", $cui);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
    }
    return $imported;
}
