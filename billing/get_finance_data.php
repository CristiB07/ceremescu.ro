<?php
// Endpoint pentru returnarea datelor financiare JSON
include '../settings.php';

if(!isset($_SESSION)) { 
    session_start(); 
}

// Verificare autentificare
if (!isset($_SESSION['userlogedin']) OR $_SESSION['userlogedin'] != "Yes") {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Construire cale către fișierul JSON
$json_file = $hddpath . "/" . $charts_folder . "/finance_comparison_" . date('Y-m-d') . ".json";

// Verificare existență fișier
if (!file_exists($json_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'JSON file not found', 'path' => $json_file]);
    exit;
}

// Citire și returnare JSON
$json_content = file_get_contents($json_file);

if ($json_content === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read JSON file']);
    exit;
}

// Setare header pentru JSON
header('Content-Type: application/json; charset=utf-8');
echo $json_content;
?>
