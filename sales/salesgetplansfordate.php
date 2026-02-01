<?php
include '../settings.php';
include '../classes/common.php';

if (!isset($_SESSION)) {
    session_start();
}
// Ensure responses are JSON with correct charset so frontend can parse reliably
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != "Yes") {
    die(json_encode(['error' => 'Unauthorized']));
}

$uid = $_SESSION['uid'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validare dată
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    die(json_encode(['error' => 'Invalid date format']));
}

// determine which columns exist
$colStart = 'programare_data_inceput';
$colEnd = 'programare_data_sfarsit';
try {
    $r1 = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data_inceput'");
    if (!$r1 || mysqli_num_rows($r1) == 0) $colStart = 'programare_data';
    $r2 = mysqli_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data_sfarsit'");
    if (!$r2 || mysqli_num_rows($r2) == 0) $colEnd = null;
} catch (Exception $e) {
    $colStart = 'programare_data_inceput'; $colEnd = 'programare_data_sfarsit';
}

$sel = "p.programare_id, p.programare_obiectiv, p.programare_client, p.programare_tipvizita, p.programare_detalii, sp.prospect_ID, p.programare_durata";
if ($colStart) $sel .= ", p." . $colStart . " as data_inceput";
if ($colEnd) $sel .= ", p." . $colEnd . " as data_sfarsit";

$query = "SELECT $sel FROM sales_programari p LEFT JOIN sales_prospecti sp ON p.programare_client = sp.prospect_ID WHERE p.programare_user='" . intval($uid) . "' AND DATE(" . $colStart . ") = '" . mysqli_real_escape_string($conn, $date) . "' ORDER BY p." . $colStart . " ASC";
$result = ezpub_query($conn, $query);

$programari = [];
while ($row = ezpub_fetch_array($result)) {
    $start = $row['data_inceput'] ?? null;
    $end = $row['data_sfarsit'] ?? null;
    $dur = isset($row['programare_durata']) && $row['programare_durata']>0 ? intval($row['programare_durata']) : null;
    // if no explicit end but duration present, compute end
    if (!$end && $dur && $start) {
        $dt = new DateTime($start);
        $dt->add(new DateInterval('PT' . $dur . 'M'));
        $end = $dt->format('Y-m-d H:i:s');
    }
    // fallback: if no end and no duration, assume +60 minutes
    if (!$end && $start) {
        $dt = new DateTime($start);
        $dt->add(new DateInterval('PT60M'));
        $end = $dt->format('Y-m-d H:i:s');
    }

    $programari[] = [
        'id' => $row['programare_id'],
        'obiectiv' => $row['programare_obiectiv'],
        'client_id' => $row['prospect_ID'] ?: $row['programare_client'],
        'tipvizita' => $row['programare_tipvizita'],
        'detalii' => $row['programare_detalii'],
        'data' => $start,
        'data_sfarsit' => $end,
        'durata' => $dur
    ];
}

echo json_encode($programari);
?>