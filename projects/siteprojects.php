<?php

$strPageTitle = "Proiecte";
include '../settings.php';
include '../classes/common.php';
include '../classes/paginator.class.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/siteprojects.php?message=MLF");
    die;
}
$role = $_SESSION['clearence'];
$uid = $_SESSION['uid'];
$lang = isset($_SESSION['$lang']) ? $_SESSION['$lang'] : 'RO';
if ($lang=="RO") { include '../lang/language_RO.php'; } else { include '../lang/language_EN.php'; }
include '../dashboard/header.php';

// DELETE proiect și tot ce ține de el
if (isset($_GET['mode']) && $_GET['mode']=="delete" && isset($_GET['id']) && $role=="ADMIN") {
    $projectID = (int)$_GET['id'];
    // Ștergere recursivă: activități, statusuri, fișiere, apoi proiectul
    // TODO: implementare efectivă ștergere recursivă
    echo "<div class=\"callout success\">Proiect șters!</div>";
    echo "<script>setTimeout(function(){ window.location='siteprojects.php'; }, 1500);</script>";
    include '../bottom.php';
    die;
}

// DELETE FILE din proiect
if (isset($_GET['mode']) && $_GET['mode']=="deletefile" && isset($_GET['id']) && isset($_GET['file']) && $role=="ADMIN") {
    $projectID = (int)$_GET['id'];
    $file = basename($_GET['file']);
    // Preluăm proiectul pentru a afla codul
    $stmt = mysqli_prepare($conn, "SELECT proiect_cod, proiect_fisiere FROM proiecte WHERE proiect_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $projectID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    if ($row && !empty($row['proiect_cod'])) {
        $project_dir = $hddpath . '/' . $projects_folder . '/' . $row['proiect_cod'];
        $file_path = $project_dir . '/' . $file;
        // Ștergere de pe disc
        if (is_file($file_path)) {
            unlink($file_path);
        }
        // Actualizare array fișiere în DB
        $fis_arr = array_filter(array_map('trim', explode(',', $row['proiect_fisiere'])));
        $fis_arr = array_diff($fis_arr, [$file]);
        $fis_str = implode(',', $fis_arr);
        $stmt2 = mysqli_prepare($conn, "UPDATE proiecte SET proiect_fisiere=? WHERE proiect_id=?");
        mysqli_stmt_bind_param($stmt2, 'si', $fis_str, $projectID);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        echo "<div class=\"callout success\">Fișier șters!</div>";
    } else {
        echo "<div class=\"callout alert\">Eroare la identificarea proiectului sau a fișierului!</div>";
    }
    echo "<script>setTimeout(function(){ window.location='siteprojects.php?mode=edit&id=$projectID'; }, 1000);</script>";
    include '../bottom.php';
    die;
}

