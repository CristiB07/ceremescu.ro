<?php
require_once '../settings.php';
require_once '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';

$strPageTitle="Client — toate tichetele";
include '../dashboard/header.php';
if ($role !== 'CLIENT') { http_response_code(403); echo 'Acces interzis'; exit; }
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1>Client — Tichetele mele</h1>
<p><a href="client_new_ticket.php" class="button"><?php echo $strAdd ?>&nbsp;<i class="fa-xl fa fa-plus" title="<?php echo $strAdd ?>"></i></a></p>

<?php

$stmt = mysqli_prepare($conn, "SELECT * FROM tickets WHERE ticket_createdby=? ORDER BY ticket_lastupdated DESC");
mysqli_stmt_bind_param($stmt, "i", $ui);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$tickets = [];
while ($row = mysqli_fetch_assoc($res)) { $tickets[] = $row; }
mysqli_stmt_close($stmt);
if (!$tickets): echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
  
 else: 
?>
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
      <td><a href="client_view_ticket.php?ticket_id=<?= (int)$t['ticket_id'] ?>">Vezi</a></td>
    </tr>
  <?php endforeach; ?>
   <tfoot><tr><td></td><td  colspan="3"><em></em></td><td>&nbsp;</td></tr></tfoot>
</table>
<?php endif; ?>
  </div>
  </div>
<?php
include '../bottom.php';
?>
