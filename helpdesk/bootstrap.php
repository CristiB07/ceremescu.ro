<?php
 if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
$role=$_SESSION['clearence'];
$ui=$_SESSION['uid'];
// Composer autoload (PHPMailer)
require '../vendor/autoload.php';

function log_action(PDO $pdo, int $ticketId, string $actorType, int $actorUi, string $actionType, array $details = []): void {
    $sql = "INSERT INTO tickets_log (ticket_id, actor_type, actor_ui, action_type, details) VALUES (:tid, :actor_type, :actor_ui, :action_type, :details)";
    $stmt = $pdo->prepare($sql);
    $json = $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
    $stmt->execute([':tid'=>$ticketId, ':actor_type'=>$actorType, ':actor_ui'=>$actorUi, ':action_type'=>$actionType, ':details'=>$json]);
}
?>
