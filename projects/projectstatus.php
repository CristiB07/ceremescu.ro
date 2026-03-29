<?php
// projectstatus.php: adaugă, editează, șterge statusuri pentru activități proiect
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    die('Nu sunteți autentificat.');
}
$uid = $_SESSION['uid'];
$activitate_id = isset($_GET['activitate_id']) ? (int)$_GET['activitate_id'] : 0;
$status_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';
$mesaj = '';
$status_raport = '';

// ȘTERGERE
if ($action==='delete' && $status_id>0) {
    // Verifică autorul
    $stmt = mysqli_prepare($conn, "SELECT status_user_id, status_activitate_id FROM proiecte_status WHERE status_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $status_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    if ($row && $row['status_user_id']==$uid) {
        $activitate_id = $row['status_activitate_id'];
        $stmt = mysqli_prepare($conn, "DELETE FROM proiecte_status WHERE status_id=?");
        mysqli_stmt_bind_param($stmt, 'i', $status_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        // Update proiect_lastupdate
        $stmt2 = mysqli_prepare($conn, "UPDATE proiecte SET proiect_lastupdate=NOW() WHERE proiect_id=(SELECT activitate_proiect_id FROM proiecte_activitati WHERE activitate_id=?)");
        mysqli_stmt_bind_param($stmt2, 'i', $activitate_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $mesaj = '<div style="color:green;">Status șters!</div>';
    } else {
        $mesaj = '<div style=\'color:red;\'>Nu aveți dreptul să ștergeți acest status.</div>';
    }
}

// EDITARE - preluare date
if (($action==='edit' && $status_id>0) || ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['edit_status']))) {
    if ($action==='edit') {
        $stmt = mysqli_prepare($conn, "SELECT * FROM proiecte_status WHERE status_id=? AND status_user_id=?");
        mysqli_stmt_bind_param($stmt, 'ii', $status_id, $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        if ($row) {
            $status_raport = $row['status_raport'];
            $activitate_id = $row['status_activitate_id'];
        } else {
            $mesaj = '<div style="color:red;">Nu aveți dreptul să editați acest status.</div>';
        }
    }
    // Salvare editare
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['edit_status'])) {
        $status_id = (int)$_POST['status_id'];
        $status_raport = trim($_POST['status_raport'] ?? '');
        if ($status_raport!=='') {
            $stmt = mysqli_prepare($conn, "UPDATE proiecte_status SET status_raport=?, status_data_raport=NOW() WHERE status_id=? AND status_user_id=?");
            mysqli_stmt_bind_param($stmt, 'sii', $status_raport, $status_id, $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // Update proiect_lastupdate
            $stmt2 = mysqli_prepare($conn, "UPDATE proiecte SET proiect_lastupdate=NOW() WHERE proiect_id=(SELECT activitate_proiect_id FROM proiecte_activitati WHERE activitate_id=?)");
            mysqli_stmt_bind_param($stmt2, 'i', $activitate_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            $mesaj = '<div style="color:green;">Status editat!</div>';
        } else {
            $mesaj = '<div style="color:red;">Introduceți un text pentru status.</div>';
        }
    }
}

// ADAUGARE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_status']) && $activitate_id>0) {
    $raport = trim($_POST['status_raport'] ?? '');
    if ($raport!=='') {
        $stmt = mysqli_prepare($conn, "INSERT INTO proiecte_status (status_user_id, status_activitate_id, status_raport, status_data_raport) VALUES (?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'iis', $uid, $activitate_id, $raport);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        // Update proiect_lastupdate
        $stmt2 = mysqli_prepare($conn, "UPDATE proiecte SET proiect_lastupdate=NOW() WHERE proiect_id=(SELECT activitate_proiect_id FROM proiecte_activitati WHERE activitate_id=?)");
        mysqli_stmt_bind_param($stmt2, 'i', $activitate_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        $mesaj = '<div style="color:green;">Status adăugat!</div>';
    } else {
        $mesaj = '<div style=\'color:red;\'>Introduceți un text pentru status.</div>';
    }
}
$reloadParent = false;
if ($mesaj && (strpos($mesaj, 'adăugat')!==false || strpos($mesaj, 'editat')!==false || strpos($mesaj, 'șters')!==false)) {
    $reloadParent = true;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <title>Status activitate proiect</title>
    <style>body{font-family:sans-serif;font-size:15px;}textarea{width:100%;min-height:80px;}button{margin-top:8px;}</style>
    <script>
    function sendResize() {
        try {
            window.parent.postMessage({
                type: "resizeStatusIframe",
                iframeId: window.frameElement ? window.frameElement.id : '',
                height: document.body.scrollHeight + 20
            }, "*");
        } catch(e) {}
    }
    window.onload = sendResize;
    window.addEventListener('resize', sendResize);
    setTimeout(sendResize, 300);
    </script>
</head>
    <link rel="stylesheet" href="<?php echo $strSiteURL; ?>/js/simple-editor/simple-editor.css">
        <link rel="stylesheet" href="<?php echo $strSiteURL ?>/css/all.css" />
<script src="<?php echo $strSiteURL; ?>/js/simple-editor/simple-editor.js"></script>
<body style="background:transparent;" onload="sendResize()">
<?php echo $mesaj; ?>
<?php if ($reloadParent) { ?>
<script>setTimeout(function(){ if(window.parent) window.parent.location.reload(); }, 700);</script>
<?php } ?>
<?php if ($action==='edit' && $status_id>0 && $status_raport!=='') { ?>
<form method="post" onsubmit="setTimeout(sendResize, 400);">
    <input type="hidden" name="status_id" value="<?php echo $status_id; ?>">
    <label for="status_raport">Editează status:</label><br>
    <textarea name="status_raport" id="status_raport" class="simple-html-editor" data-upload-dir="projects" rows="10"required><?php echo htmlspecialchars($status_raport); ?></textarea><br>
    <button type="submit" name="edit_status">Salvează modificarea</button>
</form>
<?php } else { ?>
<form method="post" onsubmit="setTimeout(sendResize, 400);">
    <label for="status_raport">Status nou:</label><br>
    <textarea name="status_raport" id="status_raport" class="simple-html-editor" data-upload-dir="projects" rows="10"required></textarea><br>
    <button type="submit" name="add_status">Adaugă status</button>
</form>
<?php } ?>
<?php if ($action==='edit' && $status_id>0) {
    echo '<form method="get"><input type="hidden" name="activitate_id" value="'.(int)$activitate_id.'"><button type="submit">Renunță la editare</button></form>';
} ?>
</body>
</html>
