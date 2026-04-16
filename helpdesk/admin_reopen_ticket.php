
<?php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';
if ($role !== 'ADMIN') 
    { http_response_code(403); echo 'Acces interzis'; exit; }

$ticketId = (int)($_GET['ticket_id'] ?? 0); if (!$ticketId) { echo 'ticket_id lipsă'; exit; }
$stmt = mysqli_prepare($conn, "UPDATE tickets SET ticket_status=6, ticket_lastupdated=NOW(), ticket_lastupdatedby=? WHERE ticket_id=?");
mysqli_stmt_bind_param($stmt, "ii", $ui, $ticketId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
log_action($ticketId, 'admin', $ui, 'REOPEN_TICKET');
header('Location: admin_tickets_all.php');
