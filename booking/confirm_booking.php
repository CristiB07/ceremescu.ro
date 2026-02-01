<?php
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { session_start(); }

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes" OR !in_array($_SESSION['clearence'], ['ADMIN', 'PROFESSIONAL'])) {
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}


$id = (int)$_GET['id'];

$query = "SELECT * FROM bookings WHERE booking_id = $id";
$result = ezpub_query($conn, $query);
$row = ezpub_fetch_array($result);

if ($row) {
    // Update confirmation
    $update_query = "UPDATE bookings SET booking_confirmation = 1 WHERE booking_id = $id";
    ezpub_query($conn, $update_query);

    // Send confirmation email
    require '../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = $SmtpServer;
    $mail->SMTPAuth = true;
    $mail->Username = $SmtpUser;
    $mail->Password = $SmtpPass;
    $mail->SMTPSecure = 'tls';
    $mail->Port = $SmtpPort;
    $mail->setFrom($SmtpUser, 'MasterApp');
    $mail->addAddress($row['booking_email']);
    $mail->Subject = 'Confirmare programare';
    $mail->Body = 'Programarea dumneavoastră a fost confirmată.';
    $mail->send();
}

header("location: confirm.php?message=Confirmed");
?>