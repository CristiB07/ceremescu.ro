<?php
include '../settings.php';
include '../classes/common.php';

session_start();
$role = $_SESSION['function'];
$uid = $_SESSION['uid'];

$eventId = $_GET['eventId'];

$query = "SELECT * FROM sales_programari WHERE programare_id=?";
if ($role == 'USER') {
    $query .= " AND programare_user=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $eventId, $uid);
} else {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $eventId);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
if ($row) {
    echo json_encode($row);
} else {
    echo json_encode([]);
}

mysqli_stmt_close($stmt);
?>