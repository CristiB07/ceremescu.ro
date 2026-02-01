<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) session_start();
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF"); die;
}
$strPageTitle = "Planificare vizite clienți";
include '../dashboard/header.php';
$role = isset($_SESSION['function']) ? $_SESSION['function'] : '';
$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
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
                $clientsQuery = "SELECT ID_Client, Client_Denumire FROM clienti_date ORDER BY Client_Denumire";
                $clientsResult = ezpub_query($conn, $clientsQuery);
                while ($client = ezpub_fetch_array($clientsResult)) {
                    echo "<option value='{$client['ID_Client']}'>{$client['Client_Denumire']}</option>";
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
    try {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        firstDay: 1,
        locale: 'ro',
        buttonText: { today: 'Astăzi', month: 'Lună', week: 'Săptămână', day: 'Zi' },
        height: 'auto',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        slotMinTime: '08:00:00',
        slotMaxTime: '19:00:00',
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('clientsgetplans.php?start=' + encodeURIComponent(fetchInfo.startStr) + '&end=' + encodeURIComponent(fetchInfo.endStr))
                .then(response => {
                    var ct = response.headers.get('content-type') || '';
                    if (!response.ok) {
                        return response.text().then(t => { throw new Error('Server returned HTTP ' + response.status + ': ' + t); });
                    }
                    if (ct.indexOf('application/json') !== -1) return response.json();
                    return response.text().then(t => { throw new Error('Invalid JSON response:\n' + t); });
                })
                .then(data => successCallback(data))
                .catch(error => { console.error('Error loading events:', error); alert('Eroare la încărcare evenimente: verifică consola.'); failureCallback(error); });
        },
        dayCellDidMount: function(info) {
            var date = info.date;
            var dateStr = date.toISOString().split('T')[0];
            var holidays = <?php echo json_encode($holidays); ?>;
            var dayOfWeek = date.getDay();
            if (holidays.includes(dateStr) || dayOfWeek === 0 || dayOfWeek === 6) info.el.classList.add('disabled-day');
        },
        dateClick: function(info) {
            var clickedDate = new Date(info.dateStr);
            var today = new Date(); today.setHours(0,0,0,0);
            if (clickedDate < today) { alert('Nu se pot face programări în trecut'); return; }
            var holidays = <?php echo json_encode($holidays); ?>;
            var dateStr = info.dateStr;
            if (holidays.includes(dateStr)) { alert('Nu se pot face programări în zile de sărbătoare'); return; }
            var dayOfWeek = clickedDate.getDay(); if (dayOfWeek === 0 || dayOfWeek === 6) { alert('Nu se pot face programări în weekend'); return; }

            document.getElementById('modalTitle').textContent = 'Adăugare programare';
            document.getElementById('eventId').value = '';
            // determine first available 60-minute slot for the selected date
            (function(){
                var defaultStartHour = 9; var defaultEndHour = 19; var slotMinutes = 60;
                var dateStr = info.dateStr;
                // clear preselection while computing available slot
                document.getElementById('eventDateTime').value = '';
                fetch('clientsgetplansfordate.php?date=' + encodeURIComponent(dateStr), { credentials: 'same-origin' })
                .then(function(r){
                    var ct = r.headers.get('content-type') || '';
                    if (!r.ok) return r.text().then(function(t){ throw new Error('HTTP ' + r.status + ': ' + t); });
                    if (ct.indexOf('application/json') !== -1) return r.json();
                    return r.text().then(function(t){ throw new Error('Invalid JSON response:\n' + t); });
                })
                .then(function(existing){
                    console && console.debug && console.debug('existing events for', dateStr, existing);
                    // build occupied intervals [start,end) in minutes since midnight
                    var occupied = existing.map(function(ev){
                        var dstr = (ev.programare_data_sfarsit && ev.programare_data_sfarsit.length>0) ? ev.data : ev.data;
                        var dt = new Date(ev.data.replace(' ', 'T'));
                        var startMin = dt.getHours()*60 + dt.getMinutes();
                        var dur = (ev.programare_durata && !isNaN(ev.programare_durata)) ? parseInt(ev.programare_durata) : 60;
                        var endMin = startMin + dur;
                        return {start: startMin, end: endMin};
                    });
                    occupied.sort(function(a,b){ return a.start - b.start; });
                    // function to test overlap
                    function overlaps(s,e, a) { for (var i=0;i<a.length;i++) { if (!(e <= a[i].start || s >= a[i].end)) return true; } return false; }
                    var slotStartMin = defaultStartHour*60;
                    var slotEndLimit = defaultEndHour*60;
                    var chosen = null;
                    while (slotStartMin + slotMinutes <= slotEndLimit) {
                        if (!overlaps(slotStartMin, slotStartMin + slotMinutes, occupied)) { chosen = slotStartMin; break; }
                        slotStartMin += slotMinutes;
                    }
                    if (chosen === null) chosen = defaultStartHour*60; // fallback
                    console && console.debug && console.debug('chosen slot minutes', chosen);
                    var hh = String(Math.floor(chosen/60)).padStart(2,'0');
                    var mm = String(chosen%60).padStart(2,'0');
                    document.getElementById('eventDateTime').value = dateStr + 'T' + hh + ':' + mm;
                })
                .catch(function(err){ console && console.error && console.error('slot calc error', err); document.getElementById('eventDateTime').value = info.dateStr + 'T09:00'; });
            })();
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
            openModal('eventModal');
        },
        eventClick: function(info) {
            var eventDate = new Date(info.event.start);
            var today = new Date(); today.setHours(0,0,0,0);
            if (eventDate < today) { alert('Nu se pot edita evenimente din trecut'); return; }

            document.getElementById('modalTitle').textContent = 'Editare programare';
            document.getElementById('eventId').value = info.event.id;
            document.getElementById('eventDateTime').value = info.event.start.toISOString().slice(0,16);
            document.getElementById('eventObjective').value = info.event.title.split(' - ')[0] || '';
            fetch('clientsgetplandetail.php?eventId=' + info.event.id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('eventClient').value = data.client_vizita || data.programare_client || '';
                    document.getElementById('eventZone').value = data.programare_zona || data.zona || '';
                    document.getElementById('eventFinalized').checked = (data.programare_finalizata == 1 || data.finalizata == 1);
                    if (document.getElementById('eventTipVizita')) document.getElementById('eventTipVizita').value = data.programare_tipvizita || data.tip_vizita || '';
                    if (document.getElementById('eventDetalii')) document.getElementById('eventDetalii').value = data.programare_detalii || data.detalii || data.scop_vizita || '';
                    if (document.getElementById('sendInvite')) document.getElementById('sendInvite').checked = data.programare_invite == 1;
                    if (document.getElementById('inviteEmail')) document.getElementById('inviteEmail').value = data.programare_invite_email || '';
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
            openModal('eventModal');
        }
    });
    calendar.render();
    } catch (e) { console.error('clientsplanning error', e); alert('A apărut o eroare Javascript: ' + e.message); }

    function isValidEmailSimple(email) { var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; return re.test(email); }

    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var sendInvite = document.getElementById('sendInvite').checked;
        var inviteRaw = document.getElementById('inviteEmail').value.trim();
        if (sendInvite && inviteRaw.length > 0) {
            var parts = inviteRaw.split(/[,;\s]+/).filter(function(p){ return p.length>0; });
            for (var i=0;i<parts.length;i++) { if (!isValidEmailSimple(parts[i])) { alert('Adresa invalidă: ' + parts[i]); return; } }
            document.getElementById('inviteEmail').value = parts.join(',');
        }
        var formData = new FormData(this);
        fetch('clientssaveplan.php', { method: 'POST', body: formData })
        .then(response => {
            var ct = response.headers.get('content-type') || '';
            if (!response.ok) return response.text().then(t => { throw new Error('Server returned HTTP ' + response.status + ': ' + t); });
            if (ct.indexOf('application/json') !== -1) return response.json();
            return response.text().then(t => { throw new Error('Invalid JSON response:\n' + t); });
        })
        .then(data => { if (data.success) { calendar.refetchEvents(); closeModal('eventModal'); } else { alert('Eroare: ' + (data.message || JSON.stringify(data))); } })
        .catch(error => { console.error('Save error:', error); alert('Eroare la salvare: verifică consola.'); });
    });

    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        if (confirm('Sigur ștergi această programare?')) {
            var eventId = document.getElementById('eventId').value;
            fetch('clientsdeleteplanedvisit.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'eventId=' + eventId })
            .then(response => response.json()).then(data => { if (data.success) { calendar.refetchEvents(); closeModal('eventModal'); } else { alert('Eroare: ' + data.message); } });
        }
    });

    document.getElementById('copyEventsBtn').addEventListener('click', function() { openModal('copyModal'); });

    document.getElementById('copyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        fetch('clientscopyplannedvisits.php', { method: 'POST', body: formData })
        .then(response => response.json()).then(data => { if (data.success) { calendar.refetchEvents(); $('#copyModal').foundation('close'); alert('Evenimente copiate cu succes'); } else { alert('Eroare: ' + data.message); } });
    });
});

