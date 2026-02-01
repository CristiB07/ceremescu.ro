<?php
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "Programări";

if(!isset($_SESSION)) { session_start(); }

if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    $loggedin = false;
    include '../header.php';
} else {
    $loggedin = true;
    $role = $_SESSION['clearence'];
    $uid = $_SESSION['uid'];
    include '../dashboard/header.php';
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $service = (int)$_POST['service'];
    $start_time = $_POST['start_time'];
    $location = isset($_POST['location']) ? (int)$_POST['location'] : null;
    $professional = isset($_POST['professional']) ? (int)$_POST['professional'] : null;
    $user_id = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;

    // Get service duration
    $query_service = "SELECT service_duration FROM bookings_services WHERE service_id = $service";
    $result_service = ezpub_query($conn, $query_service);
    if (!$result_service || ezpub_num_rows($result_service) == 0) {
        die('Serviciu invalid.');
    }
    $row_service = ezpub_fetch_array($result_service);
    $duration = $row_service['service_duration'];

    // Calculate end time
    $start_datetime = new DateTime($start_time);
    $start_datetime->add(new DateInterval('PT' . $duration . 'M'));
    $end_time = $start_datetime->format('Y-m-d H:i:s');

    // Check if start time is in the future
    $now = new DateTime();
    if ($start_datetime <= $now) {
        die('Nu se pot face programări în trecut.');
    }

    // Generate secret and hash
    $secret = generateRandomString(10);
    $hash = hash('sha256', $email);

    // Insert booking
    $query_insert = "INSERT INTO bookings (booking_location, booking_professional, booking_service, booking_first_name, booking_last_name, booking_email, booking_phone, booking_start_time, booking_end_time, booking_secret, booking_hash, booking_validation, booking_confirmation, booking_user_id, booking_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, '')";
    $stmt = mysqli_prepare($conn, $query_insert);
    if (!$stmt) {
        die('Eroare la pregătirea query-ului: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "iiissssssssi", $location, $professional, $service, $first_name, $last_name, $email, $phone, $start_time, $end_time, $secret, $hash, $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        die('Eroare la inserarea în baza de date: ' . mysqli_stmt_error($stmt));
    }
    $booking_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Send email
    try {
        ini_set('pcre.jit', '0'); // Disable PCRE JIT to avoid memory allocation warnings
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
        $mail->addAddress($email);
        $mail->Subject = 'Validare programare';
        $mail->Body = 'Codul dumneavoastră de validare: ' . $secret . "\nLink: " . $strSiteURL . "/booking/validate.php?hash=" . $hash;
        $mail->send();
        die('Rezervare trimisă cu succes. Verifică emailul pentru codul de validare.');
    } catch (Exception $e) {
        die('Eroare la trimiterea emailului: ' . $e->getMessage());
    }
}

$user_data = array();
if ($loggedin) {
    $query_user = "SELECT * FROM site_accounts WHERE account_id = $uid";
    $result_user = ezpub_query($conn, $query_user);
    $user_data = ezpub_fetch_array($result_user);
}

// Get services
$query_services = "SELECT * FROM bookings_services";
$result_services = ezpub_query($conn, $query_services);

// Get locations if enabled
$locations_data = array();
if ($locations == 1) {
    $query_locations = "SELECT * FROM bookings_locations";
    $result_locations = ezpub_query($conn, $query_locations);
    while ($row = ezpub_fetch_array($result_locations)) {
        $locations_data[] = $row;
    }
}

// Get professionals if enabled
$professionals_data = array();
if ($professionals == 1) {
    $query_prof = "SELECT user_ID, user_FirstName, user_LastName FROM date_utilizatori WHERE user_Function='PROFESSIONAL'";
    $result_prof = ezpub_query($conn, $query_prof);
    while ($row = ezpub_fetch_array($result_prof)) {
        $professionals_data[] = $row;
    }
}
?>

<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1>Programări</h1>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<!-- Modal pentru rezervare -->
<div class="reveal" id="bookingModal" data-reveal>
    <h2>Rezervare programare</h2>
    <form method="post">
        <input type="hidden" name="start_time" id="start_time">
        <label>Nume</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user_data['account_last_name'] ?? ''); ?>" required>
        <label>Prenume</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user_data['account_first_name'] ?? ''); ?>" required>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['account_email'] ?? ''); ?>" required>
        <label>Telefon</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['account_phone'] ?? ''); ?>" required>
        <label>Serviciu</label>
        <select name="service" id="service" required>
            <option value="">Alege serviciu</option>
            <?php while ($row = ezpub_fetch_array($result_services)) { ?>
                <option value="<?php echo $row['service_id']; ?>" data-duration="<?php echo $row['service_duration']; ?>"><?php echo $row['service_name']; ?></option>
            <?php } ?>
        </select>
        <?php if ($locations == 1) { ?>
        <label>Locație</label>
        <select name="location" id="location">
            <option value="">Alege locație</option>
            <?php foreach ($locations_data as $loc) { ?>
                <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['location_name']; ?></option>
            <?php } ?>
        </select>
        <?php } ?>
        <?php if ($professionals == 1) { ?>
        <label>Profesionist</label>
        <select name="professional" id="professional">
            <option value="">Alege profesionist</option>
            <?php foreach ($professionals_data as $prof) { ?>
                <option value="<?php echo $prof['user_ID']; ?>"><?php echo $prof['user_FirstName'] . ' ' . $prof['user_LastName']; ?></option>
            <?php } ?>
        </select>
        <?php } ?>
        <label>Ora început</label>
        <input type="datetime-local" name="start_time_display" id="start_time_display">
        <label>Ora sfârșit (se calculează automat)</label>
        <input type="datetime-local" id="end_time" readonly>
        <button type="submit" class="button">Rezervă</button>
    </form>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/locales/ro.global.min.js"></script>
