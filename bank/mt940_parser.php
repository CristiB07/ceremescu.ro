<?php
// Selector parser principal
function parse_mt940($filePath, $conn, $banca) {
    global $hddpath, $transactions_folder;
    // Verific dacă fișierul există deja în folderul de upload (după nume exact)
    $upload_folder = $hddpath . '/' . $transactions_folder . '/';
    $filename = basename($filePath);
    $existing_files = glob($upload_folder . '*');
    foreach ($existing_files as $existing) {
        if (basename($existing) === $filename && realpath($existing) !== realpath($filePath)) {
            echo '<div class="callout alert">Fișierul a fost deja încărcat (același nume: ' . htmlspecialchars($filename) . ').</div>';
            return 0;
        }
    }
    if ($banca === 'ING') return parse_mt940_ing($filePath, $conn);
    if ($banca === 'BT') return parse_mt940_bt($filePath, $conn);
    if ($banca === 'UNICREDIT') return parse_mt940_unicredit($filePath, $conn);
    return 0;
}
// mt940_parser.php
// Parser universal pentru fișiere MT940 (mai multe bănci)
// Salvează tranzacțiile într-o tabelă MySQL: tranzactii_bancare

include_once '../settings.php';
include_once '../classes/common.php';

// --- FORMULAR UPLOAD MULTIPLE ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<label>Selectează fișiere MT940:</label><br />';
    echo '<input type="file" name="mt940_files[]" multiple required><br /><br />';
    echo '<label>Banca:</label> <select name="banca"><option value="ING">ING</option><option value="BT">BT</option><option value="UNICREDIT">UNICREDIT</option></select><br /><br />';
    echo '<button type="submit" class="button success">Procesează fișiere</button>';
    echo '</form>';
    exit;
}

// --- PROCESARE MULTIPLE FILES ---
if (isset($_FILES['mt940_files'])) {
    $banca = $_POST['banca'] ?? '';
    $total_proc = 0;
    foreach ($_FILES['mt940_files']['tmp_name'] as $idx => $tmp_name) {
        $filename = $_FILES['mt940_files']['name'][$idx];
        if (is_uploaded_file($tmp_name)) {
            echo '<div class="callout primary">Procesez fișierul: ' . htmlspecialchars($filename) . '</div>';
            $count = parse_mt940($tmp_name, $conn, $banca);
            echo '<div class="callout success">Tranzacții procesate: ' . $count . '</div>';
            $total_proc += $count;
        } else {
            echo '<div class="callout alert">Eroare la upload: ' . htmlspecialchars($filename) . '</div>';
        }
    }
    echo '<div class="callout info">Total tranzacții procesate: ' . $total_proc . '</div>';
    exit;
}

// Parser ING (stub)

