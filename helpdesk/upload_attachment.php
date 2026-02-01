<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Metodă invalidă'; exit; }
$ticketId = (int)($_POST['ticket_id'] ?? 0);
$replyId  = (int)($_POST['reply_id'] ?? 0);
try {
    can_modify_ticket($pdo, $ticketId, $role, $ui);
    $r = $pdo->prepare("SELECT reply_id, reply_ticketid FROM tickets_replies WHERE reply_id=:rid"); 
    $r->execute([':rid'=>$replyId]); 
    $rr=$r->fetch();
    if (!$rr || (int)$rr['reply_ticketid'] !== $ticketId) throw new RuntimeException('Reply invalid');
    $maxSize=10*1024*1024; 
    $allowed=['application/pdf','image/jpeg','image/png','image/gif','text/plain','application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
    if (!isset($_FILES['attachments'])) throw new RuntimeException('Niciun fișier');
    $files=$_FILES['attachments']; $count=is_array($files['name'])?count($files['name']):0; 
    $baseDir = base_data_dir() . '/' . pad_ticket_id($ticketId); ensure_dir($baseDir); 
    $finfo=new finfo(FILEINFO_MIME_TYPE);
    $is_internal = ($role!=='CLIENT' && isset($_POST['is_internal'])) ? 1 : 0;
    for($i=0;$i<$count;$i++){
        $err=$files['error'][$i]; 
        if($err===UPLOAD_ERR_NO_FILE)continue; 
        if($err!==UPLOAD_ERR_OK) throw new RuntimeException('Eroare upload: ' . $err);  
        $tmp=$files['tmp_name'][$i];    
        $orig=$files['name'][$i]; 
        $size=$files['size'][$i]; if($size<=0||$size>$maxSize) throw new RuntimeException('Dimensiune invalidă');
        $mime=$finfo->file($tmp); if(!in_array($mime,$allowed,true)) throw new RuntimeException('Tip nepermis: ' . htmlspecialchars($mime));
        $ext=safe_ext_from_mime($mime); 
        $stored=generate_safe_name($ext);
        $dest=$baseDir.'/'.$stored; if(!move_uploaded_file($tmp,$dest)) throw new RuntimeException('Nu pot salva'); @chmod($dest,0600);
        insert_attachment($pdo, $ticketId, $replyId, [
            'stored'=>$stored,'name'=>$orig,'mime'=>$mime,'size'=>$size,'is_internal'=>$is_internal,
            'uploaded_by_type'=>$role,'uploaded_by_ui'=>$ui
        ]);
    }
    log_action($pdo, $ticketId, $role, $ui, 'ATTACH_UPLOAD', ['reply_id'=>$replyId, 'internal'=>$is_internal]);
    header('Location: ' . ($_POST['redirect'] ?? '/index.php'));
    exit;
} catch (Throwable $e) { http_response_code(400); echo 'Eroare: ' . $e->getMessage(); }
