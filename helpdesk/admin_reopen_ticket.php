
<?php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';
if ($role !== 'ADMIN') 
    { http_response_code(403); echo 'Acces interzis'; exit; }

$ticketId = (int)($_GET['ticket_id'] ?? 0); if (!$ticketId) { echo 'ticket_id lipsă'; exit; }
$u = $pdo->prepare("UPDATE tickets SET ticket_status=6, ticket_lastupdated=NOW(), ticket_lastupdatedby=:admin WHERE ticket_id=:tid"); $u->execute([':admin'=>$ui, ':tid'=>$ticketId]);
log_action($pdo, $ticketId, 'admin', $ui, 'REOPEN_TICKET');
header('Location: admin_tickets_all.php');
