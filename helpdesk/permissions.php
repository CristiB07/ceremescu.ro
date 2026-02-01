
<?php
// includes/permissions.php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';

function can_view_ticket(PDO $pdo, int $ticketId, string $role, int $ui): array {
    $s = $pdo->prepare("SELECT ticket_id, ticket_createdby, ticket_asignedto FROM tickets WHERE ticket_id=:tid");
    $s->execute([':tid'=>$ticketId]);
    $t = $s->fetch();
    if (!$t) throw new RuntimeException('Ticket inexistent');
    if ($role === 'ADMIN') return $t;
    if ($role === 'AGENT' && (int)$t['ticket_asignedto'] === $ui) return $t;
    if ($role === 'CLIENT' && (int)$t['ticket_createdby'] === $ui) return $t;
    throw new RuntimeException('Acces interzis la acest ticket');
}

function can_modify_ticket(PDO $pdo, int $ticketId, string $role, int $ui): array {
    return can_view_ticket($pdo, $ticketId, $role, $ui);
}
?>
