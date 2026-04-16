
<?php
// includes/permissions.php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';

function can_view_ticket(int $ticketId, string $role, int $ui): array {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT ticket_id, ticket_createdby, ticket_asignedto FROM tickets WHERE ticket_id=?");
    mysqli_stmt_bind_param($stmt, "i", $ticketId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $t = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$t) throw new RuntimeException('Ticket inexistent');
    if ($role === 'ADMIN') return $t;
    if ($role === 'AGENT' && (int)$t['ticket_asignedto'] === $ui) return $t;
    if ($role === 'CLIENT' && (int)$t['ticket_createdby'] === $ui) return $t;
    throw new RuntimeException('Acces interzis la acest ticket');
}

function can_modify_ticket(int $ticketId, string $role, int $ui): array {
    return can_view_ticket($ticketId, $role, $ui);
}
?>
