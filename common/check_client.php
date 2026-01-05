<?php
//update 8.01.2025
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';

// Validate and sanitize input
if (!isset($_POST['keyword']) || empty(trim($_POST['keyword']))) {
    echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
    exit();
}

$keyword = '%' . $_POST['keyword'] . '%';

// Use prepared statement to prevent SQL injection
$sql = "SELECT ID_Client, Client_Denumire, Client_CUI 
        FROM clienti_date 
        WHERE Client_CUI LIKE ? OR Client_Denumire LIKE ? 
        ORDER BY Client_Denumire ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $keyword, $keyword);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$nume = mysqli_num_rows($result);
?>
<?php
if ($nume == 0) {
    echo htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8');
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $id_client = htmlspecialchars($row["ID_Client"] ?? '', ENT_QUOTES, 'UTF-8');
        $client_cui = htmlspecialchars($row["Client_CUI"] ?? '', ENT_QUOTES, 'UTF-8');
        $client_denumire = htmlspecialchars($row["Client_Denumire"] ?? '', ENT_QUOTES, 'UTF-8');
        $str_edit = htmlspecialchars($strEdit, ENT_QUOTES, 'UTF-8');
?>
<a href="siteclients.php?mode=edit&cID=<?php echo $id_client; ?>">
    <?php echo $client_cui . " " . $client_denumire; ?> - <i class="far fa-edit fa-xl"
        title="<?php echo $str_edit; ?>"></i></a><br />
<?php 
    }
}
mysqli_stmt_close($stmt);
?>