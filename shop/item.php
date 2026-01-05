<?php
//update 8.01.2025
include '../settings.php';
include '../classes/common.php';

// Validare și sanitizare input
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    include '../header.php';
    echo '<div class="grid-x grid-padding-x">';
    echo '<div class="large-12 medium-12 small-12 cell">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div></div></div>'; 
    include('../bottom.php');
    exit;
}

// Validare action - doar valorile permise
$allowed_actions = ['delete', 'add', 'decrease'];
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions, true)) {
    $action = $_GET['action'];
} else {
    include '../header.php';
    echo '<div class="grid-x grid-padding-x">';
    echo '<div class="large-12 medium-12 small-12 cell">';
    echo '<div class="callout alert">' . htmlspecialchars($strThereWasAnError, ENT_QUOTES, 'UTF-8') . '</div></div></div>'; 
    include('../bottom.php');
    exit;
}

if ($action == "delete") {
    // Folosește prepared statement pentru SQL injection prevention
    $stmt = mysqli_prepare($conn, "DELETE FROM magazin_articole WHERE articol_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: order.php");
    exit;
}
elseif ($action == "add") {
    // Operație atomică cu UPDATE pentru race condition prevention
    $stmt = mysqli_prepare($conn, "UPDATE magazin_articole SET articol_cantitate = articol_cantitate + 1 WHERE articol_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: order.php");
    exit;
}
elseif ($action == "decrease") {
    // Scade cantitatea cu 1
    $stmt = mysqli_prepare($conn, "UPDATE magazin_articole SET articol_cantitate = articol_cantitate - 1 WHERE articol_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Șterge articolul dacă cantitatea a ajuns la 0
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM magazin_articole WHERE articol_id=? AND articol_cantitate <= 0");
    mysqli_stmt_bind_param($stmt_delete, 'i', $id);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);
    
    header("Location: order.php");
    exit;
}
?>