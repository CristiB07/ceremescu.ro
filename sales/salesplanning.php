<?php
include '../settings.php';
include '../classes/common.php';

$strPageTitle = "Planificare vânzări";
include '../dashboard/header.php';

if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}

$role = $_SESSION['function'];
$uid = $_SESSION['uid'];

?>
<div class="grid-container">
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <h1><?php echo $strSchedules; ?></h1>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
            <button id="copyEventsBtn" class="button">Copiază evenimente</button>
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal pentru adăugare/editare -->
<div class="reveal" id="eventModal" data-reveal>
    <h2 id="modalTitle">Adăugare programare</h2>
    <form id="eventForm">
        <input type="hidden" id="eventId" name="eventId">
        <label>Data și ora:
            <input type="datetime-local" id="eventDateTime" name="eventDateTime" required>
        </label>
        <label>Obiectiv:
            <input type="text" id="eventObjective" name="eventObjective" required>
        </label>
        <label>Client:
            <select id="eventClient" name="eventClient">
                <option value="">Selectează client</option>
                <?php
                $clientsQuery = "SELECT prospect_ID, prospect_denumire FROM sales_prospecti ORDER BY prospect_denumire";
                $clientsResult = ezpub_query($conn, $clientsQuery);
                while ($client = ezpub_fetch_array($clientsResult)) {
                    echo "<option value='{$client['prospect_ID']}'>{$client['prospect_denumire']}</option>";
                }
                ?>
            </select>
        </label>
        <label>Zonă:
            <input type="text" id="eventZone" name="eventZone">
        </label>
        <label>Tip vizită:
            <select id="eventTipVizita" name="eventTipVizita">
                <option value="">Selectează tip</option>
                <option value="Fizică">Fizică</option>
                <option value="Apel telefonic">Apel telefonic</option>
                <option value="Întâlnire video">Întâlnire video</option>
            </select>
        </label>
        <label>Detalii vizită (max 500 caractere):
            <textarea id="eventDetalii" name="eventDetalii" maxlength="500" rows="3"></textarea>
        </label>
        <label>Finalizată:
            <input type="checkbox" id="eventFinalized" name="eventFinalized">
        </label>
        <label>Trimite invitație (.ics):
            <input type="checkbox" id="sendInvite" name="sendInvite" value="1">
        </label>
        <label>Adrese email invitație (opțional, separate prin virgulă sau ;):
            <input type="text" id="inviteEmail" name="inviteEmail" placeholder="invite1@exemplu.ro, invite2@exemplu.ro">
        </label>
        <label>Durata vizită (minute):
            <input type="number" id="eventDurationMinutes" name="eventDurationMinutes" min="1" value="60">
        </label>
        <button type="submit" class="button">Salvează</button>
        <button type="button" id="deleteEventBtn" class="button alert">Șterge</button>
    </form>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<!-- Modal pentru copiere -->
<div class="reveal" id="copyModal" data-reveal>
    <h2>Copiază evenimente</h2>
    <form id="copyForm">
        <label>Din dată:
            <input type="date" id="copyFromDate" name="copyFromDate" required>
        </label>
        <label>La dată:
            <input type="date" id="copyToDate" name="copyToDate" required>
        </label>
        <label>Tip copiere:
            <select id="copyType" name="copyType">
                <option value="day">Zi</option>
                <option value="week">Săptămână</option>
            </select>
        </label>
        <button type="submit" class="button">Copiază</button>
    </form>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<!-- Include FullCalendar CSS and JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js"></script>