// Modal helpers to work before jQuery loads
function openModal(id) {
    if (window.$ && typeof window.$ === 'function' && window.$('#' + id).foundation) {
        try { window.$('#' + id).foundation('open'); return; } catch (e) { /* fallback */ }
    }
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden','false');
    document.dispatchEvent(new CustomEvent('open.zf.reveal', { detail: { id: id } }));
}
function closeModal(id) {
    if (window.$ && typeof window.$ === 'function' && window.$('#' + id).foundation) {
        try { window.$('#' + id).foundation('close'); return; } catch (e) { /* fallback */ }
    }
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('is-open');
    el.setAttribute('aria-hidden','true');
    document.dispatchEvent(new CustomEvent('close.zf.reveal', { detail: { id: id } }));
}

// Accessibility focus management using native events
document.addEventListener('open.zf.reveal', function(e) {
    var id = e.detail && e.detail.id ? e.detail.id : null;
    var modal = id ? document.getElementById(id) : null;
    if (!modal) return;
    var inputs = modal.querySelectorAll('input, select, textarea, button');
    for (var i=0;i<inputs.length;i++) { if (inputs[i].offsetParent !== null) { inputs[i].focus(); break; } }
});
document.addEventListener('close.zf.reveal', function(e) {
    var btn = document.getElementById('copyEventsBtn'); if (btn) btn.focus();
});
document.addEventListener('open.zf.reveal', function(e) { if (e.detail && e.detail.id === 'copyModal') { var modal = document.getElementById('copyModal'); if (!modal) return; var inputs = modal.querySelectorAll('input, select, textarea, button'); for (var i=0;i<inputs.length;i++) { if (inputs[i].offsetParent !== null) { inputs[i].focus(); break; } } } });
document.addEventListener('close.zf.reveal', function(e) { var btn = document.getElementById('copyEventsBtn'); if (btn) btn.focus(); });
</script>

<?php include '../bottom.php'; ?>