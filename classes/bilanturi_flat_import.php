<?php
// Importă un JSON bilanț și inserează datele relevante în bilanturi_flat
include '../settings.php';
include '../classes/common.php';


// Funcție pentru normalizare denumiri indicatori
function normalize_indicator_name($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/\s+/', ' ', $str); // spații multiple
    $str = trim($str);
    // Elimină diacritice
    $diacritics = [
        'ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ş'=>'s','ț'=>'t','ţ'=>'t',
        'Ă'=>'a','Â'=>'a','Î'=>'i','Ș'=>'s','Ş'=>'s','Ț'=>'t','Ţ'=>'t',
        'é'=>'e','è'=>'e','ë'=>'e','ó'=>'o','ò'=>'o','ö'=>'o','ü'=>'u','ú'=>'u','ù'=>'u','ç'=>'c'
    ];
    $str = strtr($str, $diacritics);
    return $str;
}

$map = [
    normalize_indicator_name('Cifra de afaceri neta') => 'cifra_afaceri_net',
    normalize_indicator_name('VENITURI TOTALE') => 'venituri_totale',
    normalize_indicator_name('VENITURI IN AVANS') => 'venituri_in_avans',
    normalize_indicator_name('CHELTUIELI TOTALE') => 'cheltuieli_totale',
    normalize_indicator_name('CHELTUIELI IN AVANS') => 'cheltuieli_in_avans',
    normalize_indicator_name('Profit brut') => 'profit_brut',
    normalize_indicator_name('Pierdere bruta') => 'pierdere_bruta',
    normalize_indicator_name('Profit net') => 'profit_net',
    normalize_indicator_name('Pierdere  neta') => 'pierdere_neta',
    normalize_indicator_name('PROVIZIOANE') => 'provizioane',
    normalize_indicator_name('CAPITALURI - TOTAL, din care:') => 'capitaluri_total',
    normalize_indicator_name('Capital subscris varsat') => 'capital_subscris',
    normalize_indicator_name('Patrimoniul regiei') => 'patrimoniu_regie',
    normalize_indicator_name('Numar mediu de salariati') => 'numar_salariati',
    normalize_indicator_name('ACTIVE IMOBILIZATE - TOTAL ') => 'active_imobilizate',
    normalize_indicator_name('ACTIVE CIRCULANTE - TOTAL, din care:') => 'active_circulante',
    normalize_indicator_name('Stocuri') => 'stocuri',
    normalize_indicator_name('Creante') => 'creante',
    normalize_indicator_name('Casa si conturi la banci') => 'casa_banci',
    normalize_indicator_name('Casa şi conturi la bănci') => 'casa_banci', // variantă cu diacritice
    normalize_indicator_name('DATORII ') => 'datorii'
];

function import_bilant_flat($json_path, $conn) {
    global $map;
    $json = json_decode(file_get_contents($json_path), true);
    $data = [
        'an' => $json['an'],
        'cui' => $json['cui'],
        'deni' => $json['deni'],
        'caen' => $json['caen'],
        'den_caen' => $json['den_caen']
    ];
    foreach ($map as $col) {
        $data[$col] = 0;
    }
    foreach ($json['i'] as $ind) {
        $den = trim($ind['val_den_indicator']);
        $norm_den = normalize_indicator_name($den);
        if (isset($map[$norm_den])) {
            $data[$map[$norm_den]] = $ind['val_indicator'];
        }
    }
    $cols = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = 'INSERT INTO bilanturi_flat (' . implode(',', $cols) . ') VALUES (' . $placeholders . ')';
    $stmt = mysqli_prepare($conn, $sql);
    $types = 'iisssi'; // an, cui, deni, caen, den_caen, rest int
    $types .= str_repeat('i', count($cols) - 5);
    $values = array_values($data);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt);
}
// Exemplu de utilizare:
// import_bilant_flat('../json/bilant.json', $conn);
?>