function parse_mt940_ing($filePath, $conn) {
    $content = file_get_contents($filePath);
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $lines = explode("\n", $content);
    $iban_extras = $moneda = '';
    $tranzactii = [];
    $current = [];
    $expect_86 = false;
    foreach ($lines as $idx => $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (strpos($line, ':25:') === 0) {
            $iban_extras = trim(substr($line, 4));
        }
        if (strpos($line, ':60F:') === 0) {
            $moneda = substr(trim($line), 12, 3);
            continue;
        }
        if (strpos($line, ':61:') === 0) {
            if (!empty($current)) {
                $tranzactii[] = $current;
                $current = [];
            }
            $current['raw_61'] = $line;
            $expect_86 = true;
            continue;
        }
        if ($expect_86 && strpos($line, ':86:') === 0) {
            // colectează toate liniile de detalii până la următorul :61: sau :86:
            $block_86 = substr($line, 4) . "\n";
            for ($j = $idx + 1; $j < count($lines); $j++) {
                $next = trim($lines[$j]);
                if ($next === '' || strpos($next, ':61:') === 0 || strpos($next, ':86:') === 0) break;
                $block_86 .= $next . "\n";
            }
            $current['raw_86'] = $block_86;
            $expect_86 = false;
            continue;
        }
    }
    if (!empty($current)) {
        $tranzactii[] = $current;
    }
    $count = 0;
    foreach ($tranzactii as $idx => $tr) {
        $raw_61 = $tr['raw_61'] ?? '';
        $raw_86 = $tr['raw_86'] ?? '';
        // Exemplu :61:2512161216C726,00NTRFNONREF//043ZEXA253500213
        if (preg_match('/:61:(\d{6})(\d{4})?([CDN])([\d,]+)[A-Z]*[^\/]*\/\/([A-Z0-9\-]*)/', $raw_61, $m)) {
            $data = $m[1]; // AA MM ZZ
            $tip = $m[3];
            $suma = str_replace(',', '.', $m[4]);
            $referinta = $m[5];
        } else {
            // Data din primele 6 caractere după :61:
            if (preg_match('/:61:(\d{6})/', $raw_61, $mdat)) {
                $data = $mdat[1];
            } else {
                $data = '';
            }
            $tip = $suma = $referinta = '';
            // Fallback: extrage suma și tipul din :86: dacă există ~20AMT SNT/RCD RON suma
            if (!empty($raw_86)) {
                if (preg_match('/~20AMT (SNT|RCD) RON ([\d,.]+)/i', $raw_86, $m2)) {
                    $tip = ($m2[1] === 'SNT') ? 'D' : 'C';
                    $suma = str_replace([',', ' '], ['.', ''], $m2[2]);
                }
            }
        }
        $detalii = '';
        $iban_beneficiar = '';
        $in_out = '';
        if (!empty($raw_86)) {
            // Extrag ~25 pe mai multe linii: concatenez tot ce e după ~25 până la următorul ~ sau sfârșit
            // Extrag ~25 pe mai multe linii, accept orice spații/newline între ~ și 25
            $detalii = '';
            if (preg_match('/~[\s\r\n]*25(.*?)(~|$)/is', $raw_86, $m25)) {
                $detalii_raw = $m25[1];
                $detalii = preg_replace('/^[\r\n\s]+|[\r\n\s]+$/', '', $detalii_raw);
            } elseif (preg_match('/25(.*?)(~|$)/is', $raw_86, $m25b)) {
                $detalii_raw = $m25b[1];
                $detalii = preg_replace('/^[\r\n\s]+|[\r\n\s]+$/', '', $detalii_raw);
            } else {
                $detalii = 'Plată cu cardul';
            }
            // Dacă există ~32, concatenez la detalii
            if (preg_match('/~[\s\r\n]*32(.*?)(~|$)/is', $raw_86, $m32)) {
                $detalii_32 = preg_replace('/^[\r\n\s]+|[\r\n\s]+$/', '', $m32[1]);
                if ($detalii_32 !== '') {
                    $detalii = $detalii . ' ' . $detalii_32;
                    $detalii = trim($detalii);
                }
            }
            // OUT: ~31 IBAN, IN: ~33 IBAN
            if (preg_match('/~31\s*(RO\d{2}[A-Z0-9]{4,})/is', $raw_86, $m31)) {
                $iban_beneficiar = $m31[1];
            } elseif (preg_match('/~31\s*~\s*(RO\d{2}[A-Z0-9]{4,})/is', $raw_86, $m31next)) {
                $iban_beneficiar = $m31next[1];
            }
            // Dacă nu am găsit IBAN la ~31, caut la ~33 (inclusiv pe linia următoare sau după spații/newline)
            if (empty($iban_beneficiar)) {
                // Elimin toate newline/spații din raw_86 pentru a uni IBAN-ul spart pe linii
                $raw_86_flat = preg_replace('/[\r\n\s]+/', '', $raw_86);
                // Caut ~33RO... dar accept doar format complet de IBAN RO\d{2}[A-Z0-9]{4,}
                if (preg_match('/~33(RO\d{2}[A-Z0-9]{4,})/i', $raw_86_flat, $m33iban)) {
                    $iban_beneficiar = $m33iban[1];
                } else if (preg_match('/~33([A-Z0-9@.]+)/i', $raw_86_flat, $m33)) {
                    $iban_beneficiar = 'Plată cu cardul';
                } else {
                    $iban_beneficiar = 'COMISION BANCAR';
                }
            }
        }
        $an = '20' . substr($data, 0, 2);
        $luna = substr($data, 2, 2);
        $zi = substr($data, 4, 2);
        $data_sql = (checkdate((int)$luna, (int)$zi, (int)$an)) ? "$an-$luna-$zi" : null;
        $stmt = mysqli_prepare($conn, "INSERT INTO tranzactii_bancare (data_tranzactie, tip, suma, moneda, iban_extras, iban, detalii, referinta, in_out) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdssssss", $data_sql, $tip, $suma, $moneda, $iban_extras, $iban_beneficiar, $detalii, $referinta, $in_out);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $count++;
    }
    return $count;
}

