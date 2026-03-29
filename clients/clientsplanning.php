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
            <select id="eventClient" name="eventClient" required>
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
            <input type="text" id="eventZone" name="eventZone" placeholder="Caută adresă (ex. Mall Vitan)">
            <input type="hidden" id="eventZonePlaceId" name="eventZonePlaceId">
            <input type="hidden" id="eventZoneLat" name="eventZoneLat">
            <input type="hidden" id="eventZoneLng" name="eventZoneLng">
            <small class="help-text">Introduceți o adresă; se va putea folosi pe telefon pentru direcții.</small>
            <div><a id="openMapsLink" href="#" target="_blank" style="display:none;" class="button small">Deschide în hărți</a></div>
            <?php if (empty($google_maps_api_key)) { ?>
            <div class="callout warning" role="status">Google Maps Autocomplete este <strong>dezactivat</strong>. Adaugă <code>$google_maps_api_key</code> în <code>_site/settings.local.php</code> și activează Google Places API pentru a folosi sugestiile de adresă.</div>
            <?php } ?>
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
/* Ensure Google Places dropdown is above modals and visible */
.pac-container {
    z-index: 100000 !important;
    position: fixed !important;
    max-height: 40vh; overflow-y: auto;
}
/* Custom fallback predictions dropdown */
.custom-predictions {
    z-index: 120000 !important;
    position: absolute;
    background: #fff;
    border: 1px solid #ccc;
    max-height: 40vh;
    overflow: auto;
    width: calc(100% - 2px);
}
.custom-pred-item { padding: 8px; cursor: pointer; }
.custom-pred-item.active, .custom-pred-item:hover { background: #eee; }
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
            if (document.getElementById('eventZonePlaceId')) document.getElementById('eventZonePlaceId').value = '';
            if (document.getElementById('eventZoneLat')) document.getElementById('eventZoneLat').value = '';
            if (document.getElementById('eventZoneLng')) document.getElementById('eventZoneLng').value = '';
            if (document.getElementById('openMapsLink')) document.getElementById('openMapsLink').style.display = 'none';
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
                    // populate hidden place fields if present
                    if (document.getElementById('eventZonePlaceId')) document.getElementById('eventZonePlaceId').value = data.programare_zone_place_id || data.programare_place_id || '';
                    if (document.getElementById('eventZoneLat')) document.getElementById('eventZoneLat').value = data.programare_zone_lat || data.programare_lat || '';
                    if (document.getElementById('eventZoneLng')) document.getElementById('eventZoneLng').value = data.programare_zone_lng || data.programare_lng || '';
                    // update maps link from loaded data
                    var mapsLink = document.getElementById('openMapsLink');
                    if (mapsLink) {
                        var lat = data.programare_zone_lat || data.programare_lat || '';
                        var lng = data.programare_zone_lng || data.programare_lng || '';
                        var pid = data.programare_zone_place_id || data.programare_place_id || '';
                        if (lat && lng) { mapsLink.href = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat + ',' + lng); mapsLink.style.display = 'inline-block'; }
                        else if (pid) { mapsLink.href = 'https://www.google.com/maps/search/?api=1&query=place_id:' + encodeURIComponent(pid); mapsLink.style.display = 'inline-block'; }
                        else { mapsLink.href = '#'; mapsLink.style.display = 'none'; }
                    }
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

    // Initialize Google Places Autocomplete for the zone input
    window.initPlaceAutocomplete = function() {
        try {
            var input = document.getElementById('eventZone');
            if (!input) return;
            // Use new PlaceAutocompleteElement when available (recommended), otherwise fallback to legacy Autocomplete
            window.handlePlace = function(place) {
                if (!place) return;
                if (place.formatted_address) input.value = place.formatted_address;
                var pid = place.place_id || '';
                var lat = '';
                var lng = '';
                if (place.geometry && place.geometry.location) {
                    lat = place.geometry.location.lat();
                    lng = place.geometry.location.lng();
                }
                if (document.getElementById('eventZonePlaceId')) document.getElementById('eventZonePlaceId').value = pid;
                if (document.getElementById('eventZoneLat')) document.getElementById('eventZoneLat').value = lat;
                if (document.getElementById('eventZoneLng')) document.getElementById('eventZoneLng').value = lng;
                var link = document.getElementById('openMapsLink');
                if (link) {
                    if (lat !== '' && lng !== '') { link.href = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat + ',' + lng); link.style.display = 'inline-block'; }
                    else if (pid !== '') { link.href = 'https://www.google.com/maps/search/?api=1&query=place_id:' + encodeURIComponent(pid); link.style.display = 'inline-block'; }
                    else { link.href = '#'; link.style.display = 'none'; }
                }
            }

            try {
                if (google.maps.places && google.maps.places.PlaceAutocompleteElement) {
                    // new preferred element (don't pass unknown 'fields' option)
                    var pae = null;
                    try {
                        pae = new google.maps.places.PlaceAutocompleteElement({ inputElement: input, componentRestrictions: { country: 'RO' } });
                    } catch (e) {
                        console.warn('PlaceAutocompleteElement constructor threw, falling back', e);
                        pae = null;
                    }

                    // If pae not available, fallback to legacy Autocomplete immediately
                    if (!pae) {
                        try {
                            var autocompleteFallback = new google.maps.places.Autocomplete(input, { types: ['geocode'], componentRestrictions: { country: 'RO' } });
                            autocompleteFallback.setFields(['place_id','formatted_address','geometry']);
                            autocompleteFallback.addListener('place_changed', function() { var place = autocompleteFallback.getPlace(); window.handlePlace && window.handlePlace(place); });
                            console.warn('Falling back to legacy Autocomplete (constructor absence)');
                        } catch (e2) {
                            console.error('Legacy Autocomplete also failed', e2);
                        }
                    } else {
                        // bind events with feature detection (APIs differ across versions)
                        try {
                            if (typeof pae.addListener === 'function') {
                                pae.addListener('place_changed', function() { onPaePlaceChanged(pae); }); // place_changed handled; details callback will call window.handlePlace
                            } else if (typeof pae.addEventListener === 'function') {
                                pae.addEventListener('place_changed', function() { onPaePlaceChanged(pae); });
                            } else if (typeof pae.on === 'function') {
                                pae.on('place_changed', function() { onPaePlaceChanged(pae); });
                            } else {
                                // fallback: try to poll for .getPlace when input changes
                                var lastVal = '';
                                input.addEventListener('input', function(){ if (input.value === lastVal) return; lastVal = input.value; setTimeout(function(){ try { var p = pae.getPlace && pae.getPlace(); if (p) onPaePlaceChanged(pae); } catch(e){} }, 300); });
                                console.warn('PlaceAutocompleteElement: event binding fallback active');
                            }
                        } catch (e) {
                            console.warn('PlaceAutocompleteElement binding failed, falling back to legacy Autocomplete', e);
                            try {
                                var autocompleteFallback2 = new google.maps.places.Autocomplete(input, { types: ['geocode'], componentRestrictions: { country: 'RO' } });
                                autocompleteFallback2.setFields(['place_id','formatted_address','geometry']);
                                autocompleteFallback2.addListener('place_changed', function() { var place = autocompleteFallback2.getPlace(); window.handlePlace && window.handlePlace(place); });
                            } catch (e3) {
                                console.error('Legacy Autocomplete also failed (bind path)', e3);
                            }
                        }
                    }

                    // helper to handle pae which may return an object or need details
                    function onPaePlaceChanged(source) {
                        var place = (typeof source.getPlace === 'function') ? source.getPlace() : source;
                        if (place && place.formatted_address && place.geometry) {
                            window.handlePlace && window.handlePlace(place);
                        } else if (place && place.place_id) {
                            var ps = new google.maps.places.PlacesService(document.createElement('div'));
                            ps.getDetails({ placeId: place.place_id, fields: ['place_id','formatted_address','geometry'] }, function(detail, status) {
                                if (status === google.maps.places.PlacesServiceStatus.OK) {
                                    window.handlePlace && window.handlePlace(detail);
                                } else {
                                    console.warn('Place details failed', status);
                                }
                            });
                        } else {
                            console.warn('PlaceAutocompleteElement returned no usable place', place);
                        }
                    }
                    console.log('Using PlaceAutocompleteElement (RO)');
                } else if (google.maps.places && google.maps.places.Autocomplete) {
                    var autocomplete = new google.maps.places.Autocomplete(input, { types: ['geocode'], componentRestrictions: { country: 'RO' } });
                    autocomplete.setFields(['place_id','formatted_address','geometry']);
                    autocomplete.addListener('place_changed', function() { var place = autocomplete.getPlace(); window.handlePlace && window.handlePlace(place); });
                    console.log('Using legacy Autocomplete (RO)');
                } else {
                    // No element available; leave AutocompleteService fallback for potential future work
                    var dbg = document.getElementById('mapsDebug'); if (dbg) { dbg.textContent = (dbg.textContent?dbg.textContent + ' | ':'') + 'Places API loaded but Autocomplete not available'; dbg.style.display='block'; dbg.className='callout warning'; }
                }
            } catch (e) { console.error('Place init error', e); }
        } catch (err) { console.error('initPlaceAutocomplete error', err); }
    };

    (function loadGooglePlacesAsync(){
        var key = '<?php echo isset($google_maps_api_key)?addslashes($google_maps_api_key):""; ?>';
        if (!key) {
            console.warn('Google Maps API key not set. Add $google_maps_api_key in _site/settings.local.php');
            return;
        }
        if (window.google && window.google.maps && window.google.maps.places) { window.initPlaceAutocomplete(); return; }
        var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.defer = true;
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key) + '&libraries=places&callback=initPlaceAutocomplete&language=ro&region=RO&loading=async&v=weekly';
        s.onerror = function(){ console.error('Google Maps script failed to load'); var d=document.getElementById('mapsDebug'); if (d) { d.textContent='Eroare la încărcarea Google Maps script: verifică cheia API sau restricțiile referer din Google Cloud.'; d.style.display='block'; d.className='callout warning'; } };
        s.onload = function(){ console.log('Google Maps script loaded'); };
        document.head.appendChild(s);
    })();
    var origInit = window.initPlaceAutocomplete;
    window.initPlaceAutocomplete = function() { try { if (origInit) origInit(); console.log('initPlaceAutocomplete executed');
            try {
                var input = document.getElementById('eventZone');
                if (input) {
                    input.addEventListener('input', function(ev){
                        var pac = document.querySelector('.pac-container');
                        var val = input.value.trim();
                        if (pac) console.log('pac container style:', window.getComputedStyle(pac));

                        // debounce and fallback to REST when native PAC missing or empty
                        clearTimeout(input._placeDebounce);
                        input._placeDebounce = setTimeout(function(){
                            if (val.length < 2) { hideFallback(); return; }
                            var pacHasItems = !!(document.querySelector('.pac-container .pac-item'));
                            if (!pac || !pacHasItems) {
                                fetchPredictionsFallback(val);
                            } else {
                                hideFallback();
                            }
                        }, 250);
                    });

                    input.addEventListener('keydown', function(ev){
                        var list = document.querySelector('.custom-predictions');
                        if (!list || list.style.display === 'none') return;
                        var active = list.querySelector('.active');
                        if (ev.key === 'ArrowDown') { ev.preventDefault(); var next = active ? (active.nextElementSibling || list.firstElementChild) : list.firstElementChild; setActive(next); }
                        if (ev.key === 'ArrowUp') { ev.preventDefault(); var prev = active ? (active.previousElementSibling || list.lastElementChild) : list.lastElementChild; setActive(prev); }
                        if (ev.key === 'Enter') { ev.preventDefault(); if (active) activateItem(active); }
                        if (ev.key === 'Escape') { hideFallback(); }
                    });
                }
            } catch (e2) { console.error('debug attach error', e2); }

            // Custom fallback UI and helpers
            var fallbackContainer = null;
            function ensureFallbackContainer() {
                if (fallbackContainer) return fallbackContainer;
                fallbackContainer = document.createElement('div');
                fallbackContainer.className = 'custom-predictions';
                fallbackContainer.style.position = 'absolute';
                fallbackContainer.style.zIndex = '120000';
                fallbackContainer.style.background = '#fff';
                fallbackContainer.style.border = '1px solid #ccc';
                fallbackContainer.style.width = (input ? input.offsetWidth + 'px' : '300px');
                fallbackContainer.style.maxHeight = '40vh';
                fallbackContainer.style.overflow = 'auto';
                fallbackContainer.style.display = 'none';
                var parent = input.parentNode; if (parent && getComputedStyle(parent).position === 'static') parent.style.position = 'relative';
                parent.appendChild(fallbackContainer);
                return fallbackContainer;
            }

            function hideFallback() { if (fallbackContainer) fallbackContainer.style.display = 'none'; }
            function setActive(node) { var list = fallbackContainer; if (!list) return; var prev = list.querySelector('.active'); if (prev) prev.classList.remove('active'); if (node) node.classList.add('active'); }

            // Try to get place details using the new Place API if available, otherwise fallback to PlacesService
            function getPlaceDetailsById(pid, cb) {
                // Validate pid first
                if (!pid || typeof pid !== 'string' || pid.trim() === '') {
                    cb(null, 'INVALID_ID');
                    return;
                }
                try {
                    if (google && google.maps && google.maps.places && typeof google.maps.places.Place === 'function') {
                        // try new Place constructor/fetch patterns
                        try {
                            var placeObj = new google.maps.places.Place({ placeId: pid, fields: ['place_id','formatted_address','geometry'] });
                            if (typeof placeObj.get === 'function') { placeObj.get(function(detail, status){ cb(detail, status); }); return; }
                            if (typeof placeObj.fetch === 'function') { placeObj.fetch().then(function(detail){ cb(detail, google.maps.places.PlacesServiceStatus.OK); }).catch(function(err){ console.warn('Place.fetch failed', err); cb(null,'ERROR'); }); return; }
                        } catch (e) {
                            console.warn('Place constructor/fetch not supported or threw', e);
                        }
                    }
                } catch(e){ console.warn('Place API feature detect error', e); }

                // fallback to legacy PlacesService.getDetails
                try {
                    if (google && google.maps && google.maps.places) {
                        var ps = new google.maps.places.PlacesService(document.createElement('div'));
                        ps.getDetails({ placeId: pid, fields: ['place_id','formatted_address','geometry'] }, cb);
                        return;
                    }
                } catch (e) { console.error('PlacesService.getDetails failed', e); }

                // ultimate fallback: call callback with null
                cb(null, 'ERROR');
            }

            function activateItem(node) {
                if (!node) return;
                var pid = node.getAttribute('data-placeid');
                var text = (node.textContent || node.innerText || '').trim();
                if (pid && typeof pid === 'string' && pid.trim() !== '') {
                    getPlaceDetailsById(pid, function(detail, status){
                        if ((typeof google !== 'undefined' && google.maps && google.maps.places && (status === google.maps.places.PlacesServiceStatus.OK || status === google.maps.places.PlacesServiceStatus.UNKNOWN)) || (detail && detail.place_id) ) {
                            window.handlePlace && window.handlePlace(detail);
                            hideFallback();
                        } else {
                            console.warn('getPlaceDetailsById did not return OK status', status, detail);
                            // try geocode fallback
                            geocodeTextFallback(text);
                        }
                    });
                } else {
                    // no place id available, use geocoder fallback
                    geocodeTextFallback(text);
                }
            }

            function geocodeTextFallback(text) {
                if (!text || !google || !google.maps || !google.maps.Geocoder) { console.warn('Geocode fallback not available for', text); return; }
                var ge = new google.maps.Geocoder();
                ge.geocode({ address: text }, function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK && results && results[0]) {
                        // construct minimal place-like object
                        var r = results[0];
                        var detail = { place_id: r.place_id || '', formatted_address: r.formatted_address || (r.formattedAddress || ''), geometry: r.geometry };
                        window.handlePlace && window.handlePlace(detail);
                        hideFallback();
                    } else {
                        console.warn('Geocode fallback failed', status);
                    }
                });
            }

            function renderFallback(predictions) {
                var c = ensureFallbackContainer(); c.innerHTML = '';
                predictions.forEach(function(p, idx){ var div = document.createElement('div'); div.className = 'custom-pred-item'; div.style.padding = '8px'; div.style.cursor = 'pointer'; div.textContent = p.description; div.setAttribute('data-placeid', p.place_id);
                    div.addEventListener('click', function(){ activateItem(div); });
                    div.addEventListener('mouseover', function(){ setActive(div); });
                    c.appendChild(div);
                });
                c.style.display = 'block';
            }

            function fetchPredictionsFallback(q) {
                try {
                    // Prefer new AutocompleteSuggestion API when available
                    if (google && google.maps && google.maps.places && typeof google.maps.places.AutocompleteSuggestion === 'function') {
                        try {
                            var sug = new google.maps.places.AutocompleteSuggestion();
                            if (typeof sug.getSuggestions === 'function') {
                                sug.getSuggestions({ input: q, componentRestrictions: { country: 'RO' }, types: ['establishment','geocode'] }, function(predictions, status){ if (predictions && predictions.length) { console.log('Using AutocompleteSuggestion'); renderFallback(predictions); } else hideFallback(); });
                                return;
                            }
                            if (typeof sug.getPlacePredictions === 'function') {
                                sug.getPlacePredictions({ input: q, componentRestrictions: { country: 'RO' }, types: ['establishment','geocode'] }, function(predictions, status){ if (predictions && predictions.length) { console.log('Using AutocompleteSuggestion (placePredictions)'); renderFallback(predictions); } else hideFallback(); });
                                return;
                            }
                        } catch (e) { console.warn('AutocompleteSuggestion call failed, falling back', e); }
                    }

                    // Fallback to server-side REST Autocomplete (avoid AutocompleteService to prevent deprecation warnings)
                    (function(){
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', '/common/places_autocomplete.php?q=' + encodeURIComponent(q), true);
                        xhr.timeout = 5000;
                        xhr.onreadystatechange = function(){
                            if (xhr.readyState !== 4) return;
                            if (xhr.status === 200) {
                                try {
                                    var resp = JSON.parse(xhr.responseText);
                                    if (resp && resp.predictions && resp.predictions.length) {
                                        window._placesMethod = 'REST';
                                        console.log('Using server REST Autocomplete');
                                        renderFallback(resp.predictions);
                                        return;
                                    }
                                } catch (e) { console.warn('REST autocomplete parse error', e); }
                            }
                            // If REST fails, attempt to use legacy AutocompleteService as last resort
                            try {
                                if (google && google.maps && google.maps.places && typeof google.maps.places.AutocompleteService === 'function') {
                                    var svc = new google.maps.places.AutocompleteService();
                                    svc.getPlacePredictions({ input: q, componentRestrictions: { country: 'RO' }, types: ['establishment','geocode'] }, function(predictions, status){
                                        if (status === google.maps.places.PlacesServiceStatus.OK && predictions && predictions.length) { renderFallback(predictions); }
                                        else { hideFallback(); }
                                    });
                                    return;
                                }
                            } catch (e) { console.warn('Legacy AutocompleteService also failed', e); }

                            console.warn('No suitable Autocomplete API available'); hideFallback();
                        };
                        xhr.ontimeout = function(){
                            // try legacy service if server request times out
                            try {
                                if (google && google.maps && google.maps.places && typeof google.maps.places.AutocompleteService === 'function') {
                                    var svc2 = new google.maps.places.AutocompleteService();
                                    svc2.getPlacePredictions({ input: q, componentRestrictions: { country: 'RO' }, types: ['establishment','geocode'] }, function(predictions, status){
                                        if (status === google.maps.places.PlacesServiceStatus.OK && predictions && predictions.length) { renderFallback(predictions); }
                                        else { hideFallback(); }
                                    });
                                    return;
                                }
                            } catch (e) { console.warn('Legacy AutocompleteService also failed on timeout', e); }
                            hideFallback();
                        };
                        xhr.send();
                    })();
                } catch (e) { console.error('fetchPredictionsFallback error', e); hideFallback(); }
            }

            function setActiveFromIndex(list, index) { if (!list) return; var nodes = list.children; for (var i=0;i<nodes.length;i++){ nodes[i].classList.toggle('active', i===index); } }

            document.addEventListener('click', function(ev){ if (!fallbackContainer) return; if (!fallbackContainer.contains(ev.target) && ev.target !== input) hideFallback(); });

            document.addEventListener('open.zf.reveal', function(){ if (typeof window.initPlaceAutocomplete === 'function') { try { window.initPlaceAutocomplete(); } catch(e){ console.error('reinit error', e); } } });
        } catch(e){ console.error('initPlaceAutocomplete wrapper error', e); } };

    } catch (e) { console.error('clientsplanning error', e); alert('A apărut o eroare Javascript: ' + e.message); }

    function isValidEmailSimple(email) { var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; return re.test(email); }

    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // client required validation
        var clientVal = document.getElementById('eventClient').value;
        if (!clientVal) { alert('Selectați un client înainte de a salva.'); document.getElementById('eventClient').focus(); return; }
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