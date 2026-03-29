<?php
// CLI test for just_search functions
define('JUST_SEARCH_NO_RENDER', true);
require_once __DIR__ . '/just_search.php';

// perform token + search in one flow
$t = getToken();
if (isset($t['error'])) {
    echo "GetToken error:\n" . print_r($t, true) . "\n";
    exit(1);
}
$token = $t['token'] ?? null;
if (!$token) {
    echo "No token returned\n";
    exit(1);
}
$searchTitlu = 'deșeuri';
$out = searchLegislation($token, 0, 10, null, null, null, $searchTitlu);
echo json_encode(['token' => $token, 'search' => $out], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
