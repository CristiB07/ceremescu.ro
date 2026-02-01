<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare vizite prospecți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$clearence=$_SESSION['function'];

$month= date('m');
$year=date('Y');
$day = date('d');

if ((isset( $_GET['aloc'])) && !empty( $_GET['aloc'])){
$aloc=$_GET['aloc'];}
else{
$aloc=0;}
if ((isset( $_GET['cl'])) && !empty( $_GET['cl'])){
$cl=$_GET['cl'];}
else{
$cl=0;}
if ((isset( $_GET['yr'])) && !empty( $_GET['yr'])){
$fyear=$_GET['yr'];
$year=$fyear;
}
else{
$fyear=0;}
if ((isset( $_GET['fmonth'])) && !empty( $_GET['fmonth'])){
$fmonth=$_GET['fmonth'];}
else{
$fmonth=0;}
?>
<link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM sales_vizite_prospecti WHERE ID_vizita=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"salesvisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
check_inject();
If ($_GET['mode']=="new"){
//insert new user
$client=trim($_POST["client_vizita"],"saleseditprospects.php?mode=edit&cID=");

	$mSQL = "INSERT INTO sales_vizite_prospecti(";
	$mSQL = $mSQL . "client_vizita,";
 	$mSQL = $mSQL . "tip_vizita,";
 	$mSQL = $mSQL . "programare_tipvizita,";
 	$mSQL = $mSQL . "programare_detalii,";
	$mSQL = $mSQL . "data_vizita,";
	$mSQL = $mSQL . "scop_vizita,";
	$mSQL = $mSQL . "alocat,";
	$mSQL = $mSQL . "urmatoarea_vizita,";
	$mSQL = $mSQL . "revizitare,";
	$mSQL = $mSQL . "programare_id,";
	$mSQL = $mSQL . "observatii_vizita)";

	$mSQL = $mSQL . "values(";
	$mSQL = $mSQL . "'" .$client . "', ";
 	$mSQL = $mSQL . "'" .$_POST["tip_vizita"] . "', ";
 	$mSQL = $mSQL . "'" . mysqli_real_escape_string($conn, substr($_POST["programare_tipvizita"] ?? '',0,255)) . "', ";
 	$mSQL = $mSQL . "'" . mysqli_real_escape_string($conn, substr($_POST["programare_detalii"] ?? '',0,500)) . "', ";
	$mSQL = $mSQL . "'" .$_POST["data_vizita"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["scop_vizita"] . "', ";
	$mSQL = $mSQL . "'" .$uid . "', ";
	$mSQL = $mSQL . "'" .$_POST["urmatoarea_vizita"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["revizitare"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["programare_id"] . "', ";
	$mSQL = $mSQL . "'" .$_POST["observatii_vizita"] . "') ";

//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"salesvisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$client=trim($_POST["client_vizita"],"saleseditprospects.php?mode=edit&cID=");
$strWhereClause = " WHERE sales_vizite_prospecti.ID_vizita=" . $_GET["cID"] . ";";
$query= "UPDATE sales_vizite_prospecti SET sales_vizite_prospecti.client_vizita='" .$client . "' ," ;
$query= $query . "sales_vizite_prospecti.scop_vizita='" . $_POST["scop_vizita"] . "' ," ;
$query= $query . "sales_vizite_prospecti.tip_vizita='" .$_POST["tip_vizita"] . "' ," ;
$query= $query . "sales_vizite_prospecti.data_vizita='" .$_POST["data_vizita"] . "' ," ;
$query= $query . "sales_vizite_prospecti.urmatoarea_vizita='" .$_POST["urmatoarea_vizita"] . "' ," ;
$query= $query . "sales_vizite_prospecti.revizitare='" .$_POST["revizitare"] . "' ," ;
$query= $query . "sales_vizite_prospecti.programare_tipvizita='" . mysqli_real_escape_string(
    $conn,
    substr($_POST["programare_tipvizita"] ?? '', 0, 255)
) . "' ," ;
$query= $query . "sales_vizite_prospecti.programare_detalii='" . mysqli_real_escape_string(
    $conn,
    substr($_POST["programare_detalii"] ?? '', 0, 500)
) . "' ," ;
$query= $query . "sales_vizite_prospecti.programare_id='" .$_POST["programare_id"] . "' ," ;
$query= $query . " sales_vizite_prospecti.observatii_vizita='".$_POST["observatii_vizita"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"salesvisitreports.php\"
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}
}
}
else {
?>
        <script>
        function removeIFrame() {
            document.getElementById('editframe').style.display = 'block';
        }
        </script>
        <?php
If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="salesvisitreports.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post"  action="salesvisitreports.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strDate?>
                        <input id="data_vizita" name="data_vizita" type="date" class="required" value="<?php echo date('Y-m-d'); ?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-4 cell">
                <label><?php echo $strLinkedAppointment?>
                    <select id="programare_select" name="programare_id" class="required">
                        <option value="0"><?php echo $strNoAppointment?></option>
                        <?php
                        $query="SELECT programare_id, programare_data, programare_obiectiv, programare_client, programare_tipvizita, programare_detalii FROM sales_programari WHERE programare_user='$uid' AND DATE(programare_data) = CURDATE() ORDER BY programare_data DESC";
                        $result=ezpub_query($conn,$query);
                        while ($row=ezpub_fetch_array($result)){
                        ?>
                        <option value="<?php echo $row['programare_id']?>" data-client="<?php echo $row['programare_client']?>" data-tipvizita="<?php echo htmlspecialchars($row['programare_tipvizita'] ?? '', ENT_QUOTES) ?>" data-detalii="<?php echo htmlspecialchars($row['programare_detalii'] ?? '', ENT_QUOTES) ?>"><?php echo $row['programare_obiectiv']?></option>
                        <?php
                        }
                        ?>
                   </select>     
            </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strClient?>  </label>
                       <select id="client_vizita_select" name="client_vizita" class="required" onchange="document.getElementById('editprospect').src = this.value">
                            <option value=""><?php echo $strClient?></option>
                            <?php 
                             if ($clearence=="MANAGER")
                                { $sql="SELECT prospect_ID, prospect_denumire from sales_prospecti";}
                                 else
                                { $sql="SELECT prospect_ID, prospect_denumire from sales_prospecti where prospect_aloc='$code'";}
                                 $result=ezpub_query($conn,$sql);
                                 while ($rss=ezpub_fetch_array($result)){
                             ?>
                                <option value="saleseditprospects.php?mode=edit&cID=<?php echo $rss["prospect_ID"]?>"><?php echo $rss["prospect_denumire"]?></option>
                              <?php
                              }
                             ?>
                            </select>
                    <input class="button" name="Close" type="button" value="<?php echo $strEdit?>" onClick="removeIFrame()" tabindex="10" />
                </div>
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strType?></label>
                        <input type="radio" name="tip_vizita" value="0" checked id="initiala"><label for="initiala"><?php echo $strInitial?></label>
                        <input name="tip_vizita" type="radio" value="1" id="followup"><label for="followup"><?php echo $strFollowup?></label>
                </div>
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strRevisit?></label>
                        <input type="radio" name="revizitare" value="0" checked id="da"><label for="da"><?php echo $strYes?></label>
                        <input name="revizitare" type="radio" value="1" id="nu"><label for="nu"><?php echo $strNo?></label>
            </div>
            </div>
            
            <div class="grid-x grid-margin-x" id="editframe" style="display: none;">
                <div class="large-12 medium-12 small-12 cell">
                    <iframe name="iframe" id="editprospect" src="" width="100%" border="0" frameBorder="0" scrolling="no" onload="resizeIframe(this)"></iframe>
                </div>
            </div>
            <script>
            // La încărcare, setează src-ul iframe-ului dacă există o opțiune selectată
            window.addEventListener('DOMContentLoaded', function() {
                var sel = document.getElementById('client_vizita_select');
                if (sel && sel.value && sel.value !== '') {
                    document.getElementById('editprospect').src = sel.value;
                }
                // If a programare is already selected, populate its details
                var progSel = document.getElementById('programare_select');
                if (progSel && progSel.value && progSel.value !== '0') {
                    var selected = progSel.options[progSel.selectedIndex];
                    var det = selected.getAttribute('data-detalii') || '';
                    var tip = selected.getAttribute('data-tipvizita') || '';
                    var detInput = document.querySelector('input[name="programare_detalii"]') || document.querySelector('textarea[name="programare_detalii"]');
                    if (detInput) detInput.value = det;
                    var tipInput = document.querySelector('#programare_tipvizita_input');
                    if (tipInput) {
                        // try to select option by value/text normalized (remove diacritics)
                        trySelectOption(tipInput, tip);
                    }
                }
            });

            // Funcție pentru a încărca programările pentru o dată dată
            function loadProgramari(date) {
                var url = './salesgetplansfordate.php?date=' + encodeURIComponent(date);
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        var select = document.getElementById('programare_select');
                        select.innerHTML = '<option value="0">' + <?php echo json_encode($strNoAppointment); ?> + '</option>';
                        if (!Array.isArray(data)) {
                            console.error('Unexpected response from server', data);
                            return;
                        }
                        data.forEach(function(prog) {
                            var option = document.createElement('option');
                            option.value = prog.id;
                            option.textContent = prog.obiectiv;
                            option.setAttribute('data-client', prog.client_id);
                            if (prog.tipvizita) option.setAttribute('data-tipvizita', prog.tipvizita);
                            if (prog.detalii) option.setAttribute('data-detalii', prog.detalii);
                            select.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading programari:', error);
                    });
            }

            // Când se schimbă data, încarcă programările
            var dataVizitaEl = document.getElementById('data_vizita');
            if (dataVizitaEl) {
                dataVizitaEl.addEventListener('change', function() {
                    var date = this.value;
                    if (date) {
                        loadProgramari(date);
                    }
                });
                // La încărcare, încarcă programările pentru data curentă
                if (dataVizitaEl.value) {
                    loadProgramari(dataVizitaEl.value);
                }
            }

            // Când se selectează o programare, setează clientul și scopul
            document.getElementById('programare_select').addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var clientId = selectedOption.getAttribute('data-client');
                if (clientId) {
                    document.getElementById('client_vizita_select').value = 'saleseditprospects.php?mode=edit&cID=' + clientId;
                    // Trigger change event pentru iframe
                    var event = new Event('change');
                    document.getElementById('client_vizita_select').dispatchEvent(event);
                }
                // Setează scopul vizitei cu obiectivul programării
                var obiectiv = selectedOption.textContent;
                if (this.value !== '0' && obiectiv) {
                    document.querySelector('input[name="scop_vizita"]').value = obiectiv;
                } else {
                    document.querySelector('input[name="scop_vizita"]').value = '';
                }
                // Set programare details and type
                var detalii = selectedOption.getAttribute('data-detalii') || '';
                var tip = selectedOption.getAttribute('data-tipvizita') || '';
                var detInput = document.querySelector('input[name="programare_detalii"]') || document.querySelector('textarea[name="programare_detalii"]');
                if (detInput) detInput.value = detalii;
                var tipInput = document.querySelector('#programare_tipvizita_input');
                if (tipInput) {
                    trySelectOption(tipInput, tip);
                }
            });
            
            // Helper: normalize string (remove diacritics) for robust comparison
            function normalizeText(s) {
                if (!s) return '';
                try {
                    return s.toString().normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim();
                } catch (e) {
                    return s.toString().replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
                }
            }

            function trySelectOption(selectEl, value) {
                var target = value || '';
                // exact match first
                for (var i = 0; i < selectEl.options.length; i++) {
                    if (selectEl.options[i].value === target) {
                        selectEl.selectedIndex = i;
                        return;
                    }
                }
                // normalized match on value or text
                var normTarget = normalizeText(target);
                for (var i = 0; i < selectEl.options.length; i++) {
                    var opt = selectEl.options[i];
                    if (normalizeText(opt.value) === normTarget || normalizeText(opt.text) === normTarget) {
                        selectEl.selectedIndex = i;
                        return;
                    }
                }
                // fallback: set value (will clear selection if no match)
                selectEl.value = value;
            }
            </script>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strScope?>
                        <input name="scop_vizita" type="text" class="required" value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisitDetails?>
                        <input name="programare_detalii" type="text" class="required"  value="" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strType?>
                        <select name="programare_tipvizita" id="programare_tipvizita_input">
                            <option value="">--</option>
                            <option value="Fizica">Fizică</option>
                            <option value="Apel telefonic">Apel telefonic</option>
                            <option value="Intalnire video">Întâlnire video</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="observatii_vizita" class="simple-html-editor" rows="3"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strNextVisit?>
                        <textarea name="urmatoarea_vizita" class="simple-html-editor" rows="3"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>

        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM sales_vizite_prospecti WHERE ID_vizita=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="salesvisitreports.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <form method="post" action="salesvisitreports.php?mode=edit&cID=<?php echo $row['ID_vizita']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strDate?>
                        <input id="data_vizita_edit" name="data_vizita" type="date" class="required"
                            value="<?php echo $row["data_vizita"]?>" />
                    </label>
                </div>
                <div class="large-2 medium-2 small-4 cell">
                <label><?php echo $strLinkedAppointment?>
                    <select id="programare_select_edit" name="programare_id" class="required">
                        <option value="0"><?php echo $strNoAppointment?></option>
                        <?php
                        $query="SELECT programare_id, programare_data, programare_obiectiv, programare_client, programare_tipvizita, programare_detalii FROM sales_programari WHERE programare_user='$uid' AND DATE(programare_data) = DATE('{$row["data_vizita"]}') ORDER BY programare_data DESC";
                        $result_prog=ezpub_query($conn,$query);
                        while ($row_prog=ezpub_fetch_array($result_prog)){
                        ?>
                        <option value="<?php echo $row_prog['programare_id']?>" data-client="<?php echo $row_prog['programare_client']?>" data-tipvizita="<?php echo htmlspecialchars($row_prog['programare_tipvizita'] ?? '', ENT_QUOTES) ?>" data-detalii="<?php echo htmlspecialchars($row_prog['programare_detalii'] ?? '', ENT_QUOTES) ?>" <?php if ($row_prog['programare_id'] == $row['programare_id']) echo 'selected'; ?>><?php echo $row_prog['programare_obiectiv']?></option>
                        <?php
                        }
                        ?>
                        </select>
            </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strClient?>
                        <select name="client_vizita" class="required">
          <?php 
		    $sql="SELECT prospect_ID, prospect_denumire from sales_prospecti where prospect_aloc='$code' OR prospect_ID = '{$row["client_vizita"]}' ORDER BY prospect_denumire";
            $result=ezpub_query($conn,$sql);
	    while ($rss=ezpub_fetch_array($result)){
	?>
        <option <?php if ($row["client_vizita"]==$rss["prospect_ID"]) echo "selected"; ?> value="<?php echo $rss["prospect_ID"]?>"><?php echo $rss["prospect_denumire"]?></option>
                            <?php
}?>
                        </select>
                    </label>
                </div>
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strType?></label>
                        <input type="radio" name="tip_vizita" value="0" <?php if ($row["tip_vizita"]==0) echo "checked"?> id="initiala"><label for="initiala"><?php echo $strInitial?></label>
                        <input name="tip_vizita" type="radio" value="1" <?php if ($row["tip_vizita"]==1 )echo "checked"?> id="followup"><label for="followup"><?php echo $strFollowup?></label>
                    
                </div>
                <div class="large-2 medium-2 small-4 cell">
                    <label><?php echo $strRevisit?></label>
                        <input type="radio" name="revizitare" value="0" checked id="da" <?php if ($row["revizitare"]==0) echo "checked"?>><label for="da"><?php echo $strYes?></label>
                        <input name="revizitare" type="radio" value="1" id="nu" <?php if ($row["revizitare"]==1) echo "checked"?>><label for="nu"><?php echo $strNo?></label>
            </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strScope?>
                        <input name="scop_vizita" type="text" class="required" value="<?php echo $row["scop_vizita"]?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strVisitDetails?>
                        <input name="programare_detalii" type="text" class="required"  value="<?php echo $row["programare_detalii"]?>" />
                    </label>
                </div>
                <div class="large-4 medium-4 small-4 cell">
                    <label><?php echo $strType?>
                        <select name="programare_tipvizita" id="programare_tipvizita_input_edit">
                            <option value="">--</option>
                            <option value="Fizica" <?php if (($row["programare_tipvizita"] ?? '')=='Fizica') echo 'selected'; ?>>Fizică</option>
                            <option value="Apel telefonic" <?php if (($row["programare_tipvizita"] ?? '')=='Apel telefonic') echo 'selected'; ?>>Apel telefonic</option>
                            <option value="Intalnire video" <?php if (($row["programare_tipvizita"] ?? '')=='Intalnire video') echo 'selected'; ?>>Întâlnire video</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strDetails?>
                        <textarea name="observatii_vizita" class="simple-html-editor" rows="3"><?php echo $row["observatii_vizita"]?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strNextVisit?>
                        <textarea name="urmatoarea_vizita" class="simple-html-editor" rows="3"><?php echo $row["urmatoarea_vizita"]?></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text-center"> <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <script>
        // Funcție pentru a încărca programările pentru o dată dată în edit
        function loadProgramariEdit(date) {
            var url = './salesgetplansfordate.php?date=' + encodeURIComponent(date);
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    var select = document.getElementById('programare_select_edit');
                    select.innerHTML = '<option value="0">' + <?php echo json_encode($strNoAppointment); ?> + '</option>';
                    if (!Array.isArray(data)) {
                        console.error('Unexpected response from server', data);
                        return;
                    }
                    data.forEach(function(prog) {
                        var option = document.createElement('option');
                        option.value = prog.id;
                        option.textContent = prog.obiectiv;
                        option.setAttribute('data-client', prog.client_id);
                        if (prog.tipvizita) option.setAttribute('data-tipvizita', prog.tipvizita);
                        if (prog.detalii) option.setAttribute('data-detalii', prog.detalii);
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading programari:', error));
        }

        // Când se schimbă data în edit, încarcă programările
        var dataVizitaEditEl = document.getElementById('data_vizita_edit');
        if (dataVizitaEditEl) {
            dataVizitaEditEl.addEventListener('change', function() {
                var date = this.value;
                if (date) {
                    loadProgramariEdit(date);
                }
            });
            // La încărcare în edit, încarcă programările pentru data existentă
            if (dataVizitaEditEl.value) {
                loadProgramariEdit(dataVizitaEditEl.value);
            }
        }

        // Când se selectează o programare în edit, setează clientul și scopul
        document.getElementById('programare_select_edit').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var clientId = selectedOption.getAttribute('data-client');
            if (clientId) {
                // Pentru edit, selectul client are value=prospect_ID, nu URL
                var selectClient = document.querySelector('form[action*="mode=edit"] select[name="client_vizita"]');
                if (selectClient) {
                    selectClient.value = clientId;
                }
            }
            // Setează scopul vizitei cu obiectivul programării
            var obiectiv = selectedOption.textContent;
            var inputScop = document.querySelector('form[action*="mode=edit"] input[name="scop_vizita"]');
            if (inputScop) {
                if (this.value !== '0' && obiectiv) {
                    inputScop.value = obiectiv;
                } else {
                    inputScop.value = '';
                }
            }
            // populate programare details and type in edit form
            var detalii = selectedOption.getAttribute('data-detalii') || '';
            var tip = selectedOption.getAttribute('data-tipvizita') || '';
            var detInput = document.querySelector('form[action*="mode=edit"] input[name="programare_detalii"]') || document.querySelector('form[action*="mode=edit"] textarea[name="programare_detalii"]');
            if (detInput) detInput.value = detalii;
            var tipInput = document.querySelector('#programare_tipvizita_input_edit');
            if (tipInput) tipInput.value = tip;
        });
        </script>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="view")
{
	?> <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="salesvisitreports.php" class="button"><?php echo $strBack?>&nbsp;<i
                            class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <?php

$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, consultanta_iso, consultanta_mediu, consultanta_haccp, analize, gdpr, altele,
prospect_denumire, prospect_ID
FROM sales_vizite_prospecti, sales_prospecti 
WHERE ID_vizita=$_GET[cID] AND prospect_ID=client_vizita";
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
echo ezpub_error($conn);
$row=ezpub_fetch_array($result);
	    		echo"<table id=\"rounded-corner\" summary=\"$strClients\" width=\"100%\">
				<tr><td>$strName</td><td>$row[prospect_denumire]</td></tr>
				<tr><td>$strDate</td><td>$row[data_vizita]</td></tr>
				<tr><td>$strScope</td><td>$row[scop_vizita]</td></tr>
				<tr><td>$strDetails</td><td>$row[observatii_vizita]</td></tr>
				<tr><td>$strNextVisit</td><td>$row[urmatoarea_vizita]</td></tr>
			<tr><td class=\"rounded-foot-left\"></td><td class=\"rounded-foot-right\">&nbsp;</td></tr></tfoot></table>";
}
else
{
	?>
        <script language="JavaScript" type="text/JavaScript">
            <!-- jump menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
        </script>
        <div class="grid-x grid-padding-x ">
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strSeenBy?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="salesvisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT * FROM date_utilizatori WHERE utilizator_Function='SALES' ORDER By utilizator_Nume ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['aloc'])) && !empty($_GET['aloc'])){
			If ($seenby['strSeenBy']==$_GET['aloc']) {
			echo"<option selected value=\"salesvisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['strUserName']."</option>";}
			else{echo"<option value=\"salesvisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}}
			else {echo"<option value=\"salesvisitreports.php?cl=$cl&fmonth=$fmonth&yr=$year&aloc=".$seenby['utilizator_Code']."\">". $seenby['utilizator_Nume']." ". $seenby['utilizator_Prenume']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strClient?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="salesvisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			$query7="SELECT DISTINCT client_vizita, prospect_denumire, prospect_ID	FROM sales_prospecti, sales_vizite_prospecti WHERE prospect_ID=client_vizita ORDER By prospect_denumire ASC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
			if ((isset($_GET['cl'])) && !empty($_GET['cl'])){
			If ($seenby['client_vizita']==$_GET['cl']) {
			echo"<option selected value=\"salesvisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}
			else{echo"<option value=\"salesvisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}}
			else {echo"<option value=\"salesvisitreports.php?aloc=$aloc&fmonth=$fmonth&yr=$year&cl=".$seenby['client_vizita']."\">". $seenby['prospect_denumire']."</option>";}
			}
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strMonth?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option value="00" selected>--</option>
                        <?php for ( $m = 1; $m <= 12; $m ++) {

     		//Create an option With the numeric value of the month
			$dateObj   = DateTime::createFromFormat('!m', $m);
		$formatter = new IntlDateFormatter("ro_RO",
                                    IntlDateFormatter::FULL, 
                                    IntlDateFormatter::FULL, 
                                    'Europe/Bucharest', 
                                    IntlDateFormatter::GREGORIAN,
                                    'MMMM');
$monthname = $formatter->format($dateObj);
				echo "<option value=\"salesvisitreports.php?aloc=$aloc&cl=$cl&yr=$year&fmonth=".$m."\">$monthname</option>";}
				 
			?>
                    </select></label>
            </div>
            <div class="large-3 medium-3 cell">
                <label> <?php echo $strYear?>
                    <select name="menu1" onChange="MM_jumpMenu('parent',this,1)">
                        <option
                            value="salesvisitreports.php?cl=<?php echo $cl?>&fmonth=<?php echo $fmonth?>&yr=<?php echo $year?>&aloc=<?php echo $aloc ?>"
                            selected><?php echo $strPick?></option>
                        <?php
			 			$query7="SELECT DISTINCT YEAR(data_vizita) as iyear FROM sales_vizite_prospecti ORDER By YEAR(data_vizita) DESC";
			$result7=ezpub_query($conn,$query7);
			while($seenby = ezpub_fetch_array($result7)){
						if ((isset($_GET['yr'])) && !empty($_GET['yr'])){
			If ($seenby['iyear']==$_GET['yr']) {
			echo"<option selected value=\"salesvisitreports.php?aloc=$aloc&cl$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			else{echo"<option value=\"salesvisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}}
			else {
			if ($year==$seenby['iyear']) 
			{echo "<option value=\"salesvisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']." \" selected >". $seenby['iyear']."</option>";}
			else {echo"<option value=\"salesvisitreports.php?aloc=$aloc&cl=$cl&fmonth=$fmonth&yr=".$seenby['iyear']."\">". $seenby['iyear']."</option>";}
			}
			}
			 ?>
                    </select></label>
            </div>
        </div>

        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"salesvisitreports.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";
$query="SELECT ID_vizita, client_vizita, alocat, data_vizita, tip_vizita, scop_vizita, observatii_vizita, urmatoarea_vizita, 
prospect_denumire, prospect_ID
FROM sales_vizite_prospecti, sales_prospecti WHERE 
YEAR(data_vizita)='$year' AND  
prospect_ID=client_vizita ";
if ($aloc!='0'){
$query= $query . " AND alocat='$aloc'";
};
if ($cl!='0'){
$query= $query . " AND client_vizita='$cl'";
};
if ($fmonth!='0'){
$query= $query . " AND MONTH(data_vizita)='$fmonth'";
};
$result=ezpub_query($conn,$query);
$numar=ezpub_num_rows($result,$query);
$pages = new Pagination;  
$pages->items_total = $numar;  
$pages->mid_range = 5;  
$pages->paginate(); 
$query= $query . " ORDER BY data_vizita DESC $pages->limit";
$result=ezpub_query($conn,$query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>
<div class=\"paginate\"><a href=\"salesvisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;</div>";
}
else {
?>
        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strVisits;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"salesvisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
        </div>
        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strClient?></th>
                    <th><?php echo $strDate?></th>
                    <th><?php echo $strType?></th>
                    <th><?php echo $strScope?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDetails?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
    		echo"<tr>
			<td>$row[prospect_denumire]</td>
			<td>"; echo date('d.m.Y',strtotime($row["data_vizita"]));
			echo "</td>
			<td>";
			If($row["tip_vizita"]==0) {echo $strInitial;}
else {echo $strFollowup;}			
			echo "</td>
			<td>$row[scop_vizita]</td>
			 <td><a href=\"salesvisitreports.php?mode=edit&cID=$row[ID_vizita]\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
			 <td><a href=\"salesvisitreports.php?mode=view&cID=$row[ID_vizita]\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td colspan=\"6\">&nbsp;</td></tr></tfoot></table>";
?>
                <div class="paginate">
                    <?php
echo $pages->display_pages() . " <a href=\"salesvisitreports.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
                </div>
                <?php
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>