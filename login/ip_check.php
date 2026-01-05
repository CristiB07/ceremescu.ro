<?php
// IP whitelist pentru acces la folderul login
$allowed_ip = '176.223.121.146';

// Obține IP-ul real al clientului
function get_client_ip() {
    $ip = '';
    
    // Verifică dacă vine prin proxy sau load balancer
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Poate fi o listă de IP-uri, luăm primul
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return trim($ip);
}

$client_ip = get_client_ip();

// Verifică dacă IP-ul este permis
if ($client_ip !== $allowed_ip) {
    // Obține URL-ul de bază al site-ului
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $redirect_url = $protocol . $host . '/account/';
    
    // Redirecționează către /account
    header("Location: " . $redirect_url);
    exit();
}
?>
