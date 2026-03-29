<?php
// ajax search for CPV codes/descriptions
include '../settings.php';
include '../classes/common.php';
include '../lang/language_RO.php';

// simple authentication -- could reuse session check if needed
// we allow unauthenticated use; frontend must be protected by admin page

$keyword = '';
if (isset($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']);
}

if ($keyword === '') {
    echo '<div class="parent"><ul id="country-list"><li>' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</li></ul></div>';
    exit;
}

$like = '%' . $keyword . '%';
$sql = "SELECT code_cpv, code_romana FROM generale_coduri_cpv
        WHERE code_cpv LIKE ? OR code_romana LIKE ?
        ORDER BY code_cpv ASC LIMIT 30";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_num_rows($result);
} else {
    $count = 0;
}
?>
<div class="parent">
    <ul id="country-list">
        <?php
if ($count == 0) {
    echo '<li>' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</li>';
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $cpv = htmlspecialchars($row['code_cpv'] ?? '', ENT_QUOTES, 'UTF-8');
        $descr = htmlspecialchars($row['code_romana'] ?? '', ENT_QUOTES, 'UTF-8');
        $display = $cpv . ' - ' . $descr;
?>
        <li onClick="selectCPV('<?php echo $display;?>');"><?php echo $display; ?></li>
        <?php
    }
}
mysqli_stmt_close($stmt);
?>
    </ul>
</div>
