<?php
include '../settings.php';
include '../classes/common.php';
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != 'Yes') {
    die(json_encode(['error' => 'Unauthorized']));
}
$uid = (int)$_SESSION['uid'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    die(json_encode(['error' => 'Invalid date']));
}

$query = "SELECT programare_id, programare_obiectiv, programare_client, programare_tipvizita, programare_detalii, programare_data_inceput, programare_durata, programare_data_sfarsit FROM clienti_programari WHERE programare_user='$uid' AND DATE(programare_data_inceput) = '$date' ORDER BY programare_data_inceput ASC";
$res = ezpub_query($conn, $query);
$out = [];
while ($r = ezpub_fetch_array($res)) {
    $out[] = [
        'id' => $r['programare_id'],
        'obiectiv' => $r['programare_obiectiv'],
        'client_id' => $r['programare_client'],
        'tipvizita' => $r['programare_tipvizita'],
        'detalii' => $r['programare_detalii'],
        'data' => $r['programare_data_inceput'],
        'programare_durata' => $r['programare_durata'],
        'programare_data_sfarsit' => $r['programare_data_sfarsit']
    ];
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>
