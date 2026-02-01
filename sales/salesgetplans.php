<?php
// Ensure errors are not emitted as HTML to the caller
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

include '../settings.php';
include '../classes/common.php';

if (session_status() == PHP_SESSION_NONE) session_start();
$role = isset($_SESSION['function']) ? $_SESSION['function'] : '';
$uid = isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0;

$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';

header('Content-Type: application/json; charset=utf-8');

// Parse incoming ISO datetimes (FullCalendar sends e.g. 2026-01-26T00:00:00+02:00)
try {
    $sd = new DateTime($start);
    $ed = new DateTime($end);
    // Use only the date part for SQL DATE comparisons
    $startDate = $sd->format('Y-m-d');
    $endDate = $ed->format('Y-m-d');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing start/end parameters']);
    exit;
}

$startEsc = $startDate;
$endEsc = $endDate;
if (isset($conn) && $conn instanceof mysqli) {
    $startEsc = mysqli_real_escape_string($conn, $startEsc);
    $endEsc = mysqli_real_escape_string($conn, $endEsc);
} else {
    // fallback to basic escaping if mysqli not available
    $startEsc = addslashes($startEsc);
    $endEsc = addslashes($endEsc);
    // Log that mysqli connection is not available for debugging
    $logFile = __DIR__ . '/../logs/salesgetplans.log';
    @file_put_contents($logFile, date('c') . " - WARNING: mysqli connection missing, using addslashes()\n", FILE_APPEND);
}

// Use COALESCE to support both new `programare_data_inceput` and old `programare_data`
// Determine which date column to use: prefer `programare_data_inceput`, fall back to `programare_data` if present
$dateExpr = 'p.programare_data';
$colCheck = ezpub_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data_inceput'");
if ($colCheck && ezpub_num_rows($colCheck) > 0) {
    $dateExpr = 'p.programare_data_inceput';
} else {
    $colCheck2 = ezpub_query($conn, "SHOW COLUMNS FROM sales_programari LIKE 'programare_data'");
    if ($colCheck2 && ezpub_num_rows($colCheck2) > 0) {
        $dateExpr = 'p.programare_data';
    }
}

$where = "($dateExpr >= '$startEsc 00:00:00' AND $dateExpr <= '$endEsc 23:59:59')";
if ($role === 'USER') {
    $where .= " AND p.programare_user = " . intval($uid);
}

$query = "SELECT p.*, sp.prospect_denumire, $dateExpr AS effective_date FROM sales_programari p LEFT JOIN sales_prospecti sp ON p.programare_client = sp.prospect_ID WHERE $where ORDER BY $dateExpr";

// Log the final query for debugging (safe to remove in production)
$logFile = __DIR__ . '/../logs/salesgetplans.log';
@file_put_contents($logFile, date('c') . " - QUERY: " . $query . "\n", FILE_APPEND);

$result = ezpub_query($conn, $query);
$events = [];

if ($result === false) {
    // Log DB error for debugging
    $logFile = __DIR__ . '/../logs/salesgetplans.log';
    $err = isset($conn) ? mysqli_error($conn) : 'no-conn';
    @file_put_contents($logFile, date('c') . " - QUERY FAILED: $err\nQUERY: $query\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

try {
    while ($row = ezpub_fetch_array($result)) {
        // determine the actual date value (use effective_date from COALESCE)
        $dateValue = isset($row['effective_date']) ? $row['effective_date'] : null;
        if (empty($dateValue)) {
            // skip entries without a date
            continue;
        }
        $eventDate = new DateTime($dateValue);
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $className = ($eventDate < $today) ? 'past-event' : '';
    
    $title = isset($row['programare_obiectiv']) && $row['programare_obiectiv'] !== '' ? $row['programare_obiectiv'] : 'Vizită programată';
    if (!empty($row['prospect_denumire'])) {
        $title .= ' - ' . $row['prospect_denumire'];
    }
    if (!empty($row['programare_zona'])) {
        $title .= ' (' . $row['programare_zona'] . ')';
    }
    // Determine end time if available
        $startStr = $dateValue;
        $endStr = (isset($row['programare_data_sfarsit']) && $row['programare_data_sfarsit']) ? $row['programare_data_sfarsit'] : $startStr;
        // convert to ISO8601 for FullCalendar compatibility
        $startStr = str_replace(' ', 'T', $startStr);
        $endStr = str_replace(' ', 'T', $endStr);
    $events[] = [
        'id' => isset($row['programare_id']) ? $row['programare_id'] : (isset($row['programare_ID']) ? $row['programare_ID'] : null),
        'title' => $title,
        'start' => $startStr,
        'end' => $endStr,
        'className' => $className,
        'extendedProps' => [
            'client' => isset($row['programare_client']) ? $row['programare_client'] : null,
            'zona' => isset($row['programare_zona']) ? $row['programare_zona'] : null,
            'finalizata' => isset($row['programare_finalizata']) ? $row['programare_finalizata'] : 0,
            'vizita_id' => isset($row['programare_vizita_id']) ? $row['programare_vizita_id'] : null,
            'durata' => isset($row['programare_durata']) ? $row['programare_durata'] : null,
            'invite' => isset($row['programare_invite']) ? $row['programare_invite'] : 0,
            'invite_sent' => isset($row['programare_sent']) ? $row['programare_sent'] : null
        ]
    ];
}

} catch (Throwable $e) {
    $logFile = __DIR__ . '/../logs/salesgetplans.log';
    @file_put_contents($logFile, date('c') . " - EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
echo json_encode($events, JSON_UNESCAPED_UNICODE);
?>