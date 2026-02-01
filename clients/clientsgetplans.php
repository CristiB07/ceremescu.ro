<?php
include '../settings.php';
include '../classes/common.php';
if (session_status() == PHP_SESSION_NONE) session_start();
$role = isset($_SESSION['function']) ? $_SESSION['function'] : '';
$uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;

$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
header('Content-Type: application/json; charset=utf-8');
try {
    $sd = new DateTime($start);
    $ed = new DateTime($end);
    $startDate = $sd->format('Y-m-d');
    $endDate = $ed->format('Y-m-d');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$startEsc = mysqli_real_escape_string($conn, $startDate);
$endEsc = mysqli_real_escape_string($conn, $endDate);

$dateExpr = 'p.programare_data_inceput';
$where = "($dateExpr >= '$startEsc 00:00:00' AND $dateExpr <= '$endEsc 23:59:59')";
if ($role === 'USER') $where .= " AND p.programare_user = " . intval($uid);

$query = "SELECT p.*, cd.Client_Denumire, $dateExpr AS effective_date FROM clienti_programari p LEFT JOIN clienti_date cd ON p.programare_client = cd.ID_Client WHERE $where ORDER BY $dateExpr";
$res = ezpub_query($conn, $query);
$events = [];
while ($row = ezpub_fetch_array($res)) {
    $dateValue = $row['effective_date'];
    if (empty($dateValue)) continue;
    $startStr = str_replace(' ', 'T', $dateValue);
    $endStr = isset($row['programare_data_sfarsit']) && $row['programare_data_sfarsit'] ? str_replace(' ', 'T', $row['programare_data_sfarsit']) : $startStr;
    $title = $row['programare_obiectiv'] ?: 'Vizită programată';
    if (!empty($row['Client_Denumire'])) $title .= ' - ' . $row['Client_Denumire'];
    $events[] = [
        'id' => $row['programare_id'],
        'title' => $title,
        'start' => $startStr,
        'end' => $endStr,
        'extendedProps' => [
            'client' => $row['programare_client'],
            'zona' => $row['programare_zona'] ?? null,
        ]
    ];
}
echo json_encode($events, JSON_UNESCAPED_UNICODE);
?>
