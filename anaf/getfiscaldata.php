<?php
// Script standalone pentru import date fiscale ANAF pentru un CUI dat
include_once '../settings.php';
include_once '../classes/common.php';
include_once __DIR__ . '/getfiscaldata.lib.php';

// Exemplu de utilizare CLI sau browser: ?cui=12345678
if (isset($_GET['cui']) || (isset($argv) && isset($argv[1]))) {
    $cui = isset($_GET['cui']) ? $_GET['cui'] : $argv[1];
    $ok = get_date_fiscale_anaf($cui, $conn);
    if ($ok) {
        echo "<div class='callout success'>Date fiscale importate pentru CUI $cui</div>";
    } else {
        echo "<div class='callout alert'>Nu s-au găsit date fiscale pentru CUI $cui</div>";
    }
}
