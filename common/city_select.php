<?php
//update 8.01.2025
include '../settings.php';
include '../lang/language_RO.php';
include '../classes/common.php';

// Validate and sanitize input
if (!isset($_POST['keyword']) || empty(trim($_POST['keyword']))) {
    echo '<div class="parent"><ul id="country-list"><li>' . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . '</li></ul></div>';
    exit();
}

$keyword = '%' . $_POST['keyword'] . '%';

// Fixed query with proper GROUP BY including all non-aggregated columns
$sql = "SELECT id, name, county_id, county 
        FROM generale_localitati 
        WHERE name LIKE ? 
        GROUP BY id, name, county_id, county 
        ORDER BY name ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $keyword);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$nume = mysqli_num_rows($result);
?>
<div class="parent">
    <ul id="country-list">
        <?php
if ($nume == 0) {
    echo "<li>" . htmlspecialchars($strNoRecordsFound, ENT_QUOTES, 'UTF-8') . "</li>";
} else {
    while ($city = mysqli_fetch_assoc($result)) {
        $city_name = htmlspecialchars($city["name"] ?? '', ENT_QUOTES, 'UTF-8');
        $city_county = htmlspecialchars($city["county"] ?? '', ENT_QUOTES, 'UTF-8');
        $city_select = htmlspecialchars($city_name . ' - ' . $city_county, ENT_QUOTES, 'UTF-8');
        $str_county = htmlspecialchars($strCounty, ENT_QUOTES, 'UTF-8');
?>
        <li onClick="selectCity('<?php echo $city_select; ?>');">
            <?php echo $city_name; ?>, <?php echo $str_county; ?> <?php echo $city_county; ?></li>
        <?php 
    }
}
mysqli_stmt_close($stmt);
?>
    </ul>
</div>