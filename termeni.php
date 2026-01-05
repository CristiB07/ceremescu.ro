<?php
//update 08.01.2025
include 'settings.php';
include 'classes/common.php';
$strKeywords="Termeni și condiții de utilizare";
$strDescription="termeni și condiții de utilizare a site-ului ". $strSiteName;
$strPageTitle="Termeni și condiții";
$pageurl='termeni.php';
include 'header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h1><?php echo $strPageTitle?></span></h1>
        <h2>Date privind operatorul economic</h2>
        <p>Acest site este gestionat de <?php echo $strSiteOwnerData?>.</p>
        <p>Mulțumim pentru interesul față de compania și site-ul nostru online.</p>
        <h2>Acord de utilizare</h2>
        <p>Prin utilizarea acestui site, vă exprimați acordul cu aceste condiții de utilizare. 
            Vă rugăm să citiți și <a href="<?php echo $strSiteURL ?>/politica.php">Politica noastră de confidențialitate</a> 
            și <a href="<?php echo $strSiteURL ?>/cookies.php">Politica privind utilizarea modulelor cookie</a>, 
            înainte de a naviga pe site. Dacă nu sunteți de acord cu acești Termeni sau cu politicile indicate mai sus, 
            vă rugăm să nu utilizați site-ul. </p>
            <?php if ($shop==1 || $elearning==1)
{?>
<h2>Date cu caracter personal</h2>
<p>
Conform cerințelor Regulamentului European privind Protecția Datelor cu Caracter Personal (The General Data Protection Regulation (GDPR) 
(EU) 2016/679), Consaltis Consultanță și Audit are obligația de a administra în condiții de siguranță și numai pentru scopurile specificate 
datele personale pe care ni le furnizați despre dumneavoastră, un membru al familiei dumneavoastră ori o altă persoană. 
</p>
        <p>Colectăm și prelucrăm datele dumneavoastră cu caracter personal în conformitate cu <a href="<?php echo $strSiteURL ?>/politica.php">Politica noastră de confidențialitate</a>. 
            Prin utilizarea acestui site, sunteți de acord cu colectarea și prelucrarea datelor dumneavoastră în conformitate cu această politică.</p>
<p>Declarați, totodată, că toate datele personale și informațiile transmise către noi sunt corecte. 
    În măsura în care datele nu vă aparțin, declarați că ați obținut acordul scris și prealabil al 
    persoanei vizate ale cărei date le transmiteți către noi sau declarați că transmiteți datele în baza altui 
    temei legal conform Regulamentului (UE) nr. 679/2016.</p>
    <p>Acest site colectează automat anumite informații, precum adresa IP, detalii privind browserul sau dispozitivul utilizat prin logurile serverului. 
        Aceste informații sunt utilizate pentru a îmbunătăți funcționarea site-ului și experiența utilizatorilor precum și pentru analiza unor eventuale incidente de securitate.</p>
        <p>Nu folosim decât module cookie persistente obligatorii pentru funcționarea acestui site. Dacă doriți să aflați mai multe informații, vă recomandăm să accesați și să parcurgeți <a href="<?php echo $strSiteURL ?>/cookies.php">Politica noastră privind utilizarea modulelor cookie</a>.</p>
    <h2>Produse și servicii</h2>
            <h4><?php echo $strSiteOwner?> oferă produsele și servicii sale exclusiv societăților comerciale datorită specificului acestora. 
                Prin urmare, prin plasarea unei comenzi pe acest site, declarați că acționați în numele și pentru o societate comercială 
                și nu în calitate de consumator conform definiției din Codul Civil Român.
        </h4>
            <?php if ($elearning==1)
{?>
        <h2>Cursuri online</h2>
        <p>Accesul la cursurile online oferite pe acest site este permis numai persoanelor care au achiziționat în mod legal accesul la acestea. 
            Orice distribuire, partajare sau transmitere a datelor de autentificare către terțe părți este strict interzisă și poate duce la suspendarea sau anularea accesului la cursuri fără nicio notificare prealabilă.</p>
        <p>În cazul cursurilor este posibilă și participarea persoanelor fizice, indiferent dacă participarea la curs este suportată de angajator sau în nume propriu.</p>
        <p>Materialele didactice puse la dispoziție în cadrul cursurilor online sunt protejate prin drepturi de autor și nu pot fi copiate, distribuite sau utilizate 
            în alte scopuri fără acordul scris al <?php echo $strSiteOwner?>.</p>
        <p>Ne rezervăm dreptul de a modifica conținutul cursurilor online, programul sau condițiile de acces în orice moment, cu notificarea prealabilă a utilizatorilor afectați.</p>
<?php }?>
    <?php if ($shop==1)
{?>
     
        <h2>Termeni și condiții de vânzare</h2>
        <p>Toate materialele vândute pe acest site sunt vândute „aşa cum sunt”, fiind produse „generice” şi ele trebuie
            adaptate situaţiei din organizaţia dumneavoastră de către o persoană competentă. <?php echo $strSiteOwner?>
            garantează conformarea acestora cu standardele respective, dar nu îşi poate asuma responsabilitatea pentru
            diferenţele între situaţiile descrise în document şi situaţia reală din organizaţia dumneavoastră sau pentru
            modificările ulterioare ale documentelor. </p>
        <p>Din acest motiv, <?php echo $strSiteOwner?> nu poate fi făcută responsabilă pentru orice daune sau pierderi
            cauzate de utilizarea produselor achiziţionate de pe acest site.</p>
        <p>La cerere, contra unei taxe suplimentare, <?php echo $strSiteOwner?> asigură personalizarea documentelor.</p>
        <p>Toate informațiile folosite pentru descrierea bunurilor disponibile pe Site (imagini statice, dinamice,
            prezentari multimedia, etc.) nu reprezintă o obligație contractuală din partea vânzătorului, acestea fiind
            utilizate exclusiv cu titlu de prezentare.</p>
        <p>Comanda înseamnă acțiunea de a solicita produse folosind butonul „Trimite Comanda” din coșul de cumpărături.
            Comanda este considerată contract între părți. Desfacerea unui contract se face prin notificarea firmei de
            catre client în temeiul termenilor legislației în vigoare sau de către <?php echo $strSiteOwner?> cu
            notificarea clientului, în cazul în care comanda nu poate fi onorata din motive intemeiate, fapt care
            implică restituirea sumei în cazul comenzilor pre-platite.</p>
        <p>Comenzile plasate cu succes pe acest site sunt urmate de trimiterea automată a unei notificări pe e-mail-ul
            înscris în formularul de comandă. Din motive obiective, <?php echo $strSiteOwner?> își rezervă dreptul de a
            notifica exclusiv telefonic clientul în cazul în care este necesară modificarea numărului de produse din
            comandă. Mai departe, clientul va decide dacă acceptă modificarea sau anulează comanda, cu restituirea
            integrală a sumei achitate acolo unde este cazul.</p>
        <p>Prin plasarea unei comenzi, clientul este de acord cu termenii și condițiile acestea și se consideră că este
            la curent cu forma de confirmare și livrare a comenzilor, cât și cu termenii și condițiile.</p>
        <h2>Returnarea produselor</h2>
        <p>Materialele vândute pe acest site intră sub incidenţa articolului 18, aliniat m) din cadrul Ordonanţa de
            urgenţă nr. 34/2014 privind drepturile consumatorilor în cadrul contractelor încheiate cu profesioniştii și
            completările sale, fiind astfel încadrabile în grupa produselor care nu pot fi returnate prin natura lor.
            Prin acceptarea acestor termeni şi condiţii la validarea comenzii, cumpărătorul este de acord să nu solicite
            returnarea produselor.</p>
            <?php }?>
<h2>Reguli de utilizare</h2>
<p>Prin accesarea, vizitarea, plasarea unei comenzi sau desfășurarea oricărei alte activități pe site-ul nostru, 
    aveți obligația să respectați următoarele reguli:</p>
<ul>
            <li class="tiny"> Acest site va fi utilizat exclusiv pentru efectuarea comenzilor legitime sau pentru informare;</li>
            <li class="tiny"> Nu veți efectua nicio comandă falsă sau frauduloasă, în caz contrar ne rezervăm dreptul de a anula comanda și informa autoritățile competente sau a ne adresa justiției pentru recuperarea oricăror prejudicii cauzate;</li>
            <li class="tiny"> Veți furniza informații reale, exacte, complete și actualizate;</li>
            <li class="tiny"> Veți respecta drepturile de proprietate intelectuală cu privire la orice element regăsit pe acest site. </li>
            <li class="tiny"> Nu veți desfășura niciun fel de acțiune care ar putea aduce orice fel de prejudiciu site-ului nostru, în caz contrar ne rezervăm dreptul de a ne adresa justiției pentru recuperarea oricăror prejudicii cauzate. </li>
</ul>
<p>
        Pentru a putea plasa în mod legal o comandă pe site-ul nostru trebuie să aveți peste 18 ani și capacitate deplină de exercițiu, 
        să fiți de acord cu prezentul Contract și să ne furnizați informații de  identitate și de contact reale, complete și actualizate.
    </p>
    <p>În cazul în care descoperim că am primit datele de identitate sau de contact ale unei persoane care nu îndeplinește condițiile de mai sus, 
        ne rezervăm dreptul de a anula comanda respectivă și vom șterge imediat toate datele respective.</p>
<p>Ne rezervăm dreptul de a bloca accesul oricărui utilizator care încalcă regulile de mai sus, 
    de a anula comenzile, de a sesiza autoritățile competente pentru tragerea la răspundere administrativă/penală a oricăror fapte antisociale 
    și de a ne adresa justiției pentru recuperarea în integralitate a oricăror prejudiciilor cauzate, prezente sau viitoare,
     inclusive beneficiile nerealizate și cheltuielile de judecată (inclusiv onorariile avocaților).</p>
<h2>Contract</h2>
<h3>Data încheierii contractului</h3> 
<p>Contractul dintre dumneavoastră și noi se încheie în momentul în care comanda dumneavoastră va fi acceptată în mod expres de noi și veți primi, 
    în acest sens, un e-mail în care vom confirma livrarea.</p> 
<h3>Prețuri și plăți</h3>
<p>Toate prețurile afișate pe site sunt exprimate în lei, includ TVA și alte taxe aplicabile, 
    dar nu includ costurile de livrare care vor fi afișate separat în momentul plasării comenzii, atunci când este cazul. 
    Ne rezervăm dreptulul de a modifica prețurile afișate pe site în orice moment, 
    dar aceste modificări nu vor afecta comenzile deja plasate și acceptate de noi.</p>
    <p>În măsura în care nu vom accepta comanda, dar dumneavoastră v-au fost retrase sume de bani, vom proceda la rambursarea acestor sume
         în cel mai scurt timp. </p>
<h3>Acceptarea comenzii</h3>
<p>Ne vom strădui să procesăm și să livrăm comenzile cât mai rapid posibil. 
    Cu toate acestea, toate comenzile sunt supuse disponibilității produselor și confirmării prețului comenzii. 
    <?php if ($elearning==1) {
echo "În cazul cursurilor online, accesul la acestea va fi acordat după confirmarea plății. În cazul cursurilor care necesită prezența fizică,
data și locul desfășurării vor fi comunicate prin e-mail după confirmarea grupei. Pentru aceste cursuri, ne rezervăm dreptul de a anula înscrierea 
în cazul în care nu se întrunește numărul minim de participanți. <strong>De aceea, vă rugăm să efectuați plata doar după confirmarea noastră.</strong>";
    }
     ?></p>
<p>
 Ne rezervăm dreptul de a decide, în mod unilateral și fără a preciza motivul, încheierea sau nu a unui contract de vânzare. 
 Nu vom avea nicio răspundere față de dumneavoastră în situația în care refuzăm să dăm curs unei comenzi. 
 Dreptul de proprietate asupra produselor se va transfera către dumneavoastră numai după ce ați realizat plata tuturor sumelor datorate pentru produse, inclusiv a costurilor de livrare. 
</p>
<h3>Anularea comenzii</h3>
<p>În cazul în care doriți să anulați o comandă, vă rugăm să ne contactați cât mai curând posibil, înainte de livrarea produselor<?php if ($elearning==1) { echo " sau accesul la cursurile online"; }?>.
    Dacă comanda nu a fost încă procesată sau livrată, aceasta va fi anulată și eventualele sume încasate returnate.</p>
    <p>În cazul în care produsele au fost deja livrate<?php if ($elearning==1) { echo " sau accesul la cursurile online a fost acordat"; }?>,  nu se mai poate anula comanda
        și sumele plătite nu vor fi returnate, cu excepția cazului în care se aplică dreptul de retragere conform legislației în vigoare.</p>
<?php
}?>
        <h2>Proprietatea Conținutului</h2>
        <p>Site-ul și tot ceea ce cuprinde acesta, incluzând fără limitare toate textele și imaginile („Conținut”) sunt
            în proprietatea și sub dreptul de autor (copyright) al <?php echo $strSiteOwner?> sau al altora, după
            indicații, cu toate drepturile rezervate, cu excepția cazului în care nu este altfel specificat. Imaginile
            care ilustrează acest site sunt preluate de pe <a href="https://pixabay.com/service/license/">Pixabay</a> sau <a href="https://www.pexels.com/ro-ro/license/">Pexels</a> și
            se supun licenței de utilizare a acestora. Este strict interzisă utilizarea oricărui conținut fără
            permisiunea proprietarului drepturilor de utilizare.</p>
        <h2>Lipsa garanțiilor</h2>
        <p>ÎNTREG CONȚINUTUL ACESTUI SITE POATE FI MODIFICAT ȘI VĂ ESTE OFERIT „CA ATARE” FĂRĂ A SE OFERI NICI O
            GARANȚIE DE NICI UN FEL, FIE ACEASTA EXPRESĂ SAU IMPLICITĂ.</p>
        <h2>Link-uri pe site-urile unei terțe părți</h2>
        <p>Site-ul poate conține link-uri către alte site-uri aflate în proprietatea sau operate de alte părți decât
            <?php echo $strSiteOwner?>. Astfel de link-uri vă sunt furnizate pentru a le folosi numai dacă veți dori
            aceasta. <?php echo $strSiteOwner?> nu controlează, și nu este răspunzătoare pentru conținutul și condițiile
            de confidențialitate sau securitate și de funcționalitatea acestor site-uri. Fără a se limita la cele mai
            sus menționate, <?php echo $strSiteOwner?> își declina în mod special orice răspundere dacă aceste site-uri:
        </p>
        <ul>
            <li class="tiny">Încalcă drepturile de proprietate intelectuală ale unei terțe părți;</li>
            <li class="tiny">Sunt inexacte, incomplete sau conțin informații înșelătoare;</li>
            <li class="tiny">Nu au caracter comercial sau nu răspund îndeplinirii unui anumit scop;</li>
            <li class="tiny">Nu oferă o securitate adecvata;</li>
            <li class="tiny">Conțin viruși sau alte elemente cu caracter distructiv;</li>
            <li class="tiny">Sunt licențioase sau calomnioase.</li>
        </ul>
        <p>De asemenea, <?php echo $strSiteOwner?> nu autorizează conținutul sau orice alte produse și servicii
            promovate pe astfel de site-uri. Dacă intrați printr-un link pe astfel de site-uri sau pe acest site, va
            asumați personal riscul, fără a exista în acest sens permisiunea <?php echo $strSiteOwner?>.</p>
        <h2>Revizuiri ale acestor Condiții de Utilizare</h2>
        <p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste
            Condiții de Utilizare prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de
            revizuiri și de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua
            la cunoștință Condițiile de Utilizare actualizate.</p>
        <p>Această pagină a fost revizuită la data de 19.12.2025</p>
        <h2>Legislație aplicabilă și jurisdicție</h2>
        <p>Acești Termeni și Condiții de Utilizare și utilizarea acestui site sunt guvernate de legile din România.
            Instanțele competente din România vor avea jurisdicție exclusivă asupra oricăror și tuturor disputelor ce
            vor apărea din sau se vor referi la sau vor fi în legătură cu prevederile Termenilor și Condițiilor de
            Utilizare și/sau Conținutul site-ului sau în cazul disputelor în care acești Termeni și Condiții de
            Utilizare și/sau acest site vor fi considerate fapte relevante pentru astfel de dispute. Instanțele relevante
        pentru dispute sunt cele care sunt aplicabile în sfera domiciliului social al companiei, respectiv Sectorul 3 al Municipiului București.</p>
        <hr />
    </div>
</div>
<?php
include 'bottom.php';
?>