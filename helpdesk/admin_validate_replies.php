
<?php

require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';

if ($role !== 'ADMIN') { http_response_code(403); echo 'Acces interzis'; exit; }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    try {
        $replyId = (int)($_POST['reply_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if (!$replyId || !in_array($action,['approve','reject'],true)) throw new RuntimeException('Parametri invalizi');
        $r = $pdo->prepare("SELECT reply_id, reply_ticketid, reply_by_type, reply_validated FROM tickets_replies WHERE reply_id=:rid FOR UPDATE");
        $pdo->beginTransaction();
        $r->execute([':rid'=>$replyId]); $row=$r->fetch();
        if (!$row) throw new RuntimeException('Reply inexistent');
        if ($row['reply_by_type'] !== 'agent') throw new RuntimeException('Doar replies de la agent se validează');
        if ($row['reply_validated'] !== 'pending') throw new RuntimeException('Reply deja procesat');
        $newStatus = $action==='approve' ? 'approved' : 'rejected';
        $u = $pdo->prepare("UPDATE tickets_replies SET reply_validated=:vs, validated_by_ui=:admin, validated_at=NOW() WHERE reply_id=:rid");
        $u->execute([':vs'=>$newStatus, ':admin'=>$ui, ':rid'=>$replyId]);
        if ($newStatus==='approved') {
            $t = $pdo->prepare("UPDATE tickets SET ticket_status=2, ticket_lastupdated=NOW(), ticket_lastupdatedby=:admin WHERE ticket_id=:tid"); $t->execute([':admin'=>$ui, ':tid'=>(int)$row['reply_ticketid']]);
        }
        log_action($pdo, (int)$row['reply_ticketid'], 'admin', $ui, $newStatus==='approved'?'VALIDATE_REPLY':'REJECT_REPLY', ['reply_id'=>$replyId]);
        $pdo->commit(); header('Location: admin_tickets_all.php'); exit;
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
} else { echo 'Folosiți butoanele din lista de pending din pagina de tichete.'; }
