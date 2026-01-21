<?php
// Formular upload fișier MT940 și apelare parser
include '../settings.php';
include '../classes/common.php';
$strPageTitle = "Import tranzacții bancare MT940";
include '../dashboard/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['mt940file'])) {
    $upload_dir = $hddpath . "/" . $transactions_folder . "/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    include 'mt940_parser.php';
    $banca = $_POST['banca'];
    $total_import = 0;
    $msg = '';
    // Suportă upload multiplu
    $files = $_FILES['mt940file'];
    $count_files = is_array($files['name']) ? count($files['name']) : 1;
    for ($i = 0; $i < $count_files; $i++) {
        $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $target = $upload_dir . basename($name);
        if (is_uploaded_file($tmp_name) && move_uploaded_file($tmp_name, $target)) {
            $nr = parse_mt940($target, $conn, $banca);
            $msg .= "<div>Import reușit: $nr tranzacții din $name.</div>";
            $total_import += $nr;
        } else {
            $msg .= "<div style='color:red;'>Eroare la upload: $name</div>";
        }
    }
    $msg .= "<div style='font-weight:bold;'>Total tranzacții importate: $total_import</div>";
}
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 medium-12 small-12 cell">
<h1><?php echo htmlspecialchars($strPageTitle)?></h1>
<?php if (!empty($msg)) echo "<div style='color:green;'>$msg</div>"; ?>
<div class="grid-x grid-margin-x">
    <div class="large-3 medium-3 small-12 cell">
<form method="post" enctype="multipart/form-data">
    <label>Fișier MT940:
    <input type="file" name="mt940file[]" multiple required accept=".txt,.sta">
    </label>
</div>
    <div class="large-3 medium-3 small-12 cell">
    <label>Banca:
        <select name="banca" required>
            <option value="ING">ING</option>
            <option value="BT">Banca Transilvania</option>
            <option value="UNICREDIT">Unicredit</option>
        </select>
    </label>
</div>
    <div class="large-3 medium-3 small-12 cell"><label>&nbsp;</label> <button type="submit" class="button"><i class="fa fa-upload"></i> Încarcă și importă</button>
</form>
</div>
    <div class="large-3 medium-3 small-12 cell">
<p><label>&nbsp;</label><a href="sitebanktransactions.php" class="button"><i class="fa fa-search"></i> Vezi extrase importate</a></p>
</div>
</div>

</div>
</div>
<?php
include '../bottom.php';
?>