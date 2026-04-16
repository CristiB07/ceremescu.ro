
<?php
include '../settings.php';
include '../classes/common.php';
require_once __DIR__ . '/bootstrap.php';
if ($role !== 'ADMIN') { http_response_code(403); echo 'Acces interzis'; exit; }
$strPageTitle="Admin — Vizualizează tichet";
include '../dashboard/header.php';

$ticketId = (int)($_GET['ticket_id'] ?? $_POST['ticket_id'] ?? 0);
$info=null;$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST'){
    try {
        $agentUi = (int)($_POST['agent_ui'] ?? 0);
        $priority = $_POST['priority'] ?? null;
        if ($agentUi<=0) throw new RuntimeException('agent_ui invalid');
        $sql = "UPDATE tickets SET ticket_asignedto=?, ticket_status=1, ticket_lastupdated=NOW(), ticket_lastupdatedby=?";
        $types = "ii";
        $params = [$agentUi, $ui];
        if ($priority) { $sql .= ", ticket_importantance=?"; $types .= "s"; $params[] = $priority; }
        $sql .= " WHERE ticket_id=?";
        $types .= "i"; $params[] = $ticketId;
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        log_action($ticketId, 'ADMIN', $ui, 'ASSIGN_TICKET', ['agent_ui'=>$agentUi,'priority'=>$priority]);
        $info='Actualizat';
    } catch (Throwable $e) { $error=$e->getMessage(); }
}
$stmt_t = mysqli_prepare($conn, "SELECT * FROM tickets WHERE ticket_id=?");
mysqli_stmt_bind_param($stmt_t, "i", $ticketId);
mysqli_stmt_execute($stmt_t);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_t));
mysqli_stmt_close($stmt_t);
if(!$ticket){echo 'Ticket inexistent';exit;}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">

<h1>Admin — Ticket #<?= (int)$ticketId ?> — Alocare/Setare prioritate</h1>
<?php echo "<a href=\"admin_tickets_all.php\" class=\"button\">$strBack &nbsp;<i class=\"fas fa-backward fa-xl\"></i></a>"; ?>
<?php if ($info): ?><p style="color:green;"><?= htmlspecialchars($info) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">
  <label>Agent UI 
    <input type="number" name="agent_ui" value="<?= (int)$ticket['ticket_asignedto'] ?>" required>
  </label>
  <label>Prioritate
    <select name="priority">
      <option value="">(nemodifica)</option>
      <option value="low"<?= $ticket['ticket_importantance']==='low'?' selected':'' ?>>Low</option>
      <option value="medium"<?= $ticket['ticket_importantance']==='medium'?' selected':'' ?>>Medium</option>
      <option value="high"<?= $ticket['ticket_importantance']==='high'?' selected':'' ?>>High</option>
      <option value="urgent"<?= $ticket['ticket_importantance']==='urgent'?' selected':'' ?>>Urgent</option>
    </select>
  </label>
  <button type="submit" class="button"><?php echo $strAdd; ?></button>
</form>
  </div>
  </div>
<?php
include '../bottom.php';
?>
