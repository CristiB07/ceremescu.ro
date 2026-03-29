<?php
// *****************************************************************
// Converteste suma din cifre in litere
// $No - numarul de convertit
// $sp - delimitator mii
// $pct - punct zecimal
// @dEchim 06-2006  *****************************************************************

function StrLei($No, $sp='.', $pct=',' ) {

// Input validation
if (!is_numeric($No) && !is_string($No)) {
    return "zero lei";
}

$origInput = null;
// If it's a string, normalize separators for numeric conversion but keep original for debugging
if (is_string($No)) {
    $origInput = $No;
    // keep only digits, separators and sign
    $s = trim($No);
    $s = preg_replace('/[^0-9.,\-]/u', '', $s);

    // decide which character is decimal vs thousands
    if (strpos($s, '.') !== false && strpos($s, ',') !== false) {
        $lastDot = strrpos($s, '.');
        $lastComma = strrpos($s, ',');
        if ($lastComma > $lastDot) {
            // comma is decimal, dot is thousands
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // dot is decimal, comma is thousands
            $s = str_replace(',', '', $s);
        }
    } elseif (strpos($s, ',') !== false) {
        // only comma present -> comma is decimal
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } elseif (strpos($s, '.') !== false) {
        // only dot present -> use heuristic
        if (substr_count($s, '.') > 1) {
            // multiple dots -> thousands separators
            $s = str_replace('.', '', $s);
        } else {
            $parts = explode('.', $s);
            $fracLen = isset($parts[1]) ? strlen($parts[1]) : 0;
            // treat single dot as thousands separator only when fractional part is exactly 3 digits
            if ($fracLen === 3) {
                $s = str_replace('.', '', $s);
            } else {
                // dot is decimal
                $s = str_replace(',', '', $s);
            }
        }
    }

    // normalize to dot decimal for float conversion
    $No = floatval($s);
}

// Prevent negative numbers
$No = abs($No);

// Limit to reasonable values (999 trillion)
if ($No > 999999999999999) {
    return "număr prea mare";
}

// numerele literal
$na = array ( "", "unu", "doi", "trei", "patru", "cinci", "șase", "șapte", "opt", "nouă");
$nb = array ( "", "un",  "două", "trei", "patru", "cinci", "șase", "șapte", "opt", "nouă");
$nc = array ( "", "una", "două","trei", "patru", "cinci", "șase", "șapte", "opt", "nouă");
$nd = array ( "", "unu", "două", "trei", "patru", "cinci", "șase", "șapte", "opt", "nouă");

// exceptie "saizeci"
$ex1 = 'șai';
// unitati
$ua = array ( "", "zece", "zeci", "zeci","zeci","zeci","zeci","zeci","zeci","zeci");
$ub = array ( "", "sută", "sute", "sute","sute","sute","sute","sute","sute","sute");
$uc = array ( "", "mie", "mii");
$ud = array ( "", "milion", "milioane");
$ue = array ( "", "miliard", "miliarde");

// legatura intre grupuri
$lg1 = array ("", "spre", "spre", "spre", "spre", "spre", "spre", "spre", "spre", "spre");
$lg2 = array ("", "", "și",  "și", "și", "și", "și", "și", "și", "și" );

// moneda
$mon = array ("", " leu", " lei");
$ban = array ("", " ban ", " bani ");

// Sanitize delimiters to prevent injection
$sp = substr($sp, 0, 1);
$pct = substr($pct, 0, 1);
// Build canonical string representation from the numeric value so separators match $sp/$pct
$sNo = number_format($No, 2, $pct, $sp);
// remove thousands separator from the string representation
$sNo = str_replace($sp, "", $sNo);

//extrag partea intreaga și o completez cu zerouri la stg.
$NrI = sprintf("%012s",(string) strtok($sNo,$pct));

// extrag zecimalele
$Zec = (string) strtok($pct);
$Zec = substr($Zec . '00',0,2);

// grupul 4 (miliarde)
$Gr = substr($NrI,0,3);
$n1 = (int) $NrI[0];
$n2 = (int) $NrI[1];
$n3 = (int) $NrI[2];
// build group text with spaces between hundred/multipliers; keep tens concatenated (ex: douăzeci)
$group = trim($nc[$n1] . ' ' . $ub[$n1]);
if ($n2 == 1) {
    // teens (unsprezece etc.) remain concatenated
    $group .= $nb[$n3] . $lg1[$n3] . $ua[$n2];
} else {
    $tens = ($n2==6 ? $ex1 : $nc[$n2]) . $ua[$n2];
    if ($tens !== '') {
        $group = trim($group . ($group !== '' ? ' ' . $tens : $tens));
    }
    $suffix = ($Gr=="001"||$Gr=="002") ? $nb[$n3] : $nd[$n3];
    if ($suffix !== '') {
        $group .= ' ' . $suffix;
    }
}
$Rez = ($Gr == "000") ? '' : trim($group . ' ' . ($Gr == "001" ? $ue[1] : $ue[2]));

// grupul 3 (milioane)
$Gr = substr($NrI,3,3);
$n1 = (int) $NrI[3];
$n2 = (int) $NrI[4];
$n3 = (int) $NrI[5];
$segment = trim($nc[$n1] . ' ' . $ub[$n1]);
if ($segment !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . $segment; }
$append = '';
if ($n2 == 1) {
    $append = $nb[$n3] . $lg1[$n3] . $ua[$n2];
} else {
    $append = ($n2==6 ? $ex1 : $nc[$n2]) . $ua[$n2];
    if ($lg2[$n2]) { $append .= ' ' . $lg2[$n2]; }
    $suffix = ($Gr=="001"||$Gr=="002") ? $nb[$n3] : $nd[$n3];
    if ($suffix) { $append .= ' ' . $suffix; }
}
if (trim($append) !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . trim($append); }
$Rez = ($Gr == "000") ? $Rez : ($Rez . ' ' . ($Gr == "001" ? $ud[1] : $ud[2]));

// grupul 2 (mii)
$Gr = substr($NrI,6,3);
$n1 = (int) $NrI[6];
$n2 = (int) $NrI[7];
$n3 = (int) $NrI[8];
$segment = trim($nc[$n1] . ' ' . $ub[$n1]);
if ($segment !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . $segment; }
$append = '';
if ($n2 == 1) {
    $append = $nb[$n3] . $lg1[$n3] . $ua[$n2];
} else {
    $append = ($n2==6 ? $ex1 : $nc[$n2]) . $ua[$n2];
    if ($lg2[$n2]) { $append .= ' ' . $lg2[$n2]; }
    $suffix = ($Gr=="001"||$Gr=="002") ? $nc[$n3] : $nd[$n3];
    if ($suffix) { $append .= ' ' . $suffix; }
}
if (trim($append) !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . trim($append); }
$Rez = ($Gr == "000") ? $Rez : ($Rez . ' ' . ($Gr == "001" ? $uc[1] : $uc[2]));

 // grupul 1 (unitati)
$Gr = substr($NrI,9,3);
$n1 = (int) $NrI[9];
$n2 = (int) $NrI[10];
$n3 = (int) $NrI[11];
$segment = trim($nc[$n1] . ' ' . $ub[$n1]);
if ($segment !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . $segment; }
$append = '';
if ($n2 == 1) {
    $append = $nb[$n3] . $lg1[$n3] . $ua[$n2] . $mon[2];
} else {
    $append = ($n2==6 ? $ex1 : $nc[$n2]) . $ua[$n2];
    if ($n3 > 0 && $lg2[$n2]) { $append .= ' ' . $lg2[$n2]; }
    $append .= ' ' . trim(($NrI=="000000000001" ? ($nb[$n3] . $mon[1]) : ($na[$n3] . $mon[2])));
}
if (trim($append) !== '') { $Rez .= ($Rez !== '' ? ' ' : '') . trim($append); }

if ((int) $NrI == 0) {$Rez = ""; }

// banii
if ((int) $Zec>0) 
{
 $n2 = (int) substr($Zec,0,1);
 $n3 = (int) substr($Zec,1,1);
 $Rez .= ' și ';
 $parts = array();
 if ($n2 == 1) {
     // teens (unsprezece bani) - keep concatenated
     $parts[] = trim($nb[$n3] . $lg1[$n3] . $ua[$n2]);
     $parts[] = trim($ban[2]);
 } else {
     $tens = trim(($n2==6 ? $ex1 : $nc[$n2]) . $ua[$n2]);
     if ($tens !== '') { $parts[] = $tens; }
     if ($n3 > 0) {
         if ($lg2[$n2]) { $parts[] = $lg2[$n2]; }
         if ($Zec == "01") {
             $parts[] = trim($nb[$n3]) . ' ' . trim($ban[1]);
         } else {
             $parts[] = trim($na[$n3]) . ' ' . trim($ban[2]);
         }
     } else {
         $parts[] = trim($ban[2]);
     }
 }
 $Rez .= implode(' ', array_filter($parts));
}
return $Rez;
}