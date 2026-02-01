<?php
// Clean implementation for client visits management
    // clients/sitevisitreports.php
    include '../settings.php';
    include '../classes/common.php';
    if(!isset($_SESSION)) { session_start(); }
    if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes") { header("location:$strSiteURL/login/index.php?message=MLF"); die; }
    include '../classes/paginator.class.php';

    $strPageTitle = "Administrare vizite clienți";
    include '../dashboard/header.php';
    $uid = $_SESSION['uid'];
    $code = $_SESSION['code'];

    // Ensure required table exists
    $tbl_check = mysqli_query($conn, "SHOW TABLES LIKE 'clienti_vizite'");
    if (!$tbl_check || mysqli_num_rows($tbl_check) == 0) {
        echo '<div class="callout alert">Tabela <strong>clienti_vizite</strong> nu există în baza de date. Vă rugăm restaurați tabela sau creați-o înainte de a folosi această pagină.</div>';
        include '../bottom.php';
        die;
    }

    // Validate inputs
    if (isset($_GET['cID']) && !is_numeric($_GET['cID'])) { header("location:$strSiteURL/clients/sitevisitreports.php?message=ER"); die; }
    if (isset($_GET['mode']) && !in_array($_GET['mode'], ['new','edit','view','delete'])) { header("location:$strSiteURL/clients/sitevisitreports.php?message=ER"); die; }

    // Delete
    if (isset($_GET['mode']) && $_GET['mode'] === 'delete'){
        $cID = (int)$_GET['cID'];
        $stmt = mysqli_prepare($conn, "DELETE FROM clienti_vizite WHERE ID_vizita=?");
        mysqli_stmt_bind_param($stmt, "i", $cID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<div class=\"success callout\">$strRecordDeleted</div>";
        echo "<script>setTimeout(function(){window.location='sitevisitreports.php'},1500);</script>";
        include '../bottom.php';
        die;
    }

    // POST handling for new / edit
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        check_inject();
        $mode = $_GET['mode'] ?? '';
        $raw_dt = trim($_POST['data_vizita'] ?? '');
        if (empty($raw_dt)) { die('Error: data_vizita este obligatorie'); }
        $ts = strtotime($raw_dt);
        if ($ts === false) { die('Error: data_vizita invalidă'); }
        $datavizita = date('Y-m-d H:i:s', $ts);

        $client_vizita = (int)($_POST['client_vizita'] ?? 0);
        $tip_vizita = trim($_POST['tip_vizita'] ?? '');
        $scop_vizita = trim($_POST['scop_vizita'] ?? '');
        $urmatoarea_vizita = trim($_POST['urmatoarea_vizita'] ?? '');
        $observatii_vizita = trim($_POST['observatii_vizita'] ?? '');

        if ($mode === 'new'){
            $stmt = mysqli_prepare($conn, "INSERT INTO clienti_vizite(client_vizita, tip_vizita, data_vizita, scop_vizita, alocat, urmatoarea_vizita, observatii_vizita) VALUES(?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssss", $client_vizita, $tip_vizita, $datavizita, $scop_vizita, $code, $urmatoarea_vizita, $observatii_vizita);
            if (!mysqli_stmt_execute($stmt)) { die('Error: '.mysqli_stmt_error($stmt)); }
            mysqli_stmt_close($stmt);
            echo "<div class=\"success callout\">$strRecordAdded</div>";
            echo "<script>setTimeout(function(){window.location='sitevisitreports.php'},1500);</script>";
            include '../bottom.php'; die;
        }

        if ($mode === 'edit'){
            $cID = (int)($_GET['cID'] ?? 0);
            $stmt = mysqli_prepare($conn, "UPDATE clienti_vizite SET client_vizita=?, scop_vizita=?, tip_vizita=?, data_vizita=?, urmatoarea_vizita=?, observatii_vizita=? WHERE ID_vizita=?");
            mysqli_stmt_bind_param($stmt, "isssssi", $client_vizita, $scop_vizita, $tip_vizita, $datavizita, $urmatoarea_vizita, $observatii_vizita, $cID);
            if (!mysqli_stmt_execute($stmt)) { die('Error: '.mysqli_stmt_error($stmt)); }
            mysqli_stmt_close($stmt);
            echo "<div class=\"success callout\">$strRecordModified</div>";
            echo "<script>setTimeout(function(){window.location='sitevisitreports.php'},1500);</script>";
            include '../bottom.php'; die;
        }
    }

    // Page rendering starts here
    ?>

    <script src="<?php echo $strSiteURL ?>/js/foundation/jquery.js"></script>
    <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
    <script src="../js/simple-editor/simple-editor.js"></script>

    <?php
    // New form
    if (isset($_GET['mode']) && $_GET['mode'] === 'new'){
        ?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="sitevisitreports.php" class="button"><?php echo $strBack?></a></p>
            </div>
        </div>
        <form method="post" action="sitevisitreports.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-3 medium-3 small-12 cell">
                    <label>Preia din programare</label>
                    <select id="planned_select_new" class="">
                        <option value="">-- Alege din planificări --</option>
                    </select>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strClient?></label>
                    <select name="client_vizita" class="required">
                        <option value=""><?php echo $strClient?></option>
                        <?php
                        $sql = "SELECT ID_Client, Client_Denumire FROM clienti_date ORDER BY Client_Denumire ASC";
                        $res = ezpub_query($conn, $sql);
                        while ($r = ezpub_fetch_array($res)){
                            echo '<option value="'.htmlspecialchars($r['ID_Client']).'">'.htmlspecialchars($r['Client_Denumire'])."</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strType?></label>
                    <input name="tip_vizita" type="text" class="required" />
                </div>
                <div class="large-3 medium-3 small-12 cell">
                    <label><?php echo $strDate?></label>
                    <input type="datetime-local" name="data_vizita" id="data_vizita_new" class="required" />
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strScope?></label>
                    <input name="scop_vizita" type="text" class="required" />
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="observatii_vizita" id="observatii_vizita_new" class="simple-editor" rows="6"></textarea>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strNextVisit?></label>
                    <textarea name="urmatoarea_vizita" class="simple-editor" rows="3"></textarea>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 text-center cell">
                    <input type="submit" value="<?php echo $strAdd?>" class="button success" />
                </div>
            </div>
        </form>
        <?php
    }

    // Edit form
    elseif (isset($_GET['mode']) && $_GET['mode'] === 'edit'){
        $cID = (int)$_GET['cID'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM clienti_vizite WHERE ID_vizita=?");
        mysqli_stmt_bind_param($stmt, "i", $cID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        if (!$row) { echo '<div class="callout alert">'.$strNoRecordsFound.'</div>'; include '../bottom.php'; die; }
        ?>
        <div class="grid-x grid-margin-x">
            <div class="large-12 cell"><p><a href="sitevisitreports.php" class="button"><?php echo $strBack?></a></p></div>
        </div>
        <form method="post" action="sitevisitreports.php?mode=edit&cID=<?php echo $cID?>">
            <div class="grid-x grid-margin-x">
                <div class="large-3 cell">
                    <label>Preia din programare</label>
                    <select id="planned_select_edit">
                        <option value="">-- Alege din planificări --</option>
                    </select>
                </div>
                <div class="large-3 cell">
                    <label><?php echo $strClient?></label>
                    <select name="client_vizita" class="required">
                        <?php
                        $sql = "SELECT ID_Client, Client_Denumire FROM clienti_date ORDER BY Client_Denumire ASC";
                        $res2 = ezpub_query($conn, $sql);
                        while ($r2 = ezpub_fetch_array($res2)){
                            $sel = ($r2['ID_Client']==$row['client_vizita']) ? ' selected' : '';
                            echo '<option value="'.htmlspecialchars($r2['ID_Client']).'"'.$sel.'>'.htmlspecialchars($r2['Client_Denumire'])."</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <div class="large-3 cell">
                    <label><?php echo $strType?></label>
                    <input name="tip_vizita" type="text" value="<?php echo htmlspecialchars($row['tip_vizita'])?>" />
                </div>
                <div class="large-3 cell">
                    <label><?php echo $strDate?></label>
                    <input type="datetime-local" name="data_vizita" id="data_vizita_edit" value="<?php echo date('Y-m-d\TH:i', strtotime($row['data_vizita']))?>" />
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strScope?></label>
                    <input name="scop_vizita" type="text" value="<?php echo htmlspecialchars($row['scop_vizita'])?>" />
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strDetails?></label>
                    <textarea name="observatii_vizita" class="simple-editor" rows="6"><?php echo htmlspecialchars($row['observatii_vizita'])?></textarea>
                </div>
            </div>

            <div class="grid-x grid-margin-x">
                <div class="large-12 cell">
                    <label><?php echo $strNextVisit?></label>
                    <textarea name="urmatoarea_vizita" class="simple-editor" rows="3"><?php echo htmlspecialchars($row['urmatoarea_vizita'])?></textarea>
                </div>
            </div>

            <div class="grid-x grid-margin-x"><div class="large-12 text-center cell"><input type="submit" value="<?php echo $strModify?>" class="button success" /></div></div>
        </form>
        <?php
    }

    // View single
    elseif (isset($_GET['mode']) && $_GET['mode'] === 'view'){
        $cID = (int)$_GET['cID'];
        $query = "SELECT cv.*, cd.Client_Denumire FROM clienti_vizite cv LEFT JOIN clienti_date cd ON cd.ID_Client=cv.client_vizita WHERE cv.ID_vizita=$cID";
        $res = ezpub_query($conn, $query);
        $row = ezpub_fetch_array($res);
        if (!$row) { echo '<div class="callout alert">'.$strNoRecordsFound.'</div>'; include '../bottom.php'; die; }
        echo "<div class=\"grid-x grid-margin-x\"><div class=\"large-12 cell\"><p><a href=\"sitevisitreports.php\" class=\"button\">$strBack</a></p></div></div>";
        echo "<table class=\"stack\"><tr><td>$strName</td><td>".htmlspecialchars($row['Client_Denumire'])."</td></tr>";
        echo "<tr><td>$strDate</td><td>".htmlspecialchars($row['data_vizita'])."</td></tr>";
        echo "<tr><td>$strScope</td><td>".htmlspecialchars($row['scop_vizita'])."</td></tr>";
        echo "<tr><td>$strDetails</td><td>".nl2br(htmlspecialchars($row['observatii_vizita']))."</td></tr>";
        echo "<tr><td>$strNextVisit</td><td>".nl2br(htmlspecialchars($row['urmatoarea_vizita']))."</td></tr></table>";
    }

    // List
    else {
        echo "<div class=\"grid-x grid-margin-x\"><div class=\"large-12 cell\"><a href=\"sitevisitreports.php?mode=new\" class=\"button\"><i class=\"large fa fa-plus\"></i>&nbsp;$strAdd</a></div></div>\n";
        $query = "SELECT cv.ID_vizita, cv.client_vizita, cv.alocat, cv.data_vizita, cv.tip_vizita, cv.scop_vizita, cv.observatii_vizita, cv.urmatoarea_vizita, cd.Client_Denumire, du.utilizator_Nume, du.utilizator_Prenume FROM clienti_vizite cv LEFT JOIN clienti_date cd ON cd.ID_Client=cv.client_vizita LEFT JOIN date_utilizatori du ON du.utilizator_code=cv.alocat";
        if ($_SESSION['clearence']=='USER') { $query .= " WHERE cv.alocat='".mysqli_real_escape_string($conn,$code)."'"; }
        $query .= " ORDER BY cv.data_vizita DESC";

        $resultAll = ezpub_query($conn, $query);
        $numar = ezpub_num_rows($resultAll);

        $pages = new Pagination;
        $pages->items_total = $numar;
        $pages->mid_range = 5;
        $pages->paginate();

        $result = ezpub_query($conn, $query . ' ' . $pages->limit);

        if ($numar==0){
            echo "<div class=\"callout alert\">$strNoRecordsFound</div>";
        } else {
            echo "<div class=\"paginate\">". $strTotal . ' ' . $numar . ' ' . $strVisits . "<br/><br/>". $pages->display_pages() . "<br/><br/></div>";
            echo "<table width=\"100%\"><thead><tr><th>$strClient</th><th>$strName</th><th>$strDate</th><th>$strScope</th><th>$strEdit</th><th>$strDetails</th><th>$strDelete</th></tr></thead><tbody>";
            while ($row = ezpub_fetch_array($result)){
                echo '<tr><td>'.htmlspecialchars($row['Client_Denumire']).'</td>';
                echo '<td>'.htmlspecialchars($row['utilizator_Prenume'].' '.$row['utilizator_Nume']).'</td>';
                echo '<td>'.htmlspecialchars(date('d.m.Y H:i', strtotime($row['data_vizita']))).'</td>';
                echo '<td>'.htmlspecialchars($row['scop_vizita']).'</td>';
                echo '<td><a href="sitevisitreports.php?mode=edit&cID='.$row['ID_vizita'].'"><i class="fas fa-pencil-alt fa-xl" title="'.$strEdit.'"></i></a></td>';
                echo '<td><a href="sitevisitreports.php?mode=view&cID='.$row['ID_vizita'].'"><i class="fa fa-search-plus fa-xl" title="'.$strDetails.'"></i></a></td>';
                echo '<td><a href="sitevisitreports.php?mode=delete&cID='.$row['ID_vizita'].'" OnClick="return confirm(\''.addslashes($strConfirmDelete).'\');"><i class="fa fa-eraser fa-xl" title="'.$strDelete.'"></i></a></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }

    include '../bottom.php';
    ?>

    <script>
    // Fetch planned visits for a given date (expects YYYY-MM-DD)
    function loadPlannedForDate(date, targetSelectId){
        if (!date) return;
        fetch('../clients/clientsgetplansfordate.php?date=' + encodeURIComponent(date))
        .then(r => r.json())
        .then(data => {
            var sel = document.getElementById(targetSelectId);
            if (!sel) return;
            sel.innerHTML = '<option value="">-- Alege din planificări --</option>';
            data.forEach(function(item){
                var opt = document.createElement('option');
                opt.value = item.id;
                opt.text = (item.obiectiv || '') + ' (' + (item.data || '') + ')';
                opt.dataset.client = item.client_id || '';
                opt.dataset.tip = item.tipvizita || '';
                opt.dataset.detalii = item.detalii || '';
                opt.dataset.data = item.data || '';
                sel.appendChild(opt);
            });
        }).catch(console.error);
    }

    function applyPlannedToForm(selectId, prefix){
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.addEventListener('change', function(){
            var opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) return;
            var client = opt.dataset.client || '';
            var tip = opt.dataset.tip || '';
            var detalii = opt.dataset.detalii || '';
            var data = opt.dataset.data || '';
            if (client && document.querySelector('select[name="client_vizita"]')) document.querySelector('select[name="client_vizita"]').value = client;
            if (tip && document.querySelector('input[name="tip_vizita"]')) document.querySelector('input[name="tip_vizita"]').value = tip;
            if (detalii && document.querySelector('input[name="scop_vizita"]')) document.querySelector('input[name="scop_vizita"]').value = detalii;
            if (data){
                var d = new Date(data);
                if (!isNaN(d.getTime())){
                    var yyyy = d.getFullYear();
                    var mm = ('0'+(d.getMonth()+1)).slice(-2);
                    var dd = ('0'+d.getDate()).slice(-2);
                    var hh = ('0'+d.getHours()).slice(-2);
                    var min = ('0'+d.getMinutes()).slice(-2);
                    var val = yyyy+'-'+mm+'-'+dd+'T'+hh+':'+min;
                    var input = document.getElementById(prefix=='new' ? 'data_vizita_new' : 'data_vizita_edit');
                    if (input) input.value = val;
                }
            }
        });
    }

    // Hookup for new/edit pages
    document.addEventListener('DOMContentLoaded', function(){
        var newInput = document.getElementById('data_vizita_new');
        if (newInput){
            newInput.addEventListener('change', function(){
                var datePart = this.value.split('T')[0];
                loadPlannedForDate(datePart, 'planned_select_new');
            });
            applyPlannedToForm('planned_select_new', 'new');
        }
        var editInput = document.getElementById('data_vizita_edit');
        if (editInput){
            editInput.addEventListener('change', function(){
                var datePart = this.value.split('T')[0];
                loadPlannedForDate(datePart, 'planned_select_edit');
            });
            applyPlannedToForm('planned_select_edit', 'edit');
        }
    });
    </script>