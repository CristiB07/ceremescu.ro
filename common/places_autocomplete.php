<?php
include_once __DIR__ . '/../settings.php';
include_once __DIR__ . '/../classes/common.php';

header('Content-Type: application/json; charset=utf-8');
if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
    echo json_encode(['status' => 'INVALID_REQUEST', 'predictions' => []]);
    exit;
}
$q = trim($_GET['q']);
// Restrict to RO by default
$components = 'country:ro';
$key = isset($google_maps_api_key) && !empty($google_maps_api_key) ? $google_maps_api_key : '';
if (empty($key)) {
    http_response_code(500);
    echo json_encode(['status' => 'NO_KEY']);
    exit;
}
$url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . urlencode($q) . '&key=' . urlencode($key) . '&components=' . urlencode($components) . '&language=ro&types=establishment|geocode';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$err = curl_errno($ch) ? curl_error($ch) : '';
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (PHP_VERSION_ID < 80500) { curl_close($ch); }

if ($response === false) {
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'error' => $err]);
    exit;
}

$parsed = json_decode($response, true);
if (!$parsed) {
    echo json_encode(['status'=>'INVALID_RESPONSE','raw'=>$response]);
    exit;
}
// Return only predictions array
$preds = $parsed['predictions'] ?? [];

echo json_encode(['status' => $parsed['status'] ?? 'OK', 'predictions' => $preds]);
exit;
?>