// Parser BT
function parse_mt940_bt($filePath, $conn) {
    $content = file_get_contents($filePath);
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $lines = explode("\n", $content);
    $iban_extras = $moneda = '';
    $tranzactii = [];
    $current = [];
    foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, ':25:') === 0) {
                    $iban_extras = trim(substr($line, 4));
                }
                if (strpos($line, ':60F:') === 0) {
                    $moneda = substr(trim($line), 12, 3);
                }
                if (strpos($line, ':61:') === 0) {
                    if (!empty($current)) {
                        $tranzactii[] = $current;
                        $current = [];
                    }
                    $current['raw_61'] = $line;
                }
                if (strpos($line, ':86:') === 0) {
                    $current['raw_86'] = $line;
                    // Concatenează toate liniile de detalii până la următorul :61: sau sfârșit
                    $raw_86_block = substr($line, 4);
                    $i = array_search($line, $lines);
                    for ($j = $i + 1; $j < count($lines); $j++) {
                        $next = trim($lines[$j]);
                        if (strpos($next, ':61:') === 0 || strpos($next, ':86:') === 0) break;
                        $raw_86_block .= "\n" . $next;
                    }
                    $current['raw_86'] = ':86:' . $raw_86_block;
                }
            }
            if (!empty($current)) {
                $tranzactii[] = $current;
            }
            $count = 0;
            foreach ($tranzactii as $idx => $tr) {
                $raw_61 = $tr['raw_61'] ?? '';
                $raw_86 = $tr['raw_86'] ?? '';
                $detalii_multiline = '';
                // :61:2512161216C726,00NTRFNONREF//043ZEXA253500213
                if (preg_match('/:61:(\d{6})(\d{4})?([CDN])([\d,]+)[A-Z]*[^\/]*\/\/([A-Z0-9\-]*)/', $raw_61, $m)) {
                    $data = $m[1];
                    $tip = $m[3];
                    $suma = str_replace(',', '.', $m[4]);
                    $referinta = $m[5];
                } else {
                    $data = $tip = $suma = $referinta = '';
                }
                $detalii = '';
                $iban_beneficiar = '';
                if (!empty($raw_86)) {
                    $detalii = trim(substr($raw_86, 4));
                    $detalii_multiline = $detalii;
                    if (isset($tr['raw_86_next'])) {
                        $detalii_multiline .= "\n" . $tr['raw_86_next'];
                    }
                    // Normalizez: elimin newline-uri și spații consecutive, apoi elimin spațiile dintre SRL și IBAN
                    $detalii_flat = preg_replace('/[\r\n]+/', ' ', $detalii_multiline);
                    $detalii_flat = preg_replace('/\s+/', ' ', $detalii_flat);
                    // Caut SRL/S.R.L. urmat de orice spații/newline și apoi IBAN, chiar dacă IBAN-ul e pe linie nouă sau lipit
                    if (preg_match('/(?:SRL|S\.R\.L\.)\s*([A-Z]*)\s*(RO\d{2}[A-Z0-9]{4,})/i', $detalii_flat, $iban_match)) {
                        $iban_beneficiar = $iban_match[2];
                    }
                    // Fallback: primul IBAN din detalii dacă nu există după SRL
                    if (empty($iban_beneficiar) && preg_match('/RO\d{2}[A-Z0-9]{4,}/i', $detalii_flat, $iban_match2)) {
                        $iban_beneficiar = $iban_match2[0];
                    }
                }
                $an = '20' . substr($data, 0, 2);
                $luna = substr($data, 2, 2);
                $zi = substr($data, 4, 2);
                $data_sql = (checkdate((int)$luna, (int)$zi, (int)$an)) ? "$an-$luna-$zi" : null;
                $in_out = ($tip === 'C') ? 'IN' : (($tip === 'D') ? 'OUT' : '');
                $stmt = mysqli_prepare($conn, "INSERT INTO tranzactii_bancare (data_tranzactie, tip, suma, moneda, iban_extras, iban, detalii, referinta, in_out) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssdssssss", $data_sql, $tip, $suma, $moneda, $iban_extras, $iban_beneficiar, $detalii, $referinta, $in_out);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $count++;
            }
            return $count;
        }

        // Parser UNICREDIT
        function parse_mt940_unicredit($filePath, $conn) {
            $content = file_get_contents($filePath);
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $lines = explode("\n", $content);
            $iban_extras = $moneda = '';
            $tranzactii = [];
            $current = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, ':25:') === 0) {
                    $iban_extras = trim(substr($line, 4));
                }
                if (strpos($line, ':60F:') === 0) {
                    $moneda = substr(trim($line), 12, 3);
                }
                if (strpos($line, ':61:') === 0) {
                    if (!empty($current)) {
                        $tranzactii[] = $current;
                        $current = [];
                    }
                    $current['raw_61'] = $line;
                }
                if (strpos($line, ':86:') === 0) {
                    if (isset($current['raw_86'])) {
                        // Salvează linia următoare de detalii pentru multi-line IBAN
                        $current['raw_86_next'] = $line;
                    } else {
                        $current['raw_86'] = $line;
                    }
                }
            }
            if (!empty($current)) {
                $tranzactii[] = $current;
            }
            $count = 0;
            foreach ($tranzactii as $tr) {
                $raw_61 = $tr['raw_61'] ?? '';
                $raw_86 = $tr['raw_86'] ?? '';
                // :61:2201170117C214,2FMSC20220117704219
                if (preg_match('/:61:(\d{6})(\d{4})?([CDN])([\d,.]+)FMSC([A-Z0-9]+)/', $raw_61, $m)) {
                    $data = $m[1];
                    $tip = $m[3];
                    $suma = str_replace([',', ' '], ['.', ''], $m[4]);
                    $referinta = $m[5];
                } elseif (preg_match('/:61:(\d{6})([CDN])([\d,.]+)FMSC([A-Z0-9]+)/', $raw_61, $m)) {
                    $data = $m[1];
                    $tip = $m[2];
                    $suma = str_replace([',', ' '], ['.', ''], $m[3]);
                    $referinta = $m[4];
                } else {
                    $data = $tip = $suma = $referinta = '';
                }
                $detalii = '';
                $iban_beneficiar = '';
                if (!empty($raw_86)) {
                    $detalii = trim(substr($raw_86, 4));
                    // IBAN beneficiar: primul RO... din detalii
                    if (preg_match('/(RO\d{2}[A-Z0-9]{4,})/', $detalii, $iban_match)) {
                        $iban_beneficiar = $iban_match[1];
                    }
                }
                $an = '20' . substr($data, 0, 2);
                $luna = substr($data, 2, 2);
                $zi = substr($data, 4, 2);
                $data_sql = (checkdate((int)$luna, (int)$zi, (int)$an)) ? "$an-$luna-$zi" : null;
                $in_out = ($tip === 'C') ? 'IN' : (($tip === 'D') ? 'OUT' : '');
                $stmt = mysqli_prepare($conn, "INSERT INTO tranzactii_bancare (data_tranzactie, tip, suma, moneda, iban_extras, iban, detalii, referinta, in_out) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssdssssss", $data_sql, $tip, $suma, $moneda, $iban_extras, $iban_beneficiar, $detalii, $referinta, $in_out);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $count++;
            }
            return $count;
        }



