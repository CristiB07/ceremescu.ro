<?php
$strPageTitle = "Activități proiect";
include '../settings.php';
include '../classes/common.php';
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

// ID proiect necesar
if (!isset($_GET['project_id']) || !is_numeric($_GET['project_id'])) {
    echo '<div class="callout alert">ID proiect lipsă sau invalid.</div>';
    include '../bottom.php';
    die;
}
$projectID = (int)$_GET['project_id'];

// Ștergere activitate
if (isset($_GET['mode']) && $_GET['mode']=="delete" && isset($_GET['id']) && $role=="ADMIN") {
    $actID = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM proiecte_activitati WHERE activitate_id=? AND activitate_proiect_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $actID, $projectID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo '<div class="callout success">Activitate ștearsă!</div>';
    echo '<script>setTimeout(function(){ window.location="projectactivities.php?project_id='.$projectID.'"; }, 1500);</script>';
    include '../bottom.php';
    die;
}

// Adăugare/Modificare activitate
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titlu = trim($_POST['activitate_titlu'] ?? '');
    $descriere = trim($_POST['activitate_descriere'] ?? '');
    $data_inceput = $_POST['activitate_data_inceput'] ?? null;
    $data_sfarsit = $_POST['activitate_data_sfarsit'] ?? null;
    $importanta = trim($_POST['activitate_importanta'] ?? '');
    $responsabil = isset($_POST['activitate_responsabil']) ? (int)$_POST['activitate_responsabil'] : 0;
    if ($_GET['mode']=="new") {
        $stmt = mysqli_prepare($conn, "INSERT INTO proiecte_activitati (activitate_proiect_id, activitate_titlu, activitate_descriere, activitate_data_inceput, activitate_data_sfarsit, activitate_importanta, activitate_responsabil) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssssi', $projectID, $titlu, $descriere, $data_inceput, $data_sfarsit, $importanta, $responsabil);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">Activitate adăugată!</div>';
    } else if ($_GET['mode']=="edit" && isset($_GET['id'])) {
        $actID = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "UPDATE proiecte_activitati SET activitate_titlu=?, activitate_descriere=?, activitate_data_inceput=?, activitate_data_sfarsit=?, activitate_importanta=?, activitate_responsabil=? WHERE activitate_id=? AND activitate_proiect_id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssii', $titlu, $descriere, $data_inceput, $data_sfarsit, $importanta, $responsabil, $actID, $projectID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<div class="callout success">Activitate modificată!</div>';
    }
    echo '<script>setTimeout(function(){ window.location="projectactivities.php?project_id='.$projectID.'"; }, 1500);</script>';
    include '../bottom.php';
    die;
}

