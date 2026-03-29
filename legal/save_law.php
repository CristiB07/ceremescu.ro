<?php
include '../settings.php';
include '../classes/common.php';

// buffer any accidental output so we always return valid JSON
if (!headers_sent()) ob_start();
// Basic anti-injection check
if (function_exists('check_inject')) check_inject();

$TipAct = isset($_POST['TipAct']) ? trim($_POST['TipAct']) : null;
$Numar = isset($_POST['Numar']) ? trim($_POST['Numar']) : null;
$Titlu = isset($_POST['Titlu']) ? trim($_POST['Titlu']) : null;
$DataVigoare = isset($_POST['DataVigoare']) ? trim($_POST['DataVigoare']) : null;
$Emitent = isset($_POST['Emitent']) ? trim($_POST['Emitent']) : null;
$Publicatie = isset($_POST['Publicatie']) ? trim($_POST['Publicatie']) : null;
$LinkHtml = isset($_POST['LinkHtml']) ? trim($_POST['LinkHtml']) : null;
$Text = isset($_POST['Text']) ? trim($_POST['Text']) : null;

$response = ['success' => false];

// Prepare insert
$sql = "INSERT INTO legislatie_salvata (tip_act, numar, titlu, data_vigoare, emitent, publicatie, link_html, text, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
  $response['error'] = 'Prepare failed: ' . mysqli_error($conn);
  goto SEND_JSON;
}

if (!mysqli_stmt_bind_param($stmt, 'ssssssss', $TipAct, $Numar, $Titlu, $DataVigoare, $Emitent, $Publicatie, $LinkHtml, $Text)) {
  $response['error'] = 'Bind failed: ' . mysqli_stmt_error($stmt);
  goto SEND_JSON;
}

$ok = mysqli_stmt_execute($stmt);
if ($ok) {
  $id = mysqli_insert_id($conn);
  $response = ['success' => true, 'id' => $id];
} else {
  $response['error'] = mysqli_stmt_error($stmt);
}

SEND_JSON:
// collect any buffered output (warnings/notices) and attach for debugging
$buffer = '';
if (ob_get_level()) {
  $buffer = ob_get_clean();
}
if (!empty($buffer)) {
  // sanitize HTML if present
  $san = trim(strip_tags($buffer));
  if ($san !== '') $response['debug_output'] = $san;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

?>
