
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
if ($role !== 'client') { http_response_code(403); echo 'Acces interzis'; exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Metodă invalidă'; exit; }
$ticketId = (int)($_POST['ticket_id'] ?? 0);
try {
    can_view_ticket($ticketId, $role, $ui);
    $stmt_s = mysqli_prepare($conn, "SELECT ticket_status FROM tickets WHERE ticket_id=?");
    mysqli_stmt_bind_param($stmt_s, "i", $ticketId);
    mysqli_stmt_execute($stmt_s);
    $row_s = mysqli_fetch_row(mysqli_stmt_get_result($stmt_s));
    mysqli_stmt_close($stmt_s);
    $st = (int)($row_s[0] ?? 0);
    if ($st !== 4) throw new RuntimeException('Ticketul nu este închis');
    $stmt_u = mysqli_prepare($conn, "UPDATE tickets SET ticket_status=5, ticket_lastupdated=NOW(), ticket_lastupdatedby=? WHERE ticket_id=?");
    mysqli_stmt_bind_param($stmt_u, "ii", $ui, $ticketId);
    mysqli_stmt_execute($stmt_u);
    mysqli_stmt_close($stmt_u);
    log_action($ticketId, 'client', $ui, 'REQUEST_REOPEN');
    header('Location: /client/view_ticket.php?ticket_id=' . $ticketId);
    exit;
} catch (Throwable $e) { http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