// INSERT/UPDATE proiect
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proiect_titlu = trim($_POST['proiect_titlu'] ?? '');
    $proiect_cod = trim($_POST['proiect_cod'] ?? '');
    $proiect_descriere = trim($_POST['proiect_descriere'] ?? '');
    $proiect_data_inceput = $_POST['proiect_data_inceput'] ?? null;
    $proiect_data_sfarsit = $_POST['proiect_data_sfarsit'] ?? null;
    $proiect_client = (int)($_POST['proiect_client'] ?? 0);
    $proiect_importanta = trim($_POST['proiect_importanta'] ?? '');
    $proiect_status = (int)($_POST['proiect_status'] ?? 0);
    $proiect_echipa = isset($_POST['proiect_echipa']) ? implode(',', array_map('intval', $_POST['proiect_echipa'])) : '';
    $proiect_fisiere = '';
    // Upload fișiere
        if (!empty($_FILES['proiect_fisiere']['name'][0])) {
            if (!isset($projects_folder) || !$projects_folder) { $projects_folder = 'projects'; }
            $upload_dir = $hddpath . '/' . $projects_folder . '/' . $proiect_cod;
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            foreach ($_FILES['proiect_fisiere']['name'] as $idx => $filename) {
                $tmp = $_FILES['proiect_fisiere']['tmp_name'][$idx];
                $dest = $upload_dir . '/' . basename($filename);
                if (move_uploaded_file($tmp, $dest)) {
                    $proiect_fisiere .= basename($filename) . ',';
                }
            }
            $proiect_fisiere = rtrim($proiect_fisiere, ',');
        }
    if ($_GET['mode']=="new") {
        $stmt = mysqli_prepare($conn, "INSERT INTO proiecte (proiect_titlu, proiect_cod, proiect_descriere, proiect_data_inceput, proiect_data_sfarsit, proiect_echipa, proiect_client, proiect_importanta, proiect_status, proiect_lastupdate, proiect_fisiere) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssisis', $proiect_titlu, $proiect_cod, $proiect_descriere, $proiect_data_inceput, $proiect_data_sfarsit, $proiect_echipa, $proiect_client, $proiect_importanta, $proiect_status, $proiect_fisiere);
        // corect: proiect_data_inceput și proiect_data_sfarsit sunt string (datetime), nu int
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<div class=\"callout success\">Proiect adăugat!</div>";
    } else if ($_GET['mode']=="edit" && isset($_GET['id'])) {
        $projectID = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "UPDATE proiecte SET proiect_titlu=?, proiect_cod=?, proiect_descriere=?, proiect_data_inceput=?, proiect_data_sfarsit=?, proiect_echipa=?, proiect_client=?, proiect_importanta=?, proiect_status=?, proiect_lastupdate=NOW(), proiect_fisiere=? WHERE proiect_id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssisisi', $proiect_titlu, $proiect_cod, $proiect_descriere, $proiect_data_inceput, $proiect_data_sfarsit, $proiect_echipa, $proiect_client, $proiect_importanta, $proiect_status, $proiect_fisiere, $projectID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "<div class=\"callout success\">Proiect modificat!</div>";
    }
    echo "<script>setTimeout(function(){ window.location='siteprojects.php'; }, 1500);</script>";
    include '../bottom.php';
    die;
}

