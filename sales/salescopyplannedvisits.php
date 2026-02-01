<?php
include '../settings.php';
include '../classes/common.php';

session_start();
$role = $_SESSION['function'];
$uid = $_SESSION['uid'];

$copyFromDate = $_POST['copyFromDate'];
$copyToDate = $_POST['copyToDate'];
$copyType = $_POST['copyType'];

header('Content-Type: application/json');

if (empty($copyFromDate) || empty($copyToDate) || empty($copyType)) {
    echo json_encode(['success' => false, 'message' => 'Date incomplete']);
    exit;
}

$fromDate = new DateTime($copyFromDate);
$toDate = new DateTime($copyToDate);
$today = new DateTime();
$today->setTime(0, 0, 0);

if ($toDate < $today) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot copia evenimente în trecut']);
    exit;
}

// Verificare sărbători pentru destinație
$toDateStr = $toDate->format('Y-m-d');
if (in_array($toDateStr, $holidays)) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot copia evenimente în zile de sărbătoare']);
    exit;
}

// Verificare weekend pentru destinație
$dayOfWeek = $toDate->format('D');
if (in_array($dayOfWeek, $skipdays)) {
    echo json_encode(['success' => false, 'message' => 'Nu se pot copia evenimente în weekend']);
    exit;
}

if ($copyType == 'week') {
    // Copiază întreaga săptămână
    $fromStart = clone $fromDate;
    $fromStart->modify('monday this week');
    $fromEnd = clone $fromStart;
    $fromEnd->modify('+6 days');

    $toStart = clone $toDate;
    $toStart->modify('monday this week');
} else {
    // Copiază zi
    $fromStart = $fromDate;
    $fromEnd = $fromDate;
    $toStart = $toDate;
}

$diff = $toStart->diff($fromStart)->days;

// Selectează evenimentele din intervalul sursă
$query = "SELECT * FROM sales_programari WHERE programare_data >= ? AND programare_data <= ?";
if ($role == 'USER') {
    $query .= " AND programare_user = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssi', $fromStart->format('Y-m-d 00:00:00'), $fromEnd->format('Y-m-d 23:59:59'), $uid);
} else {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $fromStart->format('Y-m-d 00:00:00'), $fromEnd->format('Y-m-d 23:59:59'));
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$inserted = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $newDate = new DateTime($row['programare_data']);
    $newDate->modify("+$diff days");
    
    // Verificare sărbători pentru data nouă
    $newDateStr = $newDate->format('Y-m-d');
    if (in_array($newDateStr, $holidays)) {
        continue; // Sari peste evenimentele în sărbători
    }
    
    // Verificare weekend pentru data nouă
    $newDayOfWeek = $newDate->format('D');
    if (in_array($newDayOfWeek, $skipdays)) {
        continue; // Sari peste evenimentele în weekend
    }
    
    $insertQuery = "INSERT INTO sales_programari (programare_user, programare_client, programare_data, programare_obiectiv, programare_finalizata, programare_vizita_id, programare_zona) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($insertStmt, 'iississ', $row['programare_user'], $row['programare_client'], $newDate->format('Y-m-d H:i:s'), $row['programare_obiectiv'], $row['programare_finalizata'], $row['programare_vizita_id'], $row['programare_zona']);
    if (mysqli_stmt_execute($insertStmt)) {
        $inserted++;
    }
    mysqli_stmt_close($insertStmt);
}

mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'message' => "$inserted evenimente copiate"]);
?>