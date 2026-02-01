
<?php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';
  
if ($role !== 'AGENT') { http_response_code(403); echo 'Acces interzis'; echo exit; }
$strPageTitle="Agent — Tichete alocate";
include '../dashboard/header.php';

$s = $pdo->prepare("SELECT * FROM tickets WHERE ticket_asignedto=:ui ORDER BY ticket_lastupdated DESC"); $s->execute([':ui'=>$ui]); $tickets=$s->fetchAll();
?>

<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>Agent — Tichete alocate</h1>
<p><a href="/logout.php">Logout</a></p>
<table>
  <thead>
  <tr>
    <th><?php echo $strID ?></th>
    <th><?php echo $strTitle ?></th>
    <th><?php echo $strPriority ?></th>
    <th><?php echo $strStatus ?></th>
    <th><?php echo $strActions ?></th>
  </tr>
  </thead>
  <?php foreach ($tickets as $t): ?>
    <tr>
      <td>#<?= (int)$t['ticket_id'] ?></td>
      <td><?= htmlspecialchars($t['ticket_title']) ?></td>
      <td><?= htmlspecialchars($t['ticket_importantance']) ?></td>
      <td><?= (int)$t['ticket_status'] ?></td>
      <td><a href="agent_reply_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>"><?php echo $strOpen?></a></td>
    </tr>
  <?php endforeach; ?>
    <tfoot><tr><td></td><td  colspan="3"><em></em></td><td>&nbsp;</td></tr></tfoot>
</table>
  </div>
  </div>
  <?php
include '../bottom.php';
?>
