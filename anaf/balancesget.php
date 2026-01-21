<?php
include '../settings.php';
include '../classes/common.php';
include 'balancesgetlib.php';

// Formular HTML pentru introducere CUI și an
echo '<form method="get" action="">
    <label for="cui">CUI:</label>
    <input type="number" name="cui" id="cui" required value="'.htmlspecialchars(isset($_GET['cui']) ? $_GET['cui'] : '').'" />
     <button type="submit">Importă bilanț</button>
</form><br />';

if (isset($_GET['cui'])) {
    $cui = (int)$_GET['cui'];
    // Determină anul maxim disponibil conform regulii ANAF
    $today = new DateTime();
    $limit_year = ( ($today->format('n') < 8) ? ($today->format('Y') - 2) : ($today->format('Y') - 1) );
    $limit_year = min($limit_year, 2024); // ANAF nu are date peste 2024
    $years = [];
    for ($y = $limit_year; $y >= 2017; $y--) {
        $years[] = $y;
    }
    // Selectează anii existenți în DB
    $query = "SELECT an FROM bilanturi WHERE cui = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $cui);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing[] = (int)$row['an'];
    }
    mysqli_stmt_close($stmt);
    // Importă anii lipsă
    $imported = 0;
    foreach ($years as $an) {
        if (in_array($an, $existing)) continue;
    }
    $imported = import_bilanturi_anaf($cui, $conn);
    if ($imported > 0) {
        echo "<div class='callout success'>Importate $imported bilanțuri noi!</div>";
    } else {
        echo "<div class='callout info'>Toate bilanțurile sunt deja importate!</div>";
    }
}
?>