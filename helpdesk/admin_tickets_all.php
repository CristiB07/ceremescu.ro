
<?php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';
$strPageTitle="Admin — toate tichetele";
include '../dashboard/header.php';

if ($role !== 'ADMIN') { http_response_code(403); echo 'Acces interzis'; exit; }
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1><?php echo $strTickets ?></h1>
<?php
$s = $pdo->query("SELECT * FROM tickets ORDER BY ticket_lastupdated DESC"); $tickets=$s->fetchAll();
if (!$tickets): echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
  
 else: 
?>
<table>
  <thead>
  <tr>
    <th><?php echo $strID ?></th>
    <th><?php echo $strTitle ?></th>
    <th><?php echo $strClient ?></th>
    <th><?php echo $strAgent ?></th>
    <th><?php echo $strPriority ?></th>
    <th><?php echo $strStatus ?></th>
    <th><?php echo $strActions ?></th>
  </tr>
  </thead>
  <?php foreach ($tickets as $t): ?>
    <tr>
      <td>#<?= (int)$t['ticket_id'] ?></td>
      <td><?= htmlspecialchars($t['ticket_title']) ?></td>
      <td><?= (int)$t['ticket_createdby'] ?></td>
      <td><?= (int)$t['ticket_asignedto'] ?></td>
      <td><?= htmlspecialchars($t['ticket_importantance']) ?></td>
      <td><?= (int)$t['ticket_status'] ?></td>
      <td>
        <a href="admin_view_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strView?></a> |
        <a href="admin_assign_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strAssign."/".$strSetPriority?></a> |
        <a href="admin_close_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strClose ?></a> |
        <a href="admin_reopen_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strReopen ?></a> |
        <a href="admin_post_internal.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strPrivateMessage ?></a>
      </td>
    </tr>
    <tfoot><tr><td></td><td  colspan="6"><em></em></td><td>&nbsp;</td></tr></tfoot>
  <?php endforeach; ?>
  <?php endif; ?>

</table>

<h2><?php echo $strRepliesPendingValidation ?></h2>
<?php $rp = $pdo->query("SELECT r.*, t.ticket_title FROM tickets_replies r JOIN tickets t ON t.ticket_id=r.reply_ticketid WHERE r.reply_by_type='agent' AND r.reply_validated='pending' ORDER BY r.reply_at ASC")->fetchAll(); ?>
<?php if (!$rp): echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
  
 else: 
  foreach ($rp as $r): ?>
    <div style="border:1px solid #ddd;padding:.5rem;margin:.5rem 0">
      <strong>Ticket #<?= (int)$r['reply_ticketid'] ?> — <?= htmlspecialchars($r['ticket_title']) ?></strong><br>
      Agent #<?= (int)$r['reply_by_ui'] ?> la <?= htmlspecialchars($r['reply_at']) ?><br>
      <div><?= nl2br(htmlspecialchars($r['reply_content'])) ?></div>
      <form method="post" action="admin_validate_replies.php" style="margin-top:.5rem;">
        <input type="hidden" name="reply_id" value="<?= (int)$r['reply_id'] ?>">
        <button name="action" value="approve" class="button">Aprobă</button>
        <button name="action" value="reject" class="button">Respinge</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
  </div>
  </div>
<?php
include '../bottom.php';
?>
