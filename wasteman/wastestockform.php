<?php
include '../settings.php';
include '../classes/common.php';
if(!isset($_SESSION)) { session_start(); }
if (!isSet($_SESSION['userlogedin']) OR $_SESSION['userlogedin']!="Yes"){
    header("location:$strSiteURL/login/index.php?message=MLF");
    die;
}
$client = isset($_GET['client']) ? (int)$_GET['client'] : 0;
$cod_id = isset($_GET['wID']) ? trim($_GET['wID']) : '';
$year = isset($_GET['year']) ? ((int)$_GET['year'] - 1) : (date('Y') - 1);
$msg = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client && $cod_id) {
    $stoc_cantitate = isset($_POST['stoc_cantitate']) ? floatval($_POST['stoc_cantitate']) : 0;
    // Check if record exists
    $stmt = mysqli_prepare($conn, "SELECT stoc_id FROM deseuri_stocuri WHERE stoc_client_id=? AND stoc_an_raportare=? AND stoc_cod_deseu=?");
    mysqli_stmt_bind_param($stmt, "iss", $client, $year, $cod_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    if ($exists) {
        // Update
        $stmt = mysqli_prepare($conn, "UPDATE deseuri_stocuri SET stoc_cantitate=? WHERE stoc_client_id=? AND stoc_an_raportare=? AND stoc_cod_deseu=?");
        mysqli_stmt_bind_param($stmt, "diss", $stoc_cantitate, $client, $year, $cod_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = '<div style="color:green;">Stoc actualizat!</div>';
    } else {
        // Insert
        $stmt = mysqli_prepare($conn, "INSERT INTO deseuri_stocuri (stoc_client_id, stoc_an_raportare, stoc_cod_deseu, stoc_cantitate) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issd", $client, $year, $cod_id, $stoc_cantitate);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = '<div style="color:green;">Stoc adăugat!</div>';
    }
}
// Get current value
// Preluare denumire client
$client_denumire = '';
if ($client) {
    $stmt = mysqli_prepare($conn, "SELECT Client_Denumire FROM clienti_date WHERE ID_Client=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $client);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $client_denumire);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
$stoc_cantitate = '';
if ($client && $cod_id) {
    $stmt = mysqli_prepare($conn, "SELECT stoc_cantitate FROM deseuri_stocuri WHERE stoc_client_id=? AND stoc_an_raportare=? AND stoc_cod_deseu=?");
    mysqli_stmt_bind_param($stmt, "iss", $client, $year, $cod_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $stoc_cantitate);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Stoc deșeu</title>
    <style>
        body { font-size: 14px; font-family: Arial, sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
        form { margin: 0; }
        .stoc-form { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
        .stoc-form label { display: block; margin-bottom: 4px; }
        .stoc-form input[type="number"] { width: 100%; padding: 4px; margin-bottom: 8px; }
        .stoc-form button { padding: 6px 16px; background: #4CAF50; color: #fff; border: none; border-radius: 3px; cursor: pointer; }
        .stoc-form button:hover { background: #388e3c; }
    </style>
</head>
<body>
<div class="stoc-form">
    <?php if ($msg) echo $msg; ?>
    <form method="post">
        <label for="stoc_cantitate">Client: <strong><?php echo htmlspecialchars($client_denumire); ?></strong><br>Stoc curent pentru cod: <strong><?php echo htmlspecialchars($cod_id); ?></strong> (an: <?php echo $year; ?>)</label>
        <input type="number" step="0.01" min="0" name="stoc_cantitate" id="stoc_cantitate" value="<?php echo htmlspecialchars($stoc_cantitate); ?>" required>
        <button type="submit">Salvează stoc</button>
    </form>
</div>
</body>
</html>
