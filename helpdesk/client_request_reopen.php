
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
if ($role !== 'client') { http_response_code(403); echo 'Acces interzis'; exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Metodă invalidă'; exit; }
$ticketId = (int)($_POST['ticket_id'] ?? 0);
try {
    can_view_ticket($pdo, $ticketId, $role, $ui);
    $s = $pdo->prepare("SELECT ticket_status FROM tickets WHERE ticket_id=:tid"); $s->execute([':tid'=>$ticketId]); $st = (int)$s->fetchColumn();
    if ($st !== 4) throw new RuntimeException('Ticketul nu este închis');
    $u = $pdo->prepare("UPDATE tickets SET ticket_status=5, ticket_lastupdated=NOW(), ticket_lastupdatedby=:ui WHERE ticket_id=:tid");
    $u->execute([':ui'=>$ui, ':tid'=>$ticketId]);
    log_action($pdo, $ticketId, 'client', $ui, 'REQUEST_REOPEN');
    header('Location: /client/view_ticket.php?ticket_id=' . $ticketId);
    exit;
} catch (Throwable $e) { http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
