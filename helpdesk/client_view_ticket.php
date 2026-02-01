<?php
require_once '../settings.php';
require_once '../classes/common.php';

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/helpers.php';
if ($role !== 'CLIENT') { http_response_code(403); echo 'Acces interzis'; exit; }
$strPageTitle="Client — Vizualizează tichet";
include '../dashboard/header.php';

$ticketId = (int)($_GET['ticket_id'] ?? 0);
try { can_view_ticket($pdo, $ticketId, $role, $ui); } catch (Throwable $e) { http_response_code(403); echo $e->getMessage(); exit; }
$tstmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id=:tid"); $tstmt->execute([':tid'=>$ticketId]); $ticket = $tstmt->fetch(); if(!$ticket){ http_response_code(404); echo 'Ticket inexistent'; exit; }
$r = $pdo->prepare("SELECT * FROM tickets_replies WHERE reply_ticketid=:tid AND is_internal=0 AND (reply_validated='approved' OR reply_by_type IN ('client','admin')) ORDER BY reply_at ASC"); $r->execute([':tid'=>$ticketId]); $replies = $r->fetchAll();
$atts = get_attachments_for_viewer($pdo, $ticketId, $role);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>Ticket #<?= (int)$ticketId ?> — <?= htmlspecialchars($ticket['ticket_title']) ?></h1>
<?php echo "<a href=\"client_my_tickets.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>"; ?>
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
<section>
  <a href="client_reply_ticket.php?ticket_id=<?= (int)$ticketId ?>" class="button"><?= $strReply ?></a>
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
  </div>
  </div>
<?php
include '../bottom.php';
?>