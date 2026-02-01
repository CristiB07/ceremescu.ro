
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mail.php';

if ($role !== 'CLIENT') { http_response_code(403); echo 'Acces interzis'; exit; }
$strPageTitle="Client — Răspunde la tichet";
include '../dashboard/header.php';
$ticketId = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);
try { can_view_ticket($pdo, $ticketId, $role, $ui); } catch (Throwable $e) { http_response_code(403); echo $e->getMessage(); exit; }
$info=null;$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST'){
    try {
        $content = trim($_POST['content'] ?? ''); if ($content==='') throw new RuntimeException('Conținut reply obligatoriu');
        $pdo->beginTransaction();
        $i = $pdo->prepare("INSERT INTO tickets_replies (reply_ticketid, reply_by_type, reply_by_ui, reply_content, is_internal, reply_validated) VALUES (:tid, 'CLIENT', :ui, :content, 0, 'approved')");
        $i->execute([':tid'=>$ticketId, ':ui'=>$ui, ':content'=>$content]);
        $replyId = (int)$pdo->lastInsertId();
        $u = $pdo->prepare("UPDATE tickets SET ticket_status=3, ticket_lastupdated=NOW(), ticket_lastupdatedby=:ui WHERE ticket_id=:tid"); $u->execute([':ui'=>$ui, ':tid'=>$ticketId]);
        log_action($pdo, $ticketId, 'CLIENT', $ui, 'POST_REPLY', ['reply_id'=>$replyId]);
        // Notificare admin: reply CLIENT (pending validare)
        notify_client_reply_posted($pdo, $ticketId, $replyId, $ui);
        // Atașamente pentru reply
        if (!empty($_FILES['attachments']['name'][0])) {
            $maxSize=10*1024*1024; $allowed=['application/pdf','image/jpeg','image/png','image/gif','text/plain','application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
            $files=$_FILES['attachments']; $count=is_array($files['name'])?count($files['name']):0;
            $baseDir = base_data_dir() . '/' . pad_ticket_id($ticketId); ensure_dir($baseDir); $finfo = new finfo(FILEINFO_MIME_TYPE);
            $is_internal = isset($_POST['is_internal']) ? 1 : 0;
            for($i2=0;$i2<$count;$i2++){
                $err=$files['error'][$i2]; if($err===UPLOAD_ERR_NO_FILE)continue; if($err!==UPLOAD_ERR_OK) throw new RuntimeException('Eroare upload: ' . $err);
                $tmp=$files['tmp_name'][$i2]; $orig=$files['name'][$i2]; $size=$files['size'][$i2]; if($size<=0||$size>$maxSize) throw new RuntimeException('Dimensiune invalidă');
                $mime=$finfo->file($tmp); if(!in_array($mime,$allowed,true)) throw new RuntimeException('Tip nepermis: ' . htmlspecialchars($mime));
                $ext=safe_ext_from_mime($mime); $stored=generate_safe_name($ext); $dest=$baseDir.'/'.$stored; if(!move_uploaded_file($tmp,$dest)) throw new RuntimeException('Nu pot salva'); @chmod($dest,0600);
                insert_attachment($pdo, $ticketId, $replyId, [
                    'stored'=>$stored,'name'=>$orig,'mime'=>$mime,'size'=>$size,'is_internal'=>$is_internal,
                    'uploaded_by_type'=>'CLIENT','uploaded_by_ui'=>$ui
                ]);
            }
            log_action($pdo, $ticketId, 'CLIENT', $ui, 'ATTACH_UPLOAD', ['reply_id'=>$replyId, 'internal'=>$is_internal]);
        }
        $pdo->commit(); $info='Reply trimis și atașamente încărcate';
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); $error=$e->getMessage(); }
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>CLIENT — Ticket #<?= (int)$ticketId ?></h1>
<?php echo "<a href=\"client_my_tickets.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>"; ?>
<?php if ($info): ?><p style="color:green;"><?= htmlspecialchars($info) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">
  <label>Răspuns către agent<br><textarea name="content" rows="6" required></textarea></label>
  <label>Atașamente (multiple) <input type="file" name="attachments[]" multiple></label>
  <button type="submit" class="button">Trimite reply</button>
</form>
    </div>
    </div> 
<?php
include '../bottom.php';
?>
