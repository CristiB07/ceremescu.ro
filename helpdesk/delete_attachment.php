
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Metodă invalidă'; exit; }
$attId = (int)($_POST['attachment_id'] ?? 0);
try {
    $stmt_a = mysqli_prepare($conn, "SELECT a.*, t.ticket_asignedto, t.ticket_createdby FROM tickets_attachments a JOIN tickets t ON t.ticket_id=a.ticket_id WHERE a.attachment_id=?");
    mysqli_stmt_bind_param($stmt_a, "i", $attId);
    mysqli_stmt_execute($stmt_a);
    $a = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_a));
    mysqli_stmt_close($stmt_a);
    if(!$a) throw new RuntimeException('Atașament inexistent');
    if ($role==='CLIENT') throw new RuntimeException('Drepturi insuficiente');
    if ($role==='AGENT' && (int)$a['ticket_asignedto'] !== $ui) throw new RuntimeException('Acces interzis');
    $baseDir = $hddpath . 'tickete/' . str_pad((string)$a['ticket_id'],6,'0',STR_PAD_LEFT);
    $path = $baseDir . '/' . $a['stored'];
    $stmt_d = mysqli_prepare($conn, "DELETE FROM tickets_attachments WHERE attachment_id=?");
    mysqli_stmt_bind_param($stmt_d, "i", $attId);
    mysqli_stmt_execute($stmt_d);
    mysqli_stmt_close($stmt_d);
    if (is_file($path)) @unlink($path);
    log_action((int)$a['ticket_id'], $role, $ui, 'ATTACH_DELETE', ['attachment_id'=>$attId]);
    header('Location: ' . ($_POST['redirect'] ?? '/index.php'));
    exit;
} catch (Throwable $e) { http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
