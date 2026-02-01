
<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mail.php';
if ($role !== 'AGENT') { http_response_code(403); echo 'Acces interzis'; exit; }
$strPageTitle="Agent — Răspunde la tichet";
include '../dashboard/header.php';
$ticketId = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);
try { can_view_ticket($pdo, $ticketId, $role, $ui); } catch (Throwable $e) { http_response_code(403); echo $e->getMessage(); exit; }
$info=null;$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST'){
    try {
        $content = trim($_POST['content'] ?? ''); if ($content==='') throw new RuntimeException('Conținut reply obligatoriu');
        $pdo->beginTransaction();
        $i = $pdo->prepare("INSERT INTO tickets_replies (reply_ticketid, reply_by_type, reply_by_ui, reply_content, is_internal, reply_validated) VALUES (:tid, 'AGENT', :ui, :content, 0, 'pending')");
        $i->execute([':tid'=>$ticketId, ':ui'=>$ui, ':content'=>$content]);
        $replyId = (int)$pdo->lastInsertId();
        $u = $pdo->prepare("UPDATE tickets SET ticket_status=3, ticket_lastupdated=NOW(), ticket_lastupdatedby=:ui WHERE ticket_id=:tid"); $u->execute([':ui'=>$ui, ':tid'=>$ticketId]);
        log_action($pdo, $ticketId, 'AGENT', $ui, 'POST_REPLY', ['reply_id'=>$replyId]);
        // Notificare admin: reply AGENT (pending validare)
        notify_agent_reply_posted($pdo, $ticketId, $replyId, $ui);
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
                    'uploaded_by_type'=>'AGENT','uploaded_by_ui'=>$ui
                ]);
            }
            log_action($pdo, $ticketId, 'AGENT', $ui, 'ATTACH_UPLOAD', ['reply_id'=>$replyId, 'internal'=>$is_internal]);
        }
        $pdo->commit(); $info='Reply trimis și atașamente încărcate';
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); $error=$e->getMessage(); }
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>AGENT — Ticket #<?= (int)$ticketId ?></h1>
<?php echo "<a href=\"agent_assigned_tickets.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>"; ?>
<?php if ($info): ?><p style="color:green;"><?= htmlspecialchars($info) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
   <?php $ticketId = (int)($_GET['ticket_id'] ?? 0);
try { can_view_ticket($pdo, $ticketId, $role, $ui); } catch (Throwable $e) { http_response_code(403); echo $e->getMessage(); exit; }
$tstmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id=:tid"); $tstmt->execute([':tid'=>$ticketId]); $ticket = $tstmt->fetch(); if(!$ticket){ http_response_code(404); echo 'Ticket inexistent'; exit; }
$r = $pdo->prepare("SELECT * FROM tickets_replies WHERE reply_ticketid=:tid AND is_internal=0 AND (reply_validated='approved' OR reply_by_type IN ('client','admin')) ORDER BY reply_at ASC"); $r->execute([':tid'=>$ticketId]); $replies = $r->fetchAll();
$atts = get_attachments_for_viewer($pdo, $ticketId, $role);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>Ticket #<?= (int)$ticketId ?> — <?= htmlspecialchars($ticket['ticket_title']) ?></h1>
<section>
  <h2>Detalii</h2>
  <p><strong>Prioritate:</strong> <?= htmlspecialchars($ticket['ticket_importantance']) ?> | <strong>Status:</strong> <?= (int)$ticket['ticket_status'] ?></p>
  <p><?= nl2br(htmlspecialchars($ticket['ticket_content'])) ?></p>
</section>
<section>
  <h2>Atașamente vizibile</h2>
  <?php if (!$atts): ?><p>Nu există atașamente.</p><?php else: ?><ul>
    <?php foreach ($atts as $a): ?>
      <li><?= htmlspecialchars($a['name'] ?? $a['stored']) ?> (<?= htmlspecialchars($a['mime']) ?>, <?= (int)$a['size'] ?> bytes)
        — <a href="download_attachment.php?attachment_id=<?= (int)$a['attachment_id'] ?>">Descarcă</a></li>
    <?php endforeach; ?>
  </ul><?php endif; ?>
</section>
<?php if ((int)$ticket['ticket_status'] === 4): ?>
<section>
  <form method="post" action="client_request_reopen.php">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">
    <button type="submit">Solicită redeschidere</button>
  </form>
</section>
<?php endif; ?>
<section>
  <h2>Răspunsuri</h2>
  <?php if (!$replies): ?><p>Nu există răspunsuri vizibile.</p><?php else: ?>
    <?php foreach ($replies as $rp): ?>
      <div style="border:1px solid #ddd;padding:.5rem;margin:.5rem 0">
        <small><?= htmlspecialchars($rp['reply_by_type']) ?> #<?= (int)$rp['reply_by_ui'] ?> — <?= htmlspecialchars($rp['reply_at']) ?></small>
        <div><?= nl2br(htmlspecialchars($rp['reply_content'])) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">
  <label>Răspuns către client<br><textarea name="content" rows="6" required></textarea></label>
  <label>Atașamente (multiple) <input type="file" name="attachments[]" multiple></label>
  <label><input type="checkbox" name="is_internal"> Marchează atașamentele ca interne (invizibile clientului)</label>
  <button type="submit" class="button">Trimite reply</button>
</form>
    </div>
    </div>
<?php
include '../bottom.php';
?>
