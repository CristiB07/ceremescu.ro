<?php
// export assets to excel (in-memory download)
$strPageTitle = "Export Active";
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { 
    session_start(); 
}

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

// restrict to admin
$role = $_SESSION['clearence'] ?? '';
if ($role !== 'ADMIN') {
    die('<div class="callout alert">Unauthorized</div>');
}

// apply same filters as listing if present
$filter_category = trim($_GET['filter_category'] ?? '');
$filter_owner = isset($_GET['filter_proprietar']) ? intval($_GET['filter_proprietar']) : 0;
$where = [];
$types = '';
$params = [];
if ($filter_category !== '') {
    $where[] = 'b.bun_categorie = ?';
    $types .= 's';
    $params[] = $filter_category;
}
if ($filter_owner > 0) {
    $where[] = 'b.bun_proprietar = ?';
    $types .= 'i';
    $params[] = $filter_owner;
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// query assets with owner name
$stmt = $conn->prepare("SELECT b.*, CONCAT(u.utilizator_Prenume,' ',u.utilizator_Nume) AS proprietar_name
    FROM administrative_bunuri b
    LEFT JOIN date_utilizatori u ON b.bun_proprietar=u.utilizator_ID
    $where_sql
    ORDER BY b.bun_id DESC");
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// headers for excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="active_' . date('Y-m-d_His') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM
echo "\xEF\xBB\xBF";

// excel xml wrapper
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<!--[if gte mso 9]><xml>';
echo '<x:ExcelWorkbook>';
echo '<x:ExcelWorksheets>';
echo '<x:ExcelWorksheet>';
echo '<x:Name>Active</x:Name>';
echo '<x:WorksheetOptions>';
echo '<x:Print><x:ValidPrinterInfo/></x:Print>';
echo '</x:WorksheetOptions>';
echo '</x:ExcelWorksheet>';
echo '</x:ExcelWorksheets>';
echo '<x:ExcelWorkbook>';
echo '</xml><![endif]-->';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color:#4CAF50;color:#ffffff;font-weight:bold;">';
// map database fields to friendly headers
$fieldMap = [
    'bun_id' => 'ID',
    'bun_categorie' => 'Categorie',
    'bun_denumire' => 'Denumire',
    'bun_descriere' => 'Descriere',
    'bun_locatie' => 'Locație',
    'bun_adresa' => 'Adresă',
    'bun_proprietar' => 'Proprietar ID',
    'proprietar_name' => 'Proprietar',
    'bun_mobil' => 'Mobil (da/nu)',
    'bun_utilizat_extern' => 'Utilizat extern (da/nu)',
    'bun_date' => 'Informații (text)', // free‑text field, not a date

    'bun_licente' => 'Licențe',
    'bun_securitate' => 'Securitate',
    'bun_riscuri_asociate' => 'Riscuri asociate',
    'bun_nivel_risc' => 'Nivel risc',
    'bun_valoareC' => 'Valoare cost',
    'bun_valoareA' => 'Valoare amortizare',
    'bun_dataO' => 'Data achiziției',
    'bun_dataA' => 'Data amortizării',
    'bun_amortizare' => 'Amortizare'
];

$cols = [];
if ($row = $result->fetch_assoc()) {
    $cols = array_keys($row);
    // if we joined owner name, drop numeric id column to avoid confusion
    if (in_array('proprietar_name', $cols)) {
        $cols = array_filter($cols, fn($c) => $c !== 'bun_proprietar');
        // reindex so iteration is clean
        $cols = array_values($cols);
    }
    foreach ($cols as $c) {
        $label = $fieldMap[$c] ?? $c;
        echo '<th>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    // output first row
    echo '<tr>';
    foreach ($cols as $c) {
        echo '<td>' . htmlspecialchars($row[$c], ENT_QUOTES, 'UTF-8') . '</td>';
    }
    echo '</tr>';
    // rest rows
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        foreach ($cols as $c) {
            $val = $row[$c];
            // convert boolean fields to Da/Nu
            if ($c === 'bun_mobil' || $c === 'bun_utilizat_extern') {
                $val = ($val == '1' ? ($strYes ?? 'Da') : ($strNo ?? 'Nu'));
            }
            echo '<td>' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '</td>';
        }
        echo '</tr>';
    }
}
echo '</tbody>';
echo '</table>';

echo '</body>';
echo '</html>';

$stmt->close();
exit();
