<?php
include '../settings.php';
include '../classes/common.php';

// Validate input exists
if (!isset($_POST['uname']) || empty(trim($_POST['uname']))) {
    echo '0'; // No email provided, return 0 (available)
    exit();
}

$uname = trim($_POST['uname']);

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) as cntUser FROM site_accounts WHERE account_email = ?");
mysqli_stmt_bind_param($stmt, 's', $uname);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Return count: 0 = email available, >0 = email already exists
$count = (int)($row['cntUser'] ?? 0);
echo $count;
?>