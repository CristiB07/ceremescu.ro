<?php
//update 08.01.2025
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';

$strPageTitle="Administrare prospecți";
include '../dashboard/header.php';
$uid=$_SESSION['uid'];
$code=$_SESSION['code'];
$role = isset($_SESSION['clearence']) ? $_SESSION['clearence'] : '';
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
 <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
        <?php
echo "<h1>$strPageTitle</h1>";
If (IsSet($_GET['mode']) AND $_GET['mode']=="delete"){

$nsql="DELETE FROM sales_prospecti WHERE prospect_id=" .$_GET['cID']. ";";
ezpub_query($conn,$nsql);
echo "<div class=\"callout success\">$strRecordDeleted</div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.location = \"salesprospects.php\"
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
    $fields = array(
        'prospect_denumire', 'prospect_adresa', 'prospect_telefon', 'prospect_rc', 'prospect_contact',
        'prospect_oras', 'prospect_judet', 'prospect_activitate', 'prospect_email', 'prospect_cui'
    );
    $fields[] = 'prospect_status';
    $fields[] = 'prospect_caracterizare';

    $values = array();
    foreach (array('prospect_denumire','prospect_adresa','prospect_telefon','prospect_rc','prospect_contact','prospect_oras','prospect_judet','prospect_activitate','prospect_email','prospect_cui') as $f) {
        $values[] = mysqli_real_escape_string($conn, $_POST[$f] ?? '');
    }
    $values[] = mysqli_real_escape_string($conn, $_POST['prospect_status'] ?? '');
    $values[] = mysqli_real_escape_string($conn, $_POST['prospect_caracterizare'] ?? '');

    $mSQL = "INSERT INTO sales_prospecti(" . implode(',', $fields) . ") VALUES ('" . implode("','", $values) . "')";
				
//It executes the SQL
if (!ezpub_query($conn,$mSQL))
  {
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordAdded</div></div></div>";
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
}
//-->
</script>
<body onLoad=\"setTimeout('delayer()', 1500)\">";
include '../bottom.php';
die;
}}
else
{// edit
$strWhereClause = " WHERE sales_prospecti.prospect_id=" . $_GET["cID"] . ";";
$query= "UPDATE sales_prospecti SET sales_prospecti.prospect_denumire='" .$_POST["prospect_denumire"] . "' ," ;
$query= $query . "sales_prospecti.prospect_adresa='" .$_POST["prospect_adresa"] . "' ," ;
$query= $query . "sales_prospecti.prospect_telefon='" .$_POST["prospect_telefon"] . "' ," ;
$query= $query . "sales_prospecti.prospect_rc='" .$_POST["prospect_rc"] . "' ," ;
$query= $query . "sales_prospecti.prospect_contact='" .$_POST["prospect_contact"] . "' ," ;
$query= $query . "sales_prospecti.prospect_oras='" .$_POST["prospect_oras"] . "' ," ;
$query= $query . "sales_prospecti.prospect_judet='" .$_POST["prospect_judet"] . "' ," ;
$query= $query . "sales_prospecti.prospect_status='" .$_POST["prospect_status"] . "' ," ;
$query= $query . "sales_prospecti.prospect_cui='" .$_POST["prospect_cui"] . "' ," ;
$query= $query . "sales_prospecti.prospect_email='" .$_POST["prospect_email"] . "' ," ;
$query= $query . "sales_prospecti.prospect_activitate='" .$_POST["prospect_activitate"] . "' ," ;
$query= $query . " sales_prospecti.prospect_caracterizare='" .$_POST["prospect_caracterizare"] . "' "; 
$query= $query . $strWhereClause;
if (!ezpub_query($conn,$query))
  {
  echo $query;
  die('Error: ' . ezpub_error($conn));
  }
else{
echo "<div class=\"callout success\">$strRecordModified</div></div></div>" ;
echo "<script type=\"text/javascript\">
<!--
function delayer(){
    window.history.go(-2);
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

If (IsSet($_GET['mode']) AND $_GET['mode']=="new"){
?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('btn1');
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var loader = document.getElementById('loaderIcon');
                if (loader) loader.style.display = '';
                var cuiEl = document.getElementById('Cui');
                var cui = cuiEl ? cuiEl.value : '';

                fetch('../common/cui.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'Cui=' + encodeURIComponent(cui)
                }).then(function(response) {
                    return response.json();
                }).then(function(data) {
                    try {
                        var den = document.getElementById('denumire');
                        if (den) den.value = (data.denumire || '').toUpperCase();
                        var cif = document.getElementById('cif');
                        if (cif) cif.value = data.cif || '';
                        var ad = document.getElementById('adresa');
                        if (ad) ad.value = data.adresa || '';
                        var jud = document.getElementById('judet');
                        if (jud) jud.value = (data.judet || '').toUpperCase();
                        var oras = document.getElementById('oras');
                        if (oras) oras.value = (data.oras || '').toUpperCase();
                        var numreg = document.getElementById('numar_reg_com');
                        if (numreg) numreg.value = data.numar_reg_com || '';
                        if (loader) loader.style.display = 'none';
                    } catch (err) {
                        var resp = document.getElementById('response');
                        if (resp) resp.innerText = err.message;
                    }
                }).catch(function() {
                    alert('Some error occurred!');
                    if (loader) loader.style.display = 'none';
                });
            });
        });
        </script>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="salesprospects.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui" placeholder="<?php echo $strEnterVATNumber?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button success"><i class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
                <div id="suggesstion-box"></div>
            </div>
        </div>
        <form method="post" action="salesprospects.php?mode=new">
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strTitle?>
                        <input name="prospect_denumire" type="text" id="denumire" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input name="prospect_cui" type="text" id="cif" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyRC?>
                        <input name="prospect_rc" id="numar_reg_com" type="text" value="" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-9 medium-9 small-9 cell">
                    <label><?php echo $strActivities?>
                        <input name="prospect_activitate" type="text" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strStatus?>
                        <select name="prospect_status">
                            <option value="">--</option>
                            <option value="Necontactat">Necontactat</option>
                            <option value="Contactat">Contactat</option>
                            <option value="Se mai gândește">Se mai gândește</option>
                            <option value="Interesat">Interesat</option>
                            <option value="Dorește contract">Dorește contract</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strAddress?>
                        <textarea name="prospect_adresa" id="adresa" style="width:100%;"></textarea>
                    </label>
                </div>
                <?php $colClass = ($role=='ADMIN') ? 'large-3 medium-3 small-3' : 'large-4 medium-4 small-4'; ?>
                <div class="<?php echo $colClass ?> cell">
                    <label><?php echo $strSector?>
                        <input name="prospect_oras" id="oras" type="text" />
                    </label>
                </div>
                <div class="<?php echo $colClass ?> cell">
                    <label><?php echo $strCounty?>
                        <input name="prospect_judet" id="judet" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strContact?>
                        <input name="prospect_contact" type="text" value="" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPhone?>
                        <input name="prospect_telefon" type="text" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="prospect_email" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strActivities?>
                        <input name="prospect_activitate" type="text" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strProfile?>
                        <textarea name="prospect_caracterizare" class="simple-html-editor" rows="5"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell text center"> <input type="submit" value="<?php echo $strAdd?>" name="Submit" class="button success" /></div>
            </div>
        </form>
        <?php
}
elseIf (IsSet($_GET['mode']) AND $_GET['mode']=="edit"){
$query="SELECT * FROM sales_prospecti WHERE prospect_id=$_GET[cID]";
$result=ezpub_query($conn,$query);
$row=ezpub_fetch_array($result);
?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('btn11');
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var loader = document.getElementById('loaderIcon');
                if (loader) loader.style.display = '';
                var cuiEl = document.getElementById('Cui');
                var cui = cuiEl ? cuiEl.value : '';

                fetch('../common/cui.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'Cui=' + encodeURIComponent(cui)
                }).then(function(response) {
                    return response.json();
                }).then(function(data) {
                    try {
                        var den = document.getElementById('denumire');
                        if (den) den.value = (data.denumire || '').toUpperCase();
                        var cif = document.getElementById('cif');
                        if (cif) cif.value = data.cif || '';
                        var ad = document.getElementById('adresa');
                        if (ad) ad.value = data.adresa || '';
                        var jud = document.getElementById('judet');
                        if (jud) jud.value = (data.judet || '').toUpperCase();
                        var oras = document.getElementById('oras');
                        if (oras) oras.value = (data.oras || '').toUpperCase();
                        var numreg = document.getElementById('numar_reg_com');
                        if (numreg) numreg.value = data.numar_reg_com || '';
                        if (loader) loader.style.display = 'none';
                    } catch (err) {
                        var resp = document.getElementById('response');
                        if (resp) resp.innerText = err.message;
                    }
                }).catch(function() {
                    alert(cui);
                    if (loader) loader.style.display = 'none';
                });
            });
        });
        </script>
        <div class="grid-x grid-margin-x">
            <div class="large-12 medium-12 small-12 cell">
                <p><a href="salesprospects.php" class="button"><?php echo $strBack?>&nbsp;<i class="fas fa-backward fa-xl"></i></a></p>
            </div>
        </div>
        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyVAT?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        value="<?php echo $row['prospect_cui'] ?>">
                    <div class="input-group-button">
                        <button id="btn11" class="button success"><i class="fas fa-sync-alt"></i>&nbsp;<?php echo $strUpdate ?></button>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" action="salesprospects.php?mode=edit&cID=<?php echo $row['prospect_id']?>">
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strTitle?>
                        <input name="prospect_denumire" type="text" id="denumire" value="<?php echo $row["prospect_denumire"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyVAT?>
                        <input name="prospect_cui" type="text" id="cif" value="<?php echo $row["prospect_cui"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCompanyRC?>
                        <input name="prospect_rc" id="numar_reg_com" type="text" value="<?php echo $row["prospect_rc"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strAddress?>
                        <textarea name="prospect_adresa" id="adresa" style="width:100%;"><?php echo $row["prospect_adresa"]?></textarea>
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strSector?>
                        <input name="prospect_oras" id="oras" type="text" value="<?php echo $row["prospect_oras"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strCounty?>
                        <input name="prospect_judet" id="judet" type="text" value="<?php echo $row["prospect_judet"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-6 medium-6 small-6 cell">
                    <label><?php echo $strContact?>
                        <input name="prospect_contact" type="text" value="<?php echo $row["prospect_contact"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strPhone?>
                        <input name="prospect_telefon" type="text" value="<?php echo $row["prospect_telefon"]?>" />
                    </label>
                </div>
                <div class="large-3 medium-3 small-3 cell">
                    <label><?php echo $strEmail?>
                        <input name="prospect_email" type="text" value="<?php echo $row["prospect_email"]?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strActivities?>
                        <input name="prospect_activitate" type="text" value="<?php echo htmlspecialchars($row["prospect_activitate"] ?? '', ENT_QUOTES);?>" />
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <?php $colClass = ($role=='ADMIN') ? 'large-3 medium-3 small-3' : 'large-4 medium-4 small-4'; ?>
                <div class="<?php echo $colClass ?> cell">
                    <label><?php echo $strStatus?>
                        <select name="prospect_status">
                            <option value="">--</option>
                            <option value="Necontactat" <?php if (($row["prospect_status"] ?? '')=='Necontactat') echo 'selected'; ?>>Necontactat</option>
                            <option value="Contactat" <?php if (($row["prospect_status"] ?? '')=='Contactat') echo 'selected'; ?>>Contactat</option>
                            <option value="Se mai gândește" <?php if (($row["prospect_status"] ?? '')=="Se mai gândește") echo 'selected'; ?>>Se mai gândește</option>
                            <option value="Interesat" <?php if (($row["prospect_status"] ?? '')=='Interesat') echo 'selected'; ?>>Interesat</option>
                            <option value="Dorește contract" <?php if (($row["prospect_status"] ?? '')=='Dorește contract') echo 'selected'; ?>>Dorește contract</option>
                        </select>
                    </label>
                </div>
                <!-- prospect_aloc removed from edit form; allocation managed via filters -->
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <label><?php echo $strProfile?>
                        <textarea name="prospect_caracterizare" class="simple-html-editor" rows="5"></textarea>
                    </label>
                </div>
            </div>
            <div class="grid-x grid-margin-x">
                <div class="large-12 medium-12 small-12 cell">
                    <input type="submit" value="<?php echo $strModify?>" name="Submit" class="button success" />
                </div>
            </div>
        </form>
        <?php
}
else {?>
        <script language="JavaScript" type="text/JavaScript">
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('Cui');
            if (!input) return;
            var suggestionBox = document.getElementById('suggesstion-box');
            input.addEventListener('keyup', function() {
                var val = input.value;
                // show loader background
                input.style.background = "#FFF url('../img/LoaderIcon.gif') no-repeat 165px";

                fetch('check_prospect.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'keyword=' + encodeURIComponent(val)
                }).then(function(response) {
                    return response.text();
                }).then(function(data) {
                    if (suggestionBox) {
                        suggestionBox.style.display = '';
                        suggestionBox.innerHTML = data;
                    }
                    input.style.background = '#FFF';
                }).catch(function(err) {
                    input.style.background = '#FFF';
                    console.error('AJAX error', err);
                });
            });
        });
        </script>

        <div class="grid-x grid-margin-x">
            <div class="large-6 medium-6 small-6 cell">
                <div id="response"></div>
                <div class="input-group">
                    <span class="input-group-label"><?php echo $strCompanyName?></span>
                    <input class="input-group-field" type="text" name="Cui" id="Cui"
                        placeholder="<?php echo $strEnterName?>">
                    <div class="input-group-button">
                        <button id="btn1" class="button success"><i
                                class="fas fa-search"></i>&nbsp;<?php echo $strCheck ?></button>
                    </div>
                </div>
                <div id="suggesstion-box"></div>
            </div>
        </div>
        <?php
