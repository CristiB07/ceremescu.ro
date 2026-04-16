<?php
 if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
$role=$_SESSION['clearence'];
$ui=$_SESSION['uid'];
// Composer autoload (PHPMailer)
require '../vendor/autoload.php';

function log_action(int $ticketId, string $actorType, int $actorUi, string $actionType, array $details = []): void {
    global $conn;
    $json = $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
    $stmt = mysqli_prepare($conn, "INSERT INTO tickets_log (ticket_id, actor_type, actor_ui, action_type, details) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isiss", $ticketId, $actorType, $actorUi, $actionType, $json);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
