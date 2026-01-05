<?php
$strKeywords="Politica de cookies";
$strDescription="politica de cookies a site-ului consultanta-haccp.ro";
$strPageTitle="Politica de cookies";
$pageurl='cookies.php';
include 'settings.php';
include 'classes/common.php';
include 'header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
?>
<div class="row">
    <div class="large-12 columns" role="content">
        <h1><?php echo $strPageTitle?></span></h1>
        <h2>Cookie-urile</h2>
        <p>Pentru a asigura buna funcționare a acestui site, plasăm în computerul dumneavoastră mici fișiere cu date,
            cunoscute sub numele de cookie-uri. Majoritatea site-urilor fac acest lucru.</p>
        <h2>Ce se înțelege prin cookie-uri?</h2>
        <p>Cookie-ul este un fișier text de mici dimensiuni pe care un site îl salvează în calculatorul sau dispozitivul
            dumneavoastră mobil atunci când îl vizitați. Datorită cookie-urilor, site-ul reține, pe o perioadă de timp,
            acțiunile și preferințele dumneavoastră (login, limbă, dimensiunea caracterelor și alte preferințe de
            afișare). Astfel nu mai trebuie să le reintroduceți ori de câte ori reveniți la site sau navigați de pe o
            pagină pe alta.</p>
        <h2>Cum folosim cookie-urile?</h2>
        <p>Site-ul nostru folosește <strong>doar cookie-uri de sesiune obligatorii pentru funcționarea
                acestuia</strong>. Un cookie de sesiune este șters atunci când închideți browserul sau după o perioadă
            scurtă de timp. Cookie-uri persistente permit site-ul nostru să-și amintească preferințele și setările dumneavoastră
            atunci când vizitați site-ul nostru în viitor. Cookie-uri persistente expiră automat după o anumită perioadă
            de timp prestabilită, <strong>respectiv 3 luni</strong>. Cookie-urile sunt utilizate în următoarele moduri
            pe site-ul nostru:</p>
        <ul>
            <li class="tiny">Folosim cookies persistente pentru a memora opțiunea dumneavoastră privind cookie-urile :), 
                astfel încât să nu vă afișăm banerul de informare de fiecare dată când reveniți pe site.</li>
            <li class="tiny">Folosim cookies de sesiune pentru a memora opțiunea dumneavoastră privind limba site-ului :).</li>
            <li class="tiny">Folosim cookies de sesiune atunci când sunt necesare pentru diversele funcționalități ale site-ului.</li>
        </ul>
        <p><strong>Nu folosim cookie-uri terțe, nici nu există instalate scripturi care să colecteze sau să genereze
                cookie-uri.</strong></p>
        <h3>Cum puteți controla cookie-urile?</h3>
            <p>Puteți să vă retrageți în orice moment acordul ștergând cookie-urile din browser.</p>
            <p>Aceste setări se găsesc de obicei în meniul 'opțiuni' sau 'preferințe' din browser. Pentru a înțelege aceste setări, 
                ar putea fi utile următoarele linkuri (sau accesați opțiunea 'Ajutor' din browser pentru mai multe detalii):</p>
        <ul>
<li class="tiny"><a href="https://support.microsoft.com/en-gb/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank"><span>Setările pentru cookie din Internet Explorer</span></a>&nbsp;</li>
<li class="tiny"><a href="http://support.mozilla.org/en-US/kb/cookies-information-websites-store-on-your-computer?redirectlocale=en-US&amp;redirectslug=Cookies" target="_blank"><span>Setările pentru cookie din Firefox</span></a>&nbsp;</li>
<li class="tiny"><a href="http://support.google.com/chrome/bin/answer.py?hl=en&amp;answer=95647" target="_blank"><span>Setările pentru cookie din Chrome</span></a>&nbsp;</li>
<li class="tiny"><a href="http://support.apple.com/kb/HT1677" target="_blank"><span>Setările pentru cookie din Safari</span></a>&nbsp;</li>
</ul>
<p>Schimbarea setărillor din browser pentru a respinge cookie-urile poate influența modul în care ne 
    accesați site-ul web și este posibil să vă fie respins accesul la anumite secțiuni ale acestuia. 
    Puteți schimba în orice moment setările browserului.</p>
    <p>Continuând să utilizați site-ul nostru, fără a schimba setările, sunteți de acord cu utilizarea cookie-urilor
            așa cum este descris mai sus.</p>
        <hr />
    </div>
</div>
<?php
include 'bottom.php';
?>