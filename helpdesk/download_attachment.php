
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
$attId = (int)($_GET['attachment_id'] ?? 0);
try {
    $s = $pdo->prepare("SELECT a.*, r.reply_by_type, r.reply_validated FROM tickets_attachments a JOIN tickets_replies r ON r.reply_id=a.reply_id WHERE a.attachment_id=:aid");
    $s->execute([':aid'=>$attId]); $a=$s->fetch(); if(!$a) throw new RuntimeException('Atașament inexistent');
    can_view_ticket($pdo, (int)$a['ticket_id'], $role, $ui);
    if ($role==='CLIENT') {
        if ((int)$a['is_internal']===1) throw new RuntimeException('Acces interzis la atașament intern');
        if ($a['reply_by_type']==='AGENT' && $a['reply_validated']!=='approved') throw new RuntimeException('Atașament nevalidat încă');
    }
    $path = $hddpath . 'tickete/' . str_pad((string)$a['ticket_id'],6,'0',STR_PAD_LEFT) . '/' . $a['stored'];
    if (!is_file($path)) throw new RuntimeException('Fișier lipsă pe disc');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
    log_action($pdo, (int)$a['ticket_id'], $role, $ui, 'ATTACH_DOWNLOAD', ['attachment_id'=>$attId]);
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . basename($a['name'] ?? $a['stored']) . '"');
    header('X-Content-Type-Options: nosniff');
    $fp=fopen($path,'rb'); if(!$fp) throw new RuntimeException('Nu pot deschide fișierul');
    while(!feof($fp)){ echo fread($fp,8192); flush(); }
    fclose($fp); exit;
} catch (Throwable $e) { http_response_code(404); echo 'Eroare: ' . $e->getMessage(); }
