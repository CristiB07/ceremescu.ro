
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mail.php';
$strPageTitle="Client — Adaugă tichet nou";
include '../dashboard/header.php';
if ($role !== 'CLIENT') { http_response_code(403); echo 'Acces interzis'; exit; }
$info=null;$error=null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        if ($title === '' || $content === '') throw new RuntimeException('Titlu și conținut obligatorii');
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO tickets (ticket_createdby, ticket_title, ticket_content, ticket_importantance, ticket_status, ticket_lastupdated, ticket_lastupdatedby) VALUES (:cui, :title, :content, :prio, 0, NOW(), :cui)");
        $stmt->execute([':cui'=>$ui, ':title'=>$title, ':content'=>$content, ':prio'=>$priority]);
        $ticketId = (int)$pdo->lastInsertId();
        log_action($pdo, $ticketId, 'CLIENT', $ui, 'CREATE_TICKET', ['title'=>$title, 'priority'=>$priority]);
        // Notificare admin: ticket nou
        notify_ticket_created($pdo, $ticketId, $ui);

        // Dacă sunt atașamente, creăm un reply "placeholder" al clientului și legăm fișierele de el
        $replyIdForAttachments = null;
        if (!empty($_FILES['attachments']['name'][0])) {
            $r = $pdo->prepare("INSERT INTO tickets_replies (reply_ticketid, reply_by_type, reply_by_ui, reply_content, is_internal, reply_validated) VALUES (:tid, 'CLIENT', :ui, '', 0, 'approved')");
            $r->execute([':tid'=>$ticketId, ':ui'=>$ui]);
            $replyIdForAttachments = (int)$pdo->lastInsertId();
            $maxSize = 10 * 1024 * 1024;
            $allowed = ['application/pdf','image/jpeg','image/png','image/gif','text/plain','application/zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
            $files = $_FILES['attachments'];
            $count = is_array($files['name']) ? count($files['name']) : 0;
            $baseDir = base_data_dir() . '/' . pad_ticket_id($ticketId); 
            ensure_dir($baseDir);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            for ($i=0;$i<$count;$i++) {
                $err = $files['error'][$i]; 
                if ($err===UPLOAD_ERR_NO_FILE) continue; 
                if ($err!==UPLOAD_ERR_OK) throw new RuntimeException('Eroare upload: ' . $err);
                $tmp = $files['tmp_name'][$i]; 
                $orig = $files['name'][$i]; 
                $size = $files['size'][$i]; 
                if ($size<=0||$size>$maxSize) throw new RuntimeException('Fișier prea mare sau gol');
                $mime = $finfo->file($tmp); 
                if (!in_array($mime,$allowed,true)) throw new RuntimeException('Tip fișier nepermis: ' . htmlspecialchars($mime));
                $ext = safe_ext_from_mime($mime); 
                $stored = generate_safe_name($ext); 
                $dest = $baseDir . '/' . $stored; 
                if (!move_uploaded_file($tmp, $dest)) throw new RuntimeException('Nu pot salva fișierul'); 
                @chmod($dest,0600);
                insert_attachment($pdo, $ticketId, $replyIdForAttachments, 
                [
                    'stored'=>$stored,'name'=>$orig,'mime'=>$mime,'size'=>$size,'is_internal'=>0,
                    'uploaded_by_type'=>'CLIENT','uploaded_by_ui'=>$ui
                ]
              );
            }
            log_action($pdo, $ticketId, 'CLIENT', $ui, 'ATTACH_UPLOAD', ['reply_id'=>$replyIdForAttachments]);
        }

        $pdo->commit();
        $info = 'Ticket creat (#' . $ticketId . ')';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack(); $error=$e->getMessage();
    }
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">

<h1>CLIENT - Creare ticket</h1>
<?php echo "<a href=\"client_my_tickets.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>"; ?>
<?php if ($info): ?><p style="color:green;"><?= htmlspecialchars($info) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <label>Titlu <input type="text" name="title" required></label>
  <label>Prioritate
    <select name="priority">
      <option value="low">Low</option>
      <option value="medium" selected>Medium</option>
      <option value="high">High</option>
      <option value="urgent">Urgent</option>
    </select>
  </label>
  <label>Conținut<br><textarea name="content" rows="8" required></textarea></label>
  <label>Atașamente (multiple) <input type="file" name="attachments[]" multiple></label>
  <button type="submit" class="button"><?php  echo $strAdd?></button>
</form>
  </div>
  </div>
<?php
include '../bottom.php';
?>
