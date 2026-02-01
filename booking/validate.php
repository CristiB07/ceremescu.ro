<?php
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { session_start(); }


$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $secret = mysqli_real_escape_string($conn, $_POST['secret']);
    $hash = mysqli_real_escape_string($conn, $_GET['hash']);

    $query = "SELECT * FROM bookings WHERE booking_email = ? AND booking_secret = ? AND booking_hash = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $email, $secret, $hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        // Update validation
        $update_query = "UPDATE bookings SET booking_validation = 1 WHERE booking_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $row['booking_id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        $message = 'Programarea a fost validată cu succes.';
    } else {
        $message = 'Date incorecte.';
    }
    mysqli_stmt_close($stmt);
}

include 'header.php';
?>

<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1>Validare programare</h1>
            <?php if ($message) echo "<div class=\"callout\">$message</div>"; ?>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-6 medium-6 small-12 cell">
            <form action="" method="post">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Cod de validare</label>
                <input type="text" name="secret" required>
                <button type="submit" class="button">Validează</button>
            </form>
        </div>
    </div>
</div>

<?php
include '../bottom.php';
?>