// Formular adăugare/editare
if (isset($_GET['mode']) && ($_GET['mode']=="new" || ($_GET['mode']=="edit" && isset($_GET['id'])))) {
    $edit = ($_GET['mode']=="edit");
    $row = [
        'activitate_titlu' => '', 'activitate_descriere' => '', 'activitate_data_inceput' => '', 'activitate_data_sfarsit' => '', 'activitate_importanta' => '', 'activitate_responsabil' => ''
    ];
    if ($edit) {
        $actID = (int)$_GET['id'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM proiecte_activitati WHERE activitate_id=? AND activitate_proiect_id=?");
        mysqli_stmt_bind_param($stmt, 'ii', $actID, $projectID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }
    // Select utilizatori USER pentru responsabil
    $utilizatori = [];
    $q = ezpub_query($conn, "SELECT utilizator_ID, utilizator_Prenume, utilizator_Nume FROM date_utilizatori WHERE utilizator_Role='USER' ORDER BY utilizator_Nume ASC");
    while ($u = ezpub_fetch_array($q)) $utilizatori[] = $u;
    ?>
        <link rel="stylesheet" href="../js/simple-editor/simple-editor.css">
<script src="../js/simple-editor/simple-editor.js"></script>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
            <h1><?php echo $edit ? 'Editare' : 'Adăugare'; ?> activitate proiect</h1>
            <form method="post" action="projectactivities.php?project_id=<?php echo $projectID; ?>&mode=<?php echo $edit ? 'edit&id='.$actID : 'new'; ?>">
</div>  
</div>  
<div class="grid-x grid-margin-x">
        <div class="large-3 medium-3 small-6 cell">
<label>Titlu activitate
                    <input type="text" name="activitate_titlu" value="<?php echo htmlspecialchars($row['activitate_titlu']); ?>" required />
                </label>
        </div>
        <div class="large-2 medium-2 small-6 cell">
          <label>Data început
                    <input type="date" name="activitate_data_inceput" value="<?php echo htmlspecialchars(substr($row['activitate_data_inceput'],0,10)); ?>" />
                </label>
        </div>
        <div class="large-2 medium-2 small-6 cell">
                <label>Data sfârșit
                    <input type="date" name="activitate_data_sfarsit" value="<?php echo htmlspecialchars(substr($row['activitate_data_sfarsit'],0,10)); ?>" />
                </label>
        </div>
        <div class="large-1 medium-1 small-4 cell">
                <label>Importanță
                    <input type="text" name="activitate_importanta" value="<?php echo htmlspecialchars($row['activitate_importanta']); ?>" />
                </label>
        </div>
        <div class="large-4 medium-4 small-8 cell">
            <label>Responsabil activitate
                <select name="activitate_responsabil">
                    <option value="">Alege responsabil</option>
                    <?php foreach($utilizatori as $u) { $sel = ($row['activitate_responsabil']==$u['utilizator_ID']) ? 'selected' : ''; ?>
                    <option value="<?php echo $u['utilizator_ID']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($u['utilizator_Prenume'].' '.$u['utilizator_Nume']); ?></option>
                    <?php } ?>
                </select>
            </label>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell">
                <label>Descriere
                    <textarea name="activitate_descriere" class="simple-html-editor" data-upload-dir="projects" rows="10"><?php echo htmlspecialchars($row['activitate_descriere']); ?></textarea>
                </label>
        </div>
    </div>
    <div class="grid-x grid-margin-x">
        <div class="large-12 cell text-center">
              
                <button type="submit" class="button success">Salvează</button>
                <a href="projectactivities.php?project_id=<?php echo $projectID; ?>" class="button secondary">Renunță</a>
            </form>
        </div>
    </div>
    <?php
    include '../bottom.php';
    die;
}

// Listare activități proiect
$q = ezpub_query($conn, "SELECT * FROM proiecte_activitati WHERE activitate_proiect_id='$projectID' ORDER BY activitate_data_inceput ASC");
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h1><?php echo $strPageTitle?></h1>
        <a href="projectactivities.php?project_id=<?php echo $projectID; ?>&mode=new" class="button success">Adaugă activitate</a>
        <table width="100%">
            <thead>
                <tr>
                    <th>Titlu</th>
                    <th>Data început</th>
                    <th>Data sfârșit</th>
                    <th>Importanță</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = ezpub_fetch_array($q)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['activitate_titlu']); ?></td>
                    <td><?php echo $row['activitate_data_inceput'] ? date('d.m.Y', strtotime($row['activitate_data_inceput'])) : ''; ?></td>
                    <td><?php echo $row['activitate_data_sfarsit'] ? date('d.m.Y', strtotime($row['activitate_data_sfarsit'])) : ''; ?></td>
                    <td><?php echo htmlspecialchars($row['activitate_importanta']); ?></td>
                    <td>
                        <a href="projectactivities.php?project_id=<?php echo $projectID; ?>&mode=edit&id=<?php echo $row['activitate_id']; ?>" title="Editare"><i class="far fa-edit"></i></a>
                        <?php if($role=="ADMIN"){ ?>
                        <a href="projectactivities.php?project_id=<?php echo $projectID; ?>&mode=delete&id=<?php echo $row['activitate_id']; ?>" onclick="return confirm('Sigur ștergi activitatea?');" title="Șterge"><i class="fa fa-eraser"></i></a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="projectview.php?id=<?php echo $projectID; ?>" class="button secondary">Înapoi la proiect</a>
    </div>
</div>
<?php include '../bottom.php'; ?>