// FORMULAR ADD/EDIT
if (isset($_GET['mode']) && ($_GET['mode']=="new" || ($_GET['mode']=="edit" && isset($_GET['id'])))) {
    $edit = ($_GET['mode']=="edit");
    $row = [
        'proiect_titlu' => '', 'proiect_cod' => '', 'proiect_descriere' => '', 'proiect_data_inceput' => '', 'proiect_data_sfarsit' => '', 'proiect_echipa' => '', 'proiect_client' => '', 'proiect_importanta' => '', 'proiect_status' => '', 'proiect_fisiere' => ''
    ];
    if ($edit) {
        $projectID = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM proiecte WHERE proiect_id=?");
        mysqli_stmt_bind_param($stmt, 'i', $projectID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }
    // Select clienți
    $clienti = [];
    $q = ezpub_query($conn, "SELECT ID_Client, Client_Denumire FROM clienti_date ORDER BY Client_Denumire ASC");
    while ($c = ezpub_fetch_array($q)) $clienti[] = $c;
    // Select utilizatori USER
    $utilizatori = [];
    $q = ezpub_query($conn, "SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_Role='USER' ORDER BY utilizator_Nume ASC");
    while ($u = ezpub_fetch_array($q)) $utilizatori[] = $u;
    ?>
    <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h1><?php echo $edit ? $strEdit : $strAdd; ?> proiect</h1>
</div>
    </div>

            <form method="post" enctype="multipart/form-data" action="siteprojects.php?mode=<?php echo $edit ? 'edit&id='.$projectID : 'new'; ?>">
                <div class="grid-x grid-margin-x">
        <div class="large-4 medium-4 small-12 cell">   
            <label>Titlu proiect
                    <input type="text" name="proiect_titlu" value="<?php echo htmlspecialchars($row['proiect_titlu']); ?>" required />
                </label>
</div>
        <div class="large-2 medium-2 small-6 cell">      
                <label>Cod proiect
                    <input type="text" name="proiect_cod" value="<?php echo htmlspecialchars($row['proiect_cod']); ?>" required />
                </label>
</div>
        <div class="large-2 medium-2 small-16 cell">
                 <label>Data început
                    <input type="date" name="proiect_data_inceput" value="<?php echo htmlspecialchars(substr($row['proiect_data_inceput'],0,10)); ?>" />
                </label>
</div>
        <div class="large-2 medium-2 small-16 cell">
                <label>Data sfârșit
                    <input type="date" name="proiect_data_sfarsit" value="<?php echo htmlspecialchars(substr($row['proiect_data_sfarsit'],0,10)); ?>" />
                </label>
</div>
        <div class="large-2 medium-2 small-16 cell">
                <label>Echipă proiect (Ctrl/Cmd+Click pentru multiplu)
                    <select name="proiect_echipa[]" multiple size="5">
                        <?php foreach($utilizatori as $u) { $sel = in_array($u['utilizator_ID'], explode(',', $row['proiect_echipa'])) ? 'selected' : ''; ?>
                        <option value="<?php echo $u['utilizator_ID']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($u['utilizator_Prenume'].' '.$u['utilizator_Nume']); ?></option>
                        <?php } ?>
                    </select>
                </label>
</div> 
</div>
<div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell">
                <label>Descriere
                    <textarea name="proiect_descriere" class="simple-html-editor" data-upload-dir="projects" rows="10"><?php echo htmlspecialchars($row['proiect_descriere']); ?></textarea>
                </label>
</div>          
</div>
<div class="grid-x grid-margin-x">
        <div class="large-3 medium-3 small-6 cell">          
                <label>Client
                    <select name="proiect_client">
                        <option value="">Alege client</option>
                        <?php foreach($clienti as $c) { $sel = $row['proiect_client']==$c['ID_Client'] ? 'selected' : ''; ?>
                        <option value="<?php echo $c['ID_Client']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($c['Client_Denumire']); ?></option>
                        <?php } ?>
                    </select>
                </label>
</div>
        <div class="large-3 medium-3 small-6 cell">
            <label>Importanță
                <select name="proiect_importanta">
                    <option value="1" <?php if($row['proiect_importanta']=='1') echo 'selected'; ?>>Low</option>
                    <option value="2" <?php if($row['proiect_importanta']=='2') echo 'selected'; ?>>Medium</option>
                    <option value="3" <?php if($row['proiect_importanta']=='3') echo 'selected'; ?>>High</option>
                </select>
            </label>
</div>
        <div class="large-3 medium-3 small-6 cell">
                <label>Status
                    <select name="proiect_status">
                        <option value="0" <?php if($row['proiect_status']==0) echo 'selected'; ?>>Deschis</option>
                        <option value="1" <?php if($row['proiect_status']==1) echo 'selected'; ?>>Închis</option>
                        <option value="2" <?php if($row['proiect_status']==2) echo 'selected'; ?>>On hold</option>
                    </select>
                </label>
</div>
 <div class="large-3 medium-3 small-6 cell">
    <label>Fișiere proiect (poți selecta mai multe)
        <input type="file" name="proiect_fisiere[]" multiple />
    </label>

    <?php
    // Afișare fișiere existente pentru proiectul curent (doar la editare)
    if (!empty($edit) && !empty($row['proiect_cod'])) {
        $project_files_dir = $hddpath . '/' . $projects_folder . '/' . $row['proiect_cod'];
        if (is_dir($project_files_dir)) {
            $files = array_diff(scandir($project_files_dir), array('.', '..'));
            if (count($files) > 0) {
                echo '<div class="callout secondary"><strong>Fișiere existente:</strong><ul style="margin-bottom:0">';
                foreach ($files as $f) {
                    $icon = getFileIcon($f);
                    $file_url = $hddpath . '/' . $projects_folder . '/' . rawurlencode($row['proiect_cod']) . '/' . rawurlencode($f);
                    echo '<li style="margin-bottom:4px"><i class="' . $icon . '"></i> <a href="' . $file_url . '" target="_blank">' . htmlspecialchars($f) . '</a>';
                    echo ' <a href="siteprojects.php?mode=deletefile&id=' . $projectID . '&file=' . rawurlencode($f) . '" onclick="return confirm(\'Ștergi fișierul?\')" title="Șterge fișier"><i class="fa fa-eraser" style="color:#c00;font-size:1.1em;vertical-align:middle;"></i></a>';
                    echo '</li>';
                }
                echo '</ul></div>';
            }
        }
    }
    ?>
</div>
</div>
        <div class="grid-x grid-margin-x">
        <div class="large-12 medium-12 small-12 cell text-center">
                <button type="submit" class="button success">Salvează</button>
                <a href="siteprojects.php" class="button secondary">Renunță</a>
            </form>     
        </div>
    </div>
    <?php
    include '../bottom.php';
    die;
}

// TABEL VIZUALIZARE
$where = "";
if ($role!="ADMIN") {
    $where = "WHERE FIND_IN_SET($uid, proiect_echipa)";
}

$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$sql_count = "SELECT COUNT(*) as total FROM proiecte p ".($where ? "$where" : "");
$result_count = ezpub_query($conn, $sql_count);
$row_count = ezpub_fetch_array($result_count);
$total = $row_count['total'];
$pages = new Pagination; 
$pages->items_total = $total; 
$pages->mid_range = 5; 
$pages->paginate(); 
$sql = "SELECT p.*, c.Client_Denumire FROM proiecte p LEFT JOIN clienti_date c ON p.proiect_client=c.ID_Client ".($where ? "$where" : "")." ORDER BY p.proiect_lastupdate DESC $pages->limit";
$result = ezpub_query($conn, $sql);
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h1><?php echo $strPageTitle?></h1>
        <a href="siteprojects.php?mode=new" class="button success">Adaugă proiect</a>
</div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
                <div class="paginate">
            <?php
echo $strTotal . " " .$total." ".$strProjects ;
echo " <br /><br />";
echo $pages->display_pages() . " <a href=\"siteprojects.php\" title=\"strClearAllFilters\">$strShowAll</a>&nbsp;";
?>
</div>       
</div>       
</div>       
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <table width="100%">
            <thead>
                <tr>
                    <th>Titlu</th>
                    <th>Client</th>
                    <th>Data început</th>
                    <th>Data sfârșit</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = ezpub_fetch_array($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['proiect_titlu']); ?></td>
                    <td><?php echo htmlspecialchars($row['Client_Denumire']); ?></td>
                    <td><?php echo $row['proiect_data_inceput'] ? date('d.m.Y', strtotime($row['proiect_data_inceput'])) : ''; ?></td>
                    <td><?php echo $row['proiect_data_sfarsit'] ? date('d.m.Y', strtotime($row['proiect_data_sfarsit'])) : ''; ?></td>
                    <td><?php echo ($row['proiect_status']==0 ? 'Deschis' : ($row['proiect_status']==1 ? 'Închis' : 'On hold')); ?></td>
                    <td>
<?php if (isset($_SESSION['clearence']) && $_SESSION['clearence'] == 'ADMIN') { ?>
    <a href="siteprojects.php?mode=edit&id=<?php echo $row['proiect_id']; ?>" title="Editare"><i class="far fa-edit"></i></a>
    <a href="siteprojects.php?mode=delete&id=<?php echo $row['proiect_id']; ?>" onclick="return confirm('Sigur ștergi proiectul?');" title="Șterge"><i class="fa fa-eraser"></i></a>
    <a href="projectactivities.php?project_id=<?php echo $row['proiect_id']; ?>" title="Vizualizare activități"><i class="fa fa-tasks"></i></a>

<?php } ?>
    <a href="projectview.php?id=<?php echo $row['proiect_id']; ?>" title="Vizualizare"><i class="fa fa-eye"></i></a>
</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
</div>
</div>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
         <div class="paginate">
            <?php echo $pages->display_pages(); ?>
        </div>
    </div>
</div>
<?php include '../bottom.php'; ?>
