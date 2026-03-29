<?php
// ajax search for NC8 codes/descriptions
include '../settings.php';
include '../classes/common.php';
include '../lang/language_RO.php';

$keyword = '';
if (isset($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']);
}

if ($keyword === '') {
    echo '<div class="parent"><ul id="country-list"><li>' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</li></ul></div>';
    exit;
}

$like = '%' . $keyword . '%';
$sql = "SELECT CN, DM_RO FROM generale_coduri_nc8
        WHERE CN LIKE ? OR DM_RO LIKE ?
        ORDER BY CN ASC LIMIT 30";
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
        $cn = htmlspecialchars($row['CN'] ?? '', ENT_QUOTES, 'UTF-8');
        $descr = htmlspecialchars($row['DM_RO'] ?? '', ENT_QUOTES, 'UTF-8');
        $display = $cn . ' - ' . $descr;
?>
        <li onClick="selectNC8('<?php echo $display;?>');"><?php echo $display; ?></li>
        <?php
    }
}
mysqli_stmt_close($stmt);
?>
    </ul>
</div>
