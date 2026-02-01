<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "Confirmare programări";
if(!isset($_SESSION)) { session_start(); }

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes" OR !in_array($_SESSION['clearence'], ['ADMIN', 'PROFESSIONAL'])) {
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

$conn = mysqli_connect("p:$host", "$username", "$password", "$db_name");

$role = $_SESSION['clearence'];
$uid = $_SESSION['uid'];

$query = "SELECT * FROM bookings WHERE booking_validation=1 AND booking_confirmation=0";
if ($role == 'PROFESSIONAL') {
    $query .= " AND booking_professional = $uid";
}

$result = ezpub_query($conn, $query);

include '../dashboard/header.php';
?>

<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1>Confirmare programări</h1>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <table>
                <thead>
                    <tr>
                        <th>Nume</th>
                        <th>Email</th>
                        <th>Data</th>
                        <th>Acțiune</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = ezpub_fetch_array($result)) { ?>
                        <tr>
                            <td><?php echo $row['booking_first_name'] . ' ' . $row['booking_last_name']; ?></td>
                            <td><?php echo $row['booking_email']; ?></td>
                            <td><?php echo $row['booking_start_time']; ?></td>
                            <td><a href="confirm_booking.php?id=<?php echo $row['booking_id']; ?>" class="button small">Confirmă</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include '../bottom.php';
?>