echo " <div class=\"grid-x grid-margin-x\">
     <div class=\"large-12 medium-12 small-12 cell\">
<a href=\"salesprospects.php?mode=new\" class=\"button\">$strAdd&nbsp;<i class=\"fa-xl fa fa-plus\" title=\"$strAdd\"></i></a></div></div>";

// Read filter parameters (GET)
$aloc = isset($_GET['aloc']) ? intval($_GET['aloc']) : 0;
$start = isset($_GET['start']) ? $_GET['start'] : '';
$filter_oras = isset($_GET['filter_oras']) ? $_GET['filter_oras'] : '';
$filter_judet = isset($_GET['filter_judet']) ? $_GET['filter_judet'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Build WHERE clauses
$where = array();
if ($start !== '') {
    $where[] = "prospect_denumire LIKE '" . mysqli_real_escape_string($conn, $start) . "%'";
}
if ($aloc) {
    $where[] = "prospect_aloc = " . intval($aloc);
}
if ($filter_oras !== '') {
    $where[] = "prospect_oras = '" . mysqli_real_escape_string($conn, $filter_oras) . "'";
}
if ($filter_judet !== '') {
    $where[] = "prospect_judet = '" . mysqli_real_escape_string($conn, $filter_judet) . "'";
}
if ($filter_status !== '') {
    $where[] = "prospect_status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$query = "SELECT * FROM sales_prospecti";
if (count($where) > 0) {
    $query .= ' WHERE ' . implode(' AND ', $where);
}

$result = ezpub_query($conn, $query);
$numar = ezpub_num_rows($result);
$pages = new Pagination;
$pages->items_total = $numar;
$pages->mid_range = 5;
$pages->paginate();
$query = $query . " ORDER BY prospect_denumire ASC " . $pages->limit;
$result = ezpub_query($conn, $query);

echo ezpub_error($conn);
if ($numar==0)
{
echo "<div class=\"callout alert\">".$strNoRecordsFound."</div>";
}
else {
?>
        <?php
        // Prepare options for filter selects using DISTINCT values from sales_prospecti
        $all_oras = ezpub_query($conn, "SELECT DISTINCT prospect_oras FROM sales_prospecti WHERE prospect_oras<>'' ORDER BY prospect_oras ASC");
        $all_judet = ezpub_query($conn, "SELECT DISTINCT prospect_judet FROM sales_prospecti WHERE prospect_judet<>'' ORDER BY prospect_judet ASC");
        if ($role=='ADMIN') {
            $all_users = ezpub_query($conn, "SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori ORDER BY utilizator_Prenume ASC");
        }
        ?>

        <div class="grid-x grid-margin-x">
            <div class="large-12 cell">
                <form method="get" action="salesprospects.php">
                    <div class="grid-x grid-margin-x">
                        <div class="large-3 medium-3 small-12 cell">
                            <label>Oraș
                                <select name="filter_oras">
                                    <option value="">--</option>
                                    <?php while ($o = ezpub_fetch_array($all_oras)) { $ov = $o['prospect_oras']; $sel = ($ov == ($filter_oras ?? '')) ? 'selected' : ''; echo '<option value="'.htmlspecialchars($ov,ENT_QUOTES).'" '. $sel .'>'.htmlspecialchars($ov,ENT_QUOTES)."</option>"; } ?>
                                </select>
                            </label>
                        </div>
                        <div class="large-3 medium-3 small-12 cell">
                            <label>Județ
                                <select name="filter_judet">
                                    <option value="">--</option>
                                    <?php while ($j = ezpub_fetch_array($all_judet)) { $jv = $j['prospect_judet']; $sel = ($jv == ($filter_judet ?? '')) ? 'selected' : ''; echo '<option value="'.htmlspecialchars($jv,ENT_QUOTES).'" '. $sel .'>'.htmlspecialchars($jv,ENT_QUOTES)."</option>"; } ?>
                                </select>
                            </label>
                        </div>
                        <div class="large-3 medium-3 small-12 cell">
                            <label>Status
                                <select name="filter_status">
                                    <option value="">--</option>
                                    <option value="Necontactat" <?php if (($filter_status ?? '')=='Necontactat') echo 'selected'; ?>>Necontactat</option>
                                    <option value="Contactat" <?php if (($filter_status ?? '')=='Contactat') echo 'selected'; ?>>Contactat</option>
                                    <option value="Se mai gândește" <?php if (($filter_status ?? '')=='Se mai gândește') echo 'selected'; ?>>Se mai gândește</option>
                                    <option value="Interesat" <?php if (($filter_status ?? '')=='Interesat') echo 'selected'; ?>>Interesat</option>
                                    <option value="Dorește contract" <?php if (($filter_status ?? '')=="Dorește contract") echo 'selected'; ?>>Dorește contract</option>
                                </select>
                            </label>
                        </div>
                        <?php if ($role=='ADMIN') { ?>
                        <div class="large-2 medium-2 small-12 cell">
                            <label>Alocat
                                <select name="aloc">
                                    <option value="">--</option>
                                    <?php while ($u = ezpub_fetch_array($all_users)) { $uidn = $u['utilizator_ID']; $uname = trim($u['utilizator_Prenume'].' '.$u['utilizator_Nume']); $sel = (intval($aloc) == intval($uidn)) ? 'selected' : ''; echo '<option value="'.intval($uidn).'" '. $sel .'>'.htmlspecialchars($uname,ENT_QUOTES)."</option>"; } ?>
                                </select>
                            </label>
                        </div>
                        <?php } ?>
                        <div class="large-1 medium-1 small-12 cell">
                            <label>&nbsp;<br/>
                                <input type="submit" class="button" value="Filtrează" />
                            </label>
                        </div>
                        <div class="large-1 medium-1 small-12 cell">
                            <label>&nbsp;<br/>
                                <a href="salesprospects.php" class="button secondary">Șterge</a>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="paginate">
            <?php
echo $strTotal . " " .$numar." ".$strClients ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"salesprospects.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
echo " <br /><br />";
$sql="SELECT DISTINCT LEFT(sales_prospecti.prospect_denumire, 1) as letter 
FROM sales_prospecti 
Group By letter ORDER BY letter ASC;";
$result2=ezpub_query($conn,$sql);
While ($row1=ezpub_fetch_array($result2)){
	$char=$row1["letter"];
    echo "<a href=\"salesprospects.php?start=$char\">$char</a>&nbsp;";
}
?>
        </div>

        <table width="100%">
            <thead>
                <tr>
                    <th><?php echo $strTitle?></th>
                    <th><?php echo $strVAT?></th>
                    <th><?php echo $strSector?></th>
                    <th><?php echo $strStatus?></th>
                    <?php if ($role=='ADMIN') { echo '<th>Alocat</th>'; } ?>
                    <th><?php echo $strNeighbourhood?></th>
                    <th><?php echo $strEdit?></th>
                    <th><?php echo $strDelete?></th>
                    <th><?php echo $strDetails?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
While ($row=ezpub_fetch_array($result)){
        $alocat_name = '';
        if ($role=='ADMIN' && !empty($row['prospect_aloc'])) {
            $uqr = ezpub_query($conn, "SELECT utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_ID='".intval($row['prospect_aloc'])."'");
            $uu = ezpub_fetch_array($uqr);
            if ($uu) $alocat_name = trim($uu['utilizator_Prenume'].' '.$uu['utilizator_Nume']);
        }
        echo "<tr>
            <td>".htmlspecialchars($row['prospect_denumire'] ?? '',ENT_QUOTES)."</td>
            <td>".htmlspecialchars($row['prospect_cui'] ?? '',ENT_QUOTES)."</td>
            <td>".htmlspecialchars($row['prospect_oras'] ?? '',ENT_QUOTES)."</td>
            <td>".htmlspecialchars($row['prospect_status'] ?? '',ENT_QUOTES)."</td>";
        if ($role=='ADMIN') { echo "<td>".htmlspecialchars($alocat_name ?? '',ENT_QUOTES)."</td>"; }
        echo "<td>".htmlspecialchars($row['prospect_judet'] ?? '',ENT_QUOTES)."</td>
             <td><a href=\"salesprospects.php?mode=edit&cID=".$row['prospect_id']."\" ><i class=\"far fa-edit fa-xl\" title=\"$strEdit\"></i></a></td>
             <td><a href=\"salesprospects.php?mode=delete&cID=".$row['prospect_id']."\"  OnClick=\"return confirm('$strConfirmDelete');\"><i class=\"fa fa-eraser fa-xl\" title=\"$strDelete\"></i></a></td>
             <td><a href=\"salesprospectprofile.php?cID=".$row['prospect_id']."\"><i class=\"fa fa-search-plus fa-xl\" title=\"$strEdit\"></i></a></td>
        </tr>";
}
echo "</tbody><tfoot><tr><td></td><td  colspan=\"6\"><em></em></td><td>&nbsp;</td></tr></tfoot></table>";
}
}
}
?>
    </div>
</div>
<?php
include '../bottom.php';
?>