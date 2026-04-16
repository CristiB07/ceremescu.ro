
<?php

require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';

if ($role !== 'ADMIN') { http_response_code(403); echo 'Acces interzis'; exit; }
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $in_transaction = false;
    try {
        $replyId = (int)($_POST['reply_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if (!$replyId || !in_array($action,['approve','reject'],true)) throw new RuntimeException('Parametri invalizi');
        mysqli_begin_transaction($conn);
        $in_transaction = true;
        $stmt_r = mysqli_prepare($conn, "SELECT reply_id, reply_ticketid, reply_by_type, reply_validated FROM tickets_replies WHERE reply_id=? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_r, "i", $replyId);
        mysqli_stmt_execute($stmt_r);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_r));
        mysqli_stmt_close($stmt_r);
        if (!$row) throw new RuntimeException('Reply inexistent');
        if ($row['reply_by_type'] !== 'agent') throw new RuntimeException('Doar replies de la agent se validează');
        if ($row['reply_validated'] !== 'pending') throw new RuntimeException('Reply deja procesat');
        $newStatus = $action==='approve' ? 'approved' : 'rejected';
        $stmt_u = mysqli_prepare($conn, "UPDATE tickets_replies SET reply_validated=?, validated_by_ui=?, validated_at=NOW() WHERE reply_id=?");
        mysqli_stmt_bind_param($stmt_u, "sii", $newStatus, $ui, $replyId);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);
        if ($newStatus==='approved') {
            $tid = (int)$row['reply_ticketid'];
            $stmt_t = mysqli_prepare($conn, "UPDATE tickets SET ticket_status=2, ticket_lastupdated=NOW(), ticket_lastupdatedby=? WHERE ticket_id=?");
            mysqli_stmt_bind_param($stmt_t, "ii", $ui, $tid);
            mysqli_stmt_execute($stmt_t);
            mysqli_stmt_close($stmt_t);
        }
        log_action((int)$row['reply_ticketid'], 'admin', $ui, $newStatus==='approved'?'VALIDATE_REPLY':'REJECT_REPLY', ['reply_id'=>$replyId]);
        mysqli_commit($conn);
        $in_transaction = false;
        header('Location: admin_tickets_all.php'); exit;
    } catch (Throwable $e) { if ($in_transaction) mysqli_rollback($conn); http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
} else { echo 'Folosiți butoanele din lista de pending din pagina de tichete.'; }
