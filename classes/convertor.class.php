<?php
//update 8.01.2025
//error_reporting(E_ALL);
// *****************************************************************
// Converteste suma din cifre in litere
// $No - numarul de convertit
// $sp - delimitator mii
// $pct - punct zecimal
// @dEchim 06-2006  *****************************************************************

function StrLei($No, $sp='.', $pct=',' ) {

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

//se elimina $sp din numar
$sNo = (string) $No;
$sNo = str_replace($sp,"",$sNo);

//extrag partea intreaga și o completez cu zerouri la stg.
$NrI = sprintf("%012s",(string) strtok($sNo,$pct));

// extrag zecimalele
$Zec = (string) strtok($pct);
$Zec = substr($Zec . '00',0,2);

// grupul 4 (miliarde)
$Gr = substr($NrI,0,3);
$n1 = (integer) $NrI[0];
$n2 = (integer) $NrI[1];
$n3 = (integer) $NrI[2];
$Rez = $nc[$n1] . $ub[$n1];
$Rez = ($n2 == 1)?$Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2]:
                $Rez . ($n2==6?$ex1:$nc[$n2]) . $ua[$n2] . $lg2[$n2] . ($Gr=="001"||$Gr=="002"?$nb[$n3]:$nd[$n3]);
               
$Rez = ($Gr == "000")?$Rez:(($Gr == "001")?($Rez . $ue[1]):($Rez . $ue[2]));

// grupul 3 (milioane)
$Gr = substr($NrI,3,3);
$n1 = (integer) $NrI[3];
$n2 = (integer) $NrI[4];
$n3 = (integer) $NrI[5];
$Rez = $Rez . $sp . $nc[$n1] . $ub[$n1];
$Rez = ($n2 == 1)?$Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2]:
                $Rez . ($n2==6?$ex1:$nc[$n2]) . $ua[$n2] . $lg2[$n2] . ($Gr=="001"||$Gr=="002"?$nb[$n3]:$nd[$n3]);
$Rez = ($Gr == "000")?$Rez:(($Gr == "001")?($Rez . $ud[1]):($Rez . $ud[2]));

// grupul 2 (mii)
$Gr = substr($NrI,6,3);
$n1 = (integer) $NrI[6];
$n2 = (integer) $NrI[7];
$n3 = (integer) $NrI[8];
$Rez = $Rez . $sp . $nc[$n1] . $ub[$n1];
$Rez = ($n2 == 1)?$Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2]:
                $Rez . ($n2==6?$ex1:$nc[$n2]) . $ua[$n2] . $lg2[$n2] . ($Gr=="001"||$Gr=="002"?$nc[$n3]:$nd[$n3]);
$Rez = ($Gr == "000")?$Rez:(($Gr == "001")?($Rez . $uc[1]):($Rez . $uc[2]));

 // grupul 1 (unitati)
$Gr = substr($NrI,9,3);
$n1 = (integer) $NrI[9];
$n2 = (integer) $NrI[10];
$n3 = (integer) $NrI[11];
$Rez = $Rez . $sp . $nc[$n1] . $ub[$n1];
$Rez = ($n2 == 1)?($Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2].$mon[2]):($Rez . ($n2==6?$ex1:$nc[$n2]). $ua[$n2] .
                ($n3>0?$lg2[$n2]:'') . ($NrI=="000000000001"?($nb[$n3] .$mon[1]):($na[$n3]). $mon[2]));

if ((integer) $NrI == 0) {$Rez = ""; }

// banii
if ((integer) $Zec>0) 
{
 $n2 = (integer) substr($Zec,0,1);
 $n3 = (integer) substr($Zec,1,1);
 $Rez .= ' și ';
 $lg22 = ($n3=='0'?'':$lg2[$n2]);
 $Rez = ($n2 == 1)?($Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2].$ban[2]):
                ($Rez . ($n2==6?$ex1:$nc[$n2]) . $ua[$n2] . $lg22 . ($Zec=="01"?($nb[$n3] .$ban[1]):($na[$n3]). $ban[2]));
}
return $Rez;
}