<style>
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
document.addEventListener('DOMContentLoaded', function() {
    console.log('salesplanning: DOMContentLoaded');
    try {
    // modal helpers: prefer Foundation if available, otherwise fallback to simple show/hide
    function openModalById(id) {
        if (window.jQuery && typeof jQuery === 'function' && jQuery.fn && jQuery.fn.foundation) {
            jQuery('#' + id).foundation('open');
            return;
        }
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        el.classList.add('is-open');
        el.removeAttribute('aria-hidden');
        // focus first focusable
        var first = el.querySelector('input, select, textarea, button');
        if (first) first.focus();
    }
    function closeModalById(id) {
        if (window.jQuery && typeof jQuery === 'function' && jQuery.fn && jQuery.fn.foundation) {
            jQuery('#' + id).foundation('close');
            return;
        }
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'none';
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
        var safe = document.getElementById('copyEventsBtn'); if (safe) safe.focus();
    }
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        firstDay: 1, // Începe săptămâna cu Luni
        locale: 'ro', // Limba română
        buttonText: {
            today: 'Astăzi',
            month: 'Lună',
            week: 'Săptămână',
            day: 'Zi'
        },
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '19:00:00',
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('salesgetplans.php?start=' + encodeURIComponent(fetchInfo.startStr) + '&end=' + encodeURIComponent(fetchInfo.endStr))
                .then(response => {
                    var ct = response.headers.get('content-type') || '';
                    if (!response.ok) {
                        return response.text().then(t => { throw new Error('Server returned HTTP ' + response.status + ': ' + t); });
                    }
                    if (ct.indexOf('application/json') !== -1) {
                        return response.json();
                    }
                    // If server returned HTML (PHP warnings/errors), surface it
                    return response.text().then(t => { throw new Error('Invalid JSON response:\n' + t); });
                })
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error loading events:', error);
                    alert('Eroare la încărcare evenimente: verifica consola pentru detalii.');
                    // show raw server output in console if available
                    failureCallback(error);
                });
        },
 
        dayCellDidMount: function(info) {
            var date = info.date;
            var dateStr = date.toISOString().split('T')[0];
            var holidays = <?php echo json_encode($holidays); ?>;
            var dayOfWeek = date.getDay(); // 0=Sun, 1=Mon, etc.
            
            if (holidays.includes(dateStr) || dayOfWeek === 0 || dayOfWeek === 6) {
                info.el.classList.add('disabled-day');
            }
        },
        dateClick: function(info) {
            var clickedDate = new Date(info.dateStr);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (clickedDate < today) {
                alert('Nu se pot face programări în trecut');
                return;
            }
            
            // Verificare sărbători
            var holidays = <?php echo json_encode($holidays); ?>;
            var dateStr = info.dateStr;
            if (holidays.includes(dateStr)) {
                alert('Nu se pot face programări în zile de sărbătoare');
                return;
            }
            
            // Verificare weekend
            var dayOfWeek = clickedDate.getDay(); // 0=Sun, 1=Mon, etc.
            if (dayOfWeek === 0 || dayOfWeek === 6) { // Duminică sau Sâmbătă
                alert('Nu se pot face programări în weekend');
                return;
            }
            
            // Adăugare eveniment nou
            document.getElementById('modalTitle').textContent = 'Adăugare programare';
            document.getElementById('eventId').value = '';
            // Preselect first available 60-minute slot between 09:00 and 19:00
            var chosenDate = info.dateStr; // YYYY-MM-DD
            document.getElementById('eventDateTime').value = chosenDate + 'T09:00'; // temporary while computing
            document.getElementById('eventObjective').value = '';
            // fetch existing events for that date to compute first free slot
            console.log('salesplanning: fetching existing events for ' + chosenDate);
            fetch('salesgetplansfordate.php?date=' + encodeURIComponent(chosenDate), { credentials: 'same-origin' })
                .then(function(resp){
                    var ct = resp.headers.get('content-type') || '';
                    if (!resp.ok) return resp.text().then(t=>{ throw new Error('Server returned HTTP '+resp.status+': '+t); });
                    if (ct.indexOf('application/json') !== -1) return resp.json();
                    return resp.text().then(t=>{ throw new Error('Invalid JSON response:\n'+t); });
                })
                .then(function(data){
                    console.log('existing events for ' + chosenDate + ':', data);
                    // build existing intervals
                    var existing = [];
                    data.forEach(function(ev){
                        if (ev.data && ev.data_sfarsit) {
                            existing.push({ start: new Date(ev.data), end: new Date(ev.data_sfarsit) });
                        }
                    });
                    // slot search parameters
                    var duration = parseInt(document.getElementById('eventDurationMinutes') ? document.getElementById('eventDurationMinutes').value : 60) || 60;
                    var startHour = 9; var endHour = 19; // working window
                    var chosen = null;
                    for (var h = startHour; h < endHour; h++) {
                        var cand = new Date(chosenDate + 'T' + (h<10?('0'+h):h) + ':00:00');
                        var candEnd = new Date(cand.getTime() + duration*60000);
                        // ensure candEnd within working hours
                        if (candEnd.getHours() >= endHour && !(candEnd.getHours() === endHour && candEnd.getMinutes()===0)) continue;
                        var overlap = false;
                        for (var i=0;i<existing.length;i++){
                            if (cand < existing[i].end && candEnd > existing[i].start) { overlap = true; break; }
                        }
                        if (!overlap) { chosen = cand; break; }
                    }
                    if (chosen) {
                        var pad = function(n){ return (n<10? '0'+n : n); };
                        var val = chosenDate + 'T' + pad(chosen.getHours()) + ':' + pad(chosen.getMinutes());
                        console.log('chosen slot for ' + chosenDate + ' : ' + val);
                        document.getElementById('eventDateTime').value = val;
                    } else {
                        // leave default 09:00
                    }
                    // open modal after selection
                    openModalById('eventModal');
                })
                .catch(function(err){
                    console.error('Error fetching day events:', err);
                    // fallback to default 09:00 and open modal
                    openModalById('eventModal');
                });
            // continue setup of fields below is done in promise
            document.getElementById('eventObjective').value = '';
            document.getElementById('eventClient').value = '';
            document.getElementById('eventZone').value = '';
            if (document.getElementById('eventTipVizita')) document.getElementById('eventTipVizita').value = '';
            if (document.getElementById('eventDetalii')) document.getElementById('eventDetalii').value = '';
            if (document.getElementById('sendInvite')) document.getElementById('sendInvite').checked = false;
            if (document.getElementById('inviteEmail')) document.getElementById('inviteEmail').value = '';
            if (document.getElementById('eventDurationMinutes')) document.getElementById('eventDurationMinutes').value = 60;
            document.getElementById('eventFinalized').checked = false;
            document.getElementById('deleteEventBtn').style.display = 'none';
        },
        eventClick: function(info) {
            var eventDate = new Date(info.event.start);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (eventDate < today) {
                alert('Nu se pot edita evenimente din trecut');
                return;
            }
            
            // Editare eveniment
            document.getElementById('modalTitle').textContent = 'Editare programare';
            document.getElementById('eventId').value = info.event.id;
            document.getElementById('eventDateTime').value = info.event.start.toISOString().slice(0, 16);
            document.getElementById('eventObjective').value = info.event.title.split(' - ')[0]; // Assuming title starts with objective
            // Fetch additional details
            fetch('salesgetplandetail.php?eventId=' + info.event.id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('eventClient').value = data.programare_client || '';
                    document.getElementById('eventZone').value = data.programare_zona || '';
                    document.getElementById('eventFinalized').checked = data.programare_finalizata == 1;
                    // New fields
                    if (document.getElementById('eventTipVizita')) document.getElementById('eventTipVizita').value = data.programare_tipvizita || '';
                    if (document.getElementById('eventDetalii')) document.getElementById('eventDetalii').value = data.programare_detalii || '';
                    if (document.getElementById('sendInvite')) document.getElementById('sendInvite').checked = data.programare_invite == 1;
                    if (document.getElementById('inviteEmail')) document.getElementById('inviteEmail').value = data.programare_invite_email || '';
                    // Duration: prefer explicit duration, otherwise compute from start/end
                    if (document.getElementById('eventDurationMinutes')) {
                        var duration = data.programare_durata || null;
                        if (!duration && data.programare_data_inceput && data.programare_data_sfarsit) {
                            var start = new Date(data.programare_data_inceput);
                            var end = new Date(data.programare_data_sfarsit);
                            duration = Math.round((end - start) / 60000);
                        }
                        document.getElementById('eventDurationMinutes').value = duration || 60;
                    }
                });
            document.getElementById('deleteEventBtn').style.display = 'inline-block';
            openModalById('eventModal');
        }
    });
    calendar.render();
    } catch (e) {
        console.error('salesplanning error', e);
        alert('A apărut o eroare Javascript: ' + e.message + '\nVerifică consola pentru detalii.');
    }

    // Helper: validează o adresă email simplă
    function isValidEmailSimple(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Form submit pentru salvare
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validare adrese multiple: separate prin virgula sau ;
        var sendInvite = document.getElementById('sendInvite').checked;
        var inviteRaw = document.getElementById('inviteEmail').value.trim();
        if (sendInvite && inviteRaw.length > 0) {
            var parts = inviteRaw.split(/[,;\s]+/).filter(function(p){ return p.length>0; });
            for (var i=0;i<parts.length;i++) {
                if (!isValidEmailSimple(parts[i])) {
                    alert('Adresa invalidă: ' + parts[i] + '\nIntroduceți adrese separate prin virgulă sau punct și virgulă.');
                    return;
                }
            }
            // reconstruieste lista curata (virgule) pentru server
            document.getElementById('inviteEmail').value = parts.join(',');
        }

        var formData = new FormData(this);
        fetch('salessaveplan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            var ct = response.headers.get('content-type') || '';
            if (!response.ok) {
                return response.text().then(t => { throw new Error('Server returned HTTP ' + response.status + ': ' + t); });
            }
            if (ct.indexOf('application/json') !== -1) {
                return response.json();
            }
            return response.text().then(t => { throw new Error('Invalid JSON response:\n' + t); });
        })
                .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                closeModalById('eventModal');
            } else {
                alert('Eroare: ' + (data.message || JSON.stringify(data)));
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            alert('Eroare la salvare: verifica consola pentru detalii.');
        });
    });

    // Ștergere eveniment
    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        if (confirm('Sigur ștergi această programare?')) {
            var eventId = document.getElementById('eventId').value;
            fetch('salesdeleteplanedvisit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'eventId=' + eventId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    closeModalById('eventModal');
                } else {
                    alert('Eroare: ' + data.message);
                }
            });
        }
    });

    // Copiere evenimente
    document.getElementById('copyEventsBtn').addEventListener('click', function() {
        openModalById('copyModal');
    });

    document.getElementById('copyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        fetch('salescopyplannedvisits.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
                if (data.success) {
                calendar.refetchEvents();
                closeModalById('copyModal');
                alert('Evenimente copiate cu succes');
            } else {
                alert('Eroare: ' + data.message);
            }
        });
    });
});

// Accessibility: native handlers not relying on jQuery/Foundation
var eventModalEl = document.getElementById('eventModal');
if (eventModalEl) {
    eventModalEl.addEventListener('open.zf.reveal', function() {
        var first = eventModalEl.querySelector('input, select, textarea, button');
        if (first) first.focus();
    });
    eventModalEl.addEventListener('close.zf.reveal', function() {
        var safe = document.getElementById('copyEventsBtn'); if (safe) safe.focus();
    });
}
var copyModalEl = document.getElementById('copyModal');
if (copyModalEl) {
    copyModalEl.addEventListener('open.zf.reveal', function() {
        var first = copyModalEl.querySelector('input, select, textarea, button');
        if (first) first.focus();
    });
    copyModalEl.addEventListener('close.zf.reveal', function() {
        var safe = document.getElementById('copyEventsBtn'); if (safe) safe.focus();
    });
}
</script>

<?php include '../bottom.php'; ?>