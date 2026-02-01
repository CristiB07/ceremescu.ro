<?php
include '../settings.php';
include '../classes/common.php';
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['userlogedin']) || $_SESSION['userlogedin'] != 'Yes') {
    die(json_encode(['error' => 'Unauthorized']));
}
$id = isset($_GET['eventId']) ? (int)$_GET['eventId'] : 0;
if ($id <= 0) die(json_encode(['error' => 'Invalid id']));

$stmt = mysqli_prepare($conn, "SELECT * FROM clienti_programari WHERE programare_id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($row, JSON_UNESCAPED_UNICODE);
?>