<style>
.fc-week-number, .fc-daygrid-week-number, .fc-timegrid-week-number, .fc-week-number-col {
    display: none !important;
}
.past-event {
    background-color: #ccc !important;
    color: #666 !important;
}
.disabled-day {
    background-color: #f0f0f0 !important;
}
.fc-daygrid-event-dot {
    display: none !important;
}
</style>
<script>
function formatLocalDateTime(date) {
    var year = date.getFullYear();
    var month = (date.getMonth() + 1).toString().padStart(2, '0');
    var day = date.getDate().toString().padStart(2, '0');
    var hours = date.getHours().toString().padStart(2, '0');
    var minutes = date.getMinutes().toString().padStart(2, '0');
    return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
}

document.addEventListener('DOMContentLoaded', function() {
    var today = new Date();
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    //var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
   // var endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00',
    firstDay:1, 
    locale:'ro', 
        buttonText: {
            today: 'Astăzi',
            month: 'Lună',
            week: 'Săptămână',
            day: 'Zi'
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'events.php',
        selectable: true,
        select: function(info) {
            // Check if start is in future
            if (info.start < tomorrow) {
                alert('Nu se pot face programări în trecut');
                return;
            }
            // Check if free
            var events = calendar.getEvents();
            var overlap = false;
            events.forEach(function(event) {
                if (event.start < info.end && event.end > info.start) {
                    overlap = true;
                }
            });
            if (!overlap) {
                var startDateTime = info.startStr.split('T')[0] + 'T09:00';
                document.getElementById('start_time').value = startDateTime;
                document.getElementById('start_time_display').value = startDateTime;
                $('#bookingModal').foundation('open');
            } else {
                alert('Slot ocupat');
            }
        },
        hiddenDays: <?php echo $allowweekends == 0 ? '[0,6]' : '[]'; ?>,
        businessHours: {
            daysOfWeek: <?php echo $allowweekends == 0 ? '[1,2,3,4,5]' : '[0,1,2,3,4,5,6]'; ?>,
            startTime: '08:00',
            endTime: '20:00'
        },
        dayCellDidMount: function(info) {
            var date = info.date;
            var dateStr = date.toISOString().split('T')[0];
            var holidays = <?php echo json_encode($holidays); ?>;
            var today = new Date();
            today.setHours(0,0,0,0);
            
            if (date < today || holidays.includes(dateStr)) {
                info.el.classList.add('disabled-day');
            }
        }
    });
    calendar.render();

    // Calculare ora sfârșit la schimbarea serviciului
    document.getElementById('service').addEventListener('change', function() {
        var duration = this.options[this.selectedIndex].getAttribute('data-duration');
        if (duration) {
            var start = new Date(document.getElementById('start_time').value);
            start.setMinutes(start.getMinutes() + parseInt(duration));
            document.getElementById('end_time').value = formatLocalDateTime(start);
        }
    });

    // Sincronizare ora început
    document.getElementById('start_time_display').addEventListener('change', function() {
        document.getElementById('start_time').value = this.value;
        // Recalculare sfârșit
        var service = document.getElementById('service');
        var duration = service.options[service.selectedIndex].getAttribute('data-duration');
        if (duration) {
            var start = new Date(this.value);
            start.setMinutes(start.getMinutes() + parseInt(duration));
            document.getElementById('end_time').value = formatLocalDateTime(start);
        }
    });
});
</script>

<?php
include '../bottom.php';
?>