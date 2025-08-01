<?php
$strKeywords="Politica de cookies";
$strDescription="politica de cookies a site-ului consultanta-haccp.ro";
$strPageTitle="Politica de cookies";
$url='cookies.php';
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
<p>Pentru a asigura buna funcționare a acestui site, plasăm în computerul dumneavoastră mici fișiere cu date, cunoscute sub numele de cookie-uri. Majoritatea site-urilor fac acest lucru.</p>
<h2>Ce se înțelege prin cookie-uri?</h2>
<p>Cookie-ul este un fișier text de mici dimensiuni pe care un site îl salvează în calculatorul sau dispozitivul dumneavoastră mobil atunci când îl vizitați. Datorită cookie-urilor, site-ul reține, pe o perioadă de timp, acțiunile și preferințele dumneavoastră (login, limbă, dimensiunea caracterelor și alte preferințe de afișare). Astfel nu mai trebuie să le reintroduceți ori de câte ori reveniți la site sau navigați de pe o pagină pe alta.</p>
<h2>Cum folosim cookie-urile?</h2>
<p>Site-ul nostru folosește <strong>doar cookie-uri de sesiune obligatorii pentru funcționarea acestuia</strong>. Un cookie de sesiune este șters atunci când închideți browserul sau după o perioadă scurtă de timp. Cookie-uri persistente permit site-ul nostru să-și amintească preferințele și setările dvs. atunci când vizitați site-ul nostru în viitor. Cookie-uri persistente expiră automat după o anumită perioadă de timp prestabilită, <strong>respectiv 3 luni</strong>. Cookie-urile sunt utilizate în următoarele moduri pe site-ul nostru:</p>
<ul class="tiny">
<li>Folosim cookies de sesiune pentru a memora opțiunea dumneavoastră privind cookie-urile :).</li>
<li>Folosim cookies de sesiune pentru a memora opțiunea dumneavoastră privind limba site-ului :).</li>
<li>Folosim cookies de sesiune atunci când sunt necesare pentru diversele funcționalități ale site-ului.</li>
</ul>
<p><strong>Nu folosim alte cookieuri, nici nu există instalate scripturi terțe care să colecteze sau să genereze cookieu-uri.</strong></p>
<h3>Cum puteți controla cookie-urile?</h3>
<p>Continuând să utilizați site-ul nostru, fără a schimba setările, sunteți de acord cu utilizarea cookie-urilor așa cum este descris mai sus.</p>
<hr />
</div>
</div>
<?php
include 'bottom.php';
?>