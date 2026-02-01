<?php
include '../settings.php';
include '../classes/common.php';
if (!isset($_SESSION)) session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != 'Yes') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}
$from = isset($_POST['copyFromDate']) ? $_POST['copyFromDate'] : '';
$to = isset($_POST['copyToDate']) ? $_POST['copyToDate'] : '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    echo json_encode(['success'=>false,'message'=>'Invalid dates']); exit;
}

$query = "SELECT * FROM clienti_programari WHERE DATE(programare_data_inceput) >= '$from' AND DATE(programare_data_inceput) <= '$to'";
$res = ezpub_query($conn, $query);
$count = 0;
while ($r = ezpub_fetch_array($res)) {
    $stmt = mysqli_prepare($conn, "INSERT INTO clienti_programari (programare_user, programare_client, programare_data_inceput, programare_obiectiv, programare_finalizata, programare_zona, programare_tipvizita, programare_detalii, programare_durata, programare_invite_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iississsis", $r['programare_user'], $r['programare_client'], $r['programare_data_inceput'], $r['programare_obiectiv'], $r['programare_finalizata'], $r['programare_zona'], $r['programare_tipvizita'], $r['programare_detalii'], $r['programare_durata'], $r['programare_invite_email']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $count++;
}
echo json_encode(['success'=>true,'copied'=>$count]);
?>
