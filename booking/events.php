<?php
include '../settings.php';
include '../classes/common.php';

if(!isset($_SESSION)) { session_start(); }

$events = array();

$conn = mysqli_connect("p:$host", "$username", "$password", "$db_name");

// Add holidays if not allowed
if ($allowholydays == 0) {
    foreach ($holidays as $holiday) {
        $event = array();
        $event['title'] = 'Sărbătoare';
        $event['start'] = $holiday;
        $event['allDay'] = true;
        $events[] = $event;
    }
}

// Get reservations
$query_res = "SELECT * FROM bookings_reservations";
$result_res = ezpub_query($conn, $query_res);
while ($row = ezpub_fetch_array($result_res)) {
    $event = array();
    $event['title'] = 'Ocupat';
    if ($row['reservation_type'] == 0) { // full day
        $event['start'] = $row['reservation_start'];
        $event['end'] = date('Y-m-d', strtotime($row['reservation_end'] . ' +1 day')); // end is inclusive?
        $event['allDay'] = true;
    } else { // partial
        $event['start'] = $row['reservation_start_hours'];
        $event['end'] = $row['reservation_end_hours'];
    }
    $events[] = $event;
}

// Get bookings
$query_book = "SELECT b.*, s.service_name, s.service_duration FROM bookings b LEFT JOIN bookings_services s ON b.booking_service = s.service_id WHERE b.booking_validation=1 AND b.booking_confirmation=1";
$role = isset($_SESSION['clearence']) ? $_SESSION['clearence'] : '';
$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
if ($role == 'PROFESSIONAL') {
    $query_book .= " AND b.booking_professional = $uid";
}
if ($query_book) {
    $result_book = ezpub_query($conn, $query_book);
    while ($row = ezpub_fetch_array($result_book)) {
        $event = array();
        if ($role == 'ADMIN' || ($role == 'PROFESSIONAL' && $row['booking_professional'] == $uid)) {
            $event['title'] = $row['service_name'] . ' - ' . $row['booking_first_name'] . ' ' . $row['booking_last_name'];
        } else {
            $event['title'] = 'Ocupat';
        }
        $event['start'] = $row['booking_start_time'];
        $event['end'] = $row['booking_end_time'];
        $events[] = $event;
    }
}

header('Content-Type: application/json');
echo json_encode($events);
?>