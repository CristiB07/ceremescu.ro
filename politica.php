<?php
//update 08.01.2025
include 'settings.php';
include 'classes/common.php';
$strKeywords="Politica de confidențialitate și protecția datelor cu caracter personal pentru site-ul ". $strSiteName;
$strDescription="Politica de confidențialitate și protecția datelor cu caracter personal pentru site-ul ". $strSiteName;
$strPageTitle="Politica de confidențialitate";
$pageurl='politicadeconfidentialitate.php';
include 'header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
?>
<div class="grid-x grid-margin-x">
    <div class="large-12 cell">
        <h1><?php echo $strPageTitle?></span></h1>
             <h2>Date personale</h2>
        <?php if ($shop==1)
{?>
        <h3>Date privind operatorul de date</h3>
        <p><?php echo $strSiteOwnerData?>.</p>
         <h3>Date cu caracter personal</h3>
        <p>Colectăm și prelucrăm datele dumneavoastră cu caracter personal în funcție de situație, astfel:</p>
<ul>
    <li class="tiny"><strong>Date de contact:</strong> nume, prenume, adresă email, număr de telefon, adresă poștală 
        - pentru a vă putea contacta și livra produsele și serviciile comandate sau a face o ofertă personalizată;</li>
    <li class="tiny"><strong>Date de facturare:</strong> nume, prenume, adresă poștală, adresă mail, număr de telefon, pentru emiterea și comunicarea facturii fiscale;</li>
    <li class="tiny"><strong>Date colectate automat:</strong> adresa IP, tipul browserului, paginile vizitate, timpul petrecut pe site 
        - pentru a îmbunătăți funcționarea site-ului și experiența utilizatorilor precum și pentru analiza unor eventuale 
        incidente de securitate.</li>
    <li class="tiny"><strong>Date transmise de dumneavoastră ca aplicare în vederea angajării sau pentru intership, fie spontan, fie ca urmare a unui anunț.</strong></li>
</ul>
<h3>Temeiul legal</h3>
<p>Prelucrarea datelor cu caracter personal se face pe baza următoarelor temeiuri legale:</p>
<ul>
    <li class="tiny"><strong>Executarea unui contract:</strong> pentru a vă putea livra produsele și serviciile comandate sau ofertele solicitate;</li>
    <li class="tiny"><strong>Executarea unui contract:</strong> în vederea angajării sau a intership-ului;</li>
    <li class="tiny"><strong>Consimțământul dumneavoastră:</strong> pentru trimiterea de newslettere și/sau alerte periodice, prin folosirea poștei electronice;</li>
    <li class="tiny"><strong>Interesul legitim:</strong> pentru a îmbunătăți funcționarea site-ului și experiența utilizatorilor precum 
        și pentru analiza unor eventuale incidente de securitate.</li>
</ul>
<h3>Durata de păstrare a datelor</h3>
<p>Păstrarea datelor cu caracter personal se face pe o perioadă limitată, astfel:</p>
<ul>
    <li class="tiny"><strong>Datele de contact și de facturare</strong> sunt păstrate pe durata necesară pentru emiterea facturii și livrarea produselor,
        precum și pe perioada legală de păstrare a documentelor contabile, respectiv 5 ani de la data depunerii bilanțului pentru anul respectiv;</li>
    <li class="tiny"><strong>Datele colectate automat</strong> prin logurile serverului sunt păstrate pentru o perioadă de maxim 6 luni, cu excepția cazurilor
        în care sunt necesare pentru analiza unor incidente de securitate și trebuie reținute ca probe;</li>
    <li class="tiny"><strong>Datele transmise de dumneavoastră ca aplicare în vederea angajării sau pentru intership, fie spontan, 
        fie ca urmare a unui anunț</strong> sunt păstrate între 6 luni și un an, în funcție de potrivirea profilului dumneavoastră cu cerințele postului.</li>
</ul>
      <h3>Politica de confidențialitate</h3>
        <p><?php echo $strSiteOwner?> apreciază încrederea pe care o manifestați navigând pe site-ul nostru, scriindu-ne
            sau făcând comenzi on-line, de aceea dorim să vă asigurăm că nu vom furniza, vinde sau ceda nici unei alte
            părți baza de date referitoare la datele dumneavoastră personale.</p>
        <p>Informațiile cu caracter personal ale cumpărătorului pot fi furnizate către Parchetul General, Poliție,
            instanțele judecătorești și altor organe abilitate ale statului, în baza și în limitele prevederilor legale
            și ca urmare a unor cereri expres formulate.</p>
        <p>Conform cerințelor Regulamentului European privind Protecția Datelor cu Caracter Personal (The General Data
            Protection Regulation (GDPR) (EU) 2016/679), <?php echo $strSiteOwner?> are obligația de a administra în
            condiții de siguranță și numai pentru scopurile specificate datele personale pe care ni le furnizați despre
            dumneavoastră, un membru al familiei dumneavoastră ori o altă persoană.</p>
        <p>Scopul colectării datelor este informarea clienților privind situația contului lor inclusiv
            validarea, procesarea și facturarea comenzilor, rezolvarea anulărilor sau a problemelor de orice natură
            referitoare la o comandă sau produsele și/sau serviciile achiziționate.</p>
        <p>Opțional, numai cu acordul dumneavoastră, pentru trimiterea de newslettere și/sau alerte periodice, prin
            folosirea poștei electronice. Acest acord poate fi revocat oricând, în subsolului emailului fiind un link de
            dezabonare.</p>
        <p>În cazul în care livrarea produselor comandate se va face de o altă entitate juridică decât
            <?php echo $strSiteOwner?>, datele colectate vor fi comunicate și acelei entități juridice în scopurile
            menționate mai sus.</p>
        <p>Sunteți obligat(ă) să furnizați datele solicitate, acestea fiind necesare pentru crearea facturii pentru
            produsele comandate și, ulterior, livrarea produselor. Refuzul dumneavoastră determină anularea comenzii.
            Informațiile înregistrate sunt destinate utilizării de către operator în scopul livrării serviciilor și sunt
            comunicate numai de către dumneavoastră.</p>
        <p>În cazul în care este necesar, vom externaliza către alte companii anumite sarcini contribuind la serviciile
            noastre în numele nostru, în cadrul acordurilor de prelucrare a datelor. Este posibil, de exemplu, să
            furnizăm date cu caracter personal către agenți, contractori sau parteneri autorizați pentru găzduirea
            bazelor noastre de date, a site-ului web, pentru serviciile de prelucrare a datelor sau pentru a vă trimite
            informațiile pe care ni le-ați solicitat.</p>
        <p>Vom partaja sau permite accesul la astfel de informații NUMAI furnizorilor externi de servicii după cum este
            necesar pentru furnizarea serviciilor noastre. Aceste informații nu pot fi utilizate de către furnizorii
            externi de servicii pentru orice alte scopuri și sunt obligați prin contract să respecte confidențialitatea
            datelor personale încredințate.</p>
        <p>Toate transferurile de date cu caracter personal către terțe părți vor fi efectuate cu notificarea
            dumneavoastră anterioară și, după caz, cu acordul dumneavoastră. Orice transferuri de date cu caracter
            personal în alte țări decât cele pentru care a fost luată o decizie de adecvare în ceea ce privește nivelul
            de protecție a datelor de către Comisia Europeană, după cum este prezentat la adresa
            http://ec.europa.eu/justice/data-protection/international-transfers/adequacy/index_en.htm, au loc pe baza
            unor acorduri contractuale ce utilizează clauzele contractuale standard adoptate de Comisia Europeană sau
            alte garanții corespunzătoare, în conformitate cu legea în vigoare.</p>
        <p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest site, precum întrebări, comentarii,
            sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de
            proprietate intelectuală determinate.
        <h3>Candidaturi</h3>
        <p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție
            vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul
            dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei
            noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de
            colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca
            datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a
            restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este
            procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
        <h3>Drepturile utilizatorului</h3>
        <p>Beneficiați de dreptul de acces, de intervenție asupra datelor, dreptul de a nu fi supus unei decizii
            individuale și dreptul de a vă adresa justiției. Totodată, aveți dreptul să vă opuneți prelucrării datelor
            personale care vă privesc și să solicitați ștergerea datelor. </p>
        <p>Dacă unele din datele despre dumneavoastră sunt incorecte, vă rugăm să ne informați cât mai curând posibil.
            Pentru exercitarea acestor drepturi, vă puteți adresa cu o cerere scrisă, datată și semnată la Departamentul
            relații cu Clienții, la adresa de e-mail office at consaltis . ro, sau prin poștă la adresa din partea de
            jos a site-ului. De asemenea, vă este recunoscut dreptul de a vă adresa justiției.</p>
        <p>Aveți dreptul de a depune plângere în fața unei autorități de supraveghere. În Romania puteți depune plângere
            în fața Autorității Naționale de Supraveghere a Datelor cu Caracter Personal cu sediul în B-dul G-ral.
            Gheorghe Magheru 28-30, sector 1, cod poștal 010336, București, email anspdcp@dataprotection.ro, telefon
            +40.318.059.211, +40.318.059.212, website www.dataprotection.ro.</p>
        <p>Aveți dreptul de a vă adresa justiției pentru apărarea oricăror drepturi garantate de lege, care v-au fost
            încălcate.
        <p>Dreptul de intervenție asupra datelor se aplică pentru:</p>
        <ul>
            <li class="tiny">rectificarea, actualizarea, blocarea sau ștergerea datelor a căror prelucrare nu este
                conformă legii, în special a datelor incomplete sau inexacte;</li>
            <li class="tiny">transformarea în date anonime a datelor a căror prelucrare nu este conformă legii;</li>
            <li class="tiny">notificarea către terții cărora le-au fost dezvăluite datele, dacă această notificare nu se
                dovedește imposibilă sau nu presupune un efort disproporționat față de interesul legitim care ar putea
                fi lezat.</li>
        </ul>
        <p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii,
            sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de
            proprietate intelectuală determinate.</p>
        <h3>Jurnale de acces (loguri)</h3>
        <p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa
            (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură
            tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor
            incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual
            fără a fi citite, de regulă la 3-4 luni.</p>
        <h3>Rețele sociale</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe
            rețelele sociale vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate
            asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.
            <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de
            exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba
            de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de
            contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul
            în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
        <h3>Rețele de publicitate</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt
            colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate.
            Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu
            își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele
            dumneavoastră.</p>
        <h3>Revizuiri ale politicii</h3>
        <p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste
            politici prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de revizuiri și
            de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua la cunoștință
            politica de confidențialitate actualizată.</p>
       
        <?php
}
elseif ($elearning==1)
{?>
        <h3>Date privind operatorul de date</h3>
        <p><?php echo $strSiteOwnerData?>.</p>
             <h3>Date cu caracter personal</h3>
        <p>Colectăm și prelucrăm datele dumneavoastră cu caracter personal în funcție de situație, astfel:</p>
<ul>
    <li class="tiny">Date de contact: nume, prenume, adresă email, număr de telefon, adresă poștală 
        - pentru a vă putea contacta și livra produsele și serviciile comandate sau a face o ofertă personalizată;</li>
    <li class="tiny">Date de facturare: Nume, prenume, adresă poștală, adresă mail, număr de telefon, pentru emiterea și comunicarea facturii fiscale;</li>
    <li class="tiny">Date colectate automat: adresa IP, tipul browserului, paginile vizitate, timpul petrecut pe site 
        - pentru a îmbunătăți funcționarea site-ului și experiența utilizatorilor precum și pentru analiza unor eventuale 
        incidente de securitate.</li>
    <li class="tiny">Date transmise de dumneavoastră ca aplicare în vederea angajării sau pentru intership, fie spontan, fie ca urmare a unui anunț.</li>
    <li class="tiny">Date privind participarea la cursuri: datele de contact, datele de facturare, notele obținute la teste și examene, 
        pentru a vă putea oferi acces la cursurile achiziționate, a vă monitoriza progresul și a vă elibera diploma de participare. 
        În cazul în care pentru anumite categorii de cursuri sunt necesare date suplimentare conform cerințelor autorităților (de ex. CNP)
    vi se va solicita în mod explicit consimțământul pentru procesarea acestora.</li>
</ul>
<h3>Temeiul legal</h3>
<p>Prelucrarea datelor cu caracter personal se face pe baza următoarelor temeiuri legale:</p>
<ul>
    <li class="tiny">Executarea unui contract: pentru a vă putea livra produsele și serviciile comandate sau ofertele solicitate;</li>
    <li class="tiny">Cerință legală: pentru a putea emite diplomele de participare la cursurile acreditate;</li>
    <li class="tiny">Executarea unui contract: în vederea angajării sau a intership-ului;</li>
    <li class="tiny">Consimțământul dumneavoastră: pentru trimiterea de Newslettere și/sau alerte periodice, prin folosirea poștei electronice;</li>
    <li class="tiny">Interesul legitim: pentru a îmbunătăți funcționarea site-ului și experiența utilizatorilor precum 
        și pentru analiza unor eventuale incidente de securitate.</li>
</ul>
<h3>Durata de păstrare a datelor</h3>
<p>Păstrarea datelor cu caracter personal se face pe o perioadă limitată, astfel:</p>
<ul>
    <li class="tiny">Datele de contact și de facturare sunt păstrate pe durata necesară pentru emiterea facturii și livrarea produselor,
        precum și pe perioada legală de păstrare a documentelor contabile, respectiv 5 ani de la data depunerii bilanțului pentru anul respectiv;</li>
    <li class="tiny">Datele privind participarea la cursuri sunt păstrate pe durata necesară pentru livrarea cursului, 
        eliberarea diplomei de participare și conform cerințelor legale, respectiv 5 ani pentru documentele de curs (diploma de participare) 
        și de 1 an pentru restul datelor;</li>
    <li class="tiny">Datele colectate automat prin logurile serverului sunt păstrate pentru o perioadă de maxim 6 luni, cu excepția cazurilor
        în care sunt necesare pentru analiza unor incidente de securitate și trebuie reținute ca probe;</li>
    <li class="tiny">Datele transmise de dumneavoastră ca aplicare în vederea angajării sau pentru intership, fie spontan, 
        fie ca urmare a unui anunț sunt păstrate între 6 luni și un an, în funcție de potrivirea profilului dumneavoastră cu cerințele postului.</li>
</ul>
        <h3>Politica de confidențialitate</h3>
        <p><?php echo $strSiteOwner?> apreciază încrederea pe care o manifestați navigând pe site-ul nostru, scriindu-ne
            sau făcând comenzi on-line, de aceea dorim să vă asigurăm că nu vom furniza, vinde sau ceda nici unei alte
            părți baza de date referitoare la datele dumneavoastră personale.</p>
        <p>Informațiile cu caracter personal ale Cumpărătorului pot fi furnizate și către Parchetul General, Poliție,
            instanțele judecătorești și altor organe abilitate ale statului, în baza și în limitele prevederilor legale
            și ca urmare a unor cereri expres formulate.</p>
        <p>Conform cerințelor Regulamentului European privind Protecția Datelor cu Caracter Personal (The General Data
            Protection Regulation (GDPR) (EU) 2016/679), <?php echo $strSiteOwner?> are obligația de a administra în
            condiții de siguranță și numai pentru scopurile specificate datele personale pe care ni le furnizați despre
            dumneavoastră, un membru al familiei dumneavoastră ori o altă persoană.</p>
        <p>Organizatorul procesează aceste date cu caracter personal exclusiv în scopul pentru care au fost colectate,
            respectiv livrarea cursului, eliberarea diplomei de participare. Durata de păstrare este de 5 ani pentru
            documentele de curs (diploma de participare) și de 1 an pentru restul datelor. Este posibil ca datele să se
            regăsească în backupuri realizate pentru protecție și asigurarea continuității serviciilor. Aceste backupuri
            sunt șterse de regulă anual.</p>
        <p>Opțional, cu acordul dumneavoastră, pentru trimiterea de Newslettere și/sau alerte periodice, prin folosirea
            poștei electronice. Acest acord poate fi revocat oricând, în subsolului emailului fiind un link de
            dezabonare.</p>
        <p>În cazul în care livrarea se va face de o altă entitate juridică decât <?php echo $strSiteOwner?>, datele
            colectate vor fi comunicate și acelei entități juridice în scopurile menționate mai sus.</p>
        <p>Sunteți obligat(ă) să furnizați datele solicitate, acestea fiind necesare pentru crearea facturii și,
            ulterior, eliberarea diplomei. Refuzul dvs. determină anularea comenzii. Informațiile înregistrate sunt
            destinate utilizării de către operator în scopul livrării serviciilor achiziționate și sunt comunicate numai
            de către dumneavoastră.</p>
        <p>În cazul în care este necesar, vom externaliza către alte companii anumite sarcini contribuind la serviciile
            noastre în numele nostru, în cadrul acordurilor de prelucrare a datelor. Este posibil, de exemplu, să
            furnizăm date cu caracter personal către agenți, contractori sau parteneri autorizați pentru găzduirea
            bazelor noastre de date, a site-ului web, pentru serviciile de prelucrare a datelor sau pentru a vă trimite
            informațiile pe care ni le-ați solicitat.</p>
        <p>Vom partaja sau permite accesul la astfel de informații NUMAI furnizorilor externi de servicii după cum este
            necesar pentru furnizarea serviciilor noastre. Aceste informații nu pot fi utilizate de către furnizorii
            externi de servicii pentru orice alte scopuri și sunt obligați prin contract să respecte confidențialitatea
            datelor personale încredințate.</p>
        <p>Toate transferurile de date cu caracter personal către terțe părți vor fi efectuate cu notificarea
            dumneavoastră anterioară și, după caz, cu acordul dumneavoastră. Orice transferuri de date cu caracter
            personal în alte țări decât cele pentru care a fost luată o decizie de adecvare în ceea ce privește nivelul
            de protecție a datelor de către Comisia Europeană, după cum este prezentat la adresa
            http://ec.europa.eu/justice/data-protection/international-transfers/adequacy/index_en.htm, au loc pe baza
            unor acorduri contractuale ce utilizează clauzele contractuale standard adoptate de Comisia Europeană sau
            alte garanții corespunzătoare, în conformitate cu legea în vigoare.</p>
        <p><strong>Produsele și serviciile oferite de noi se adresează persoanelor juridice.</strong> Nu vindem sau prestăm 
            servicii către persoane minore (fără capacitate deplină de exercițiu). În cazul în care descoperim că am primit datele 
            de identitate sau de contact ale unei persoane care nu îndeplinește condițiile de mai sus, 
            ne rezervăm dreptul de a anula comanda respectivă și vom șterge imediat toate datele respective.</p>
        <p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii,
            sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de
            proprietate intelectuală determinate.
        <h3>Candidaturi</h3>
        <p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție
            vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul
            dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei
            noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de
            colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca
            datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a
            restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este
            procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
        <h3>Drepturile utilizatorului</h3>
        <p>Beneficiați de dreptul de acces, de intervenție asupra datelor, dreptul de a nu fi supus unei decizii
            individuale și dreptul de a vă adresa justiției. Totodată, aveți dreptul să vă opuneți prelucrării datelor
            personale care vă privesc și să solicitați ștergerea datelor. </p>
        <p>Dacă unele din datele despre dumneavoastră sunt incorecte, vă rugăm să ne informați cât mai curând posibil.
            Pentru exercitarea acestor drepturi, vă puteți adresa cu o cerere scrisă, datată și semnată la Departamentul
            relații cu Clienții, la adresa de e-mail office at consaltis . ro, sau prin poștă la adresa din partea de
            jos a site-ului. De asemenea, vă este recunoscut dreptul de a vă adresa justiției.</p>
        <p>Aveți dreptul de a depune plângere în fața unei autorități de supraveghere. În Romania puteți depune plângere
            în fața Autorității Naționale de Supraveghere a Datelor cu Caracter Personal cu sediul în B-dul G-ral.
            Gheorghe Magheru 28-30, sector 1, cod poștal 010336, București, email anspdcp@dataprotection.ro, telefon
            +40.318.059.211, +40.318.059.212, website www.dataprotection.ro.</p>
        <p>Aveți dreptul de a vă adresa justiției pentru apărarea oricăror drepturi garantate de lege, care v-au fost
            încălcate.
        <p>Dreptul de intervenție asupra datelor se aplică pentru:</p>
        <ul>
            <li class="tiny">rectificarea, actualizarea, blocarea sau ștergerea datelor a căror prelucrare nu este
                conformă legii, în special a datelor incomplete sau inexacte;</li>
            <li class="tiny">transformarea în date anonime a datelor a căror prelucrare nu este conformă legii;</li>
            <li class="tiny">notificarea către terții cărora le-au fost dezvăluite datele, dacă această notificare nu se
                dovedește imposibilă sau nu presupune un efort disproporționat față de interesul legitim care ar putea
                fi lezat.</li>
        </ul>
        <p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii,
            sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de
            proprietate intelectuală determinate.</p>
        <h3>Jurnale de acces (loguri)</h3>
        <p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa
            (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură
            tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor
            incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual
            fără a fi citite, de regulă la 3-4 luni.</p>
        <h3>Rețele sociale</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe
            rețelele sociale vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate
            asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.
            <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de
            exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba
            de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de
            contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul
            în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
        <h3>Rețele de publicitate</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt
            colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate.
            Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu
            își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele
            dumneavoastră.</p>
        <h3>Înscriere și plată</h3>
        <p> Plata trebuie efectuată și confirmată înainte de data desfășurării evenimentului.</p>
        <p> Anularea participării se poate face prin e-mail cu cel puțin 3 zile lucrătoare înainte de data desfășurării
            evenimentului.</p>
        <p> Daca persoana înscrisă la eveniment nu mai poate fi prezentă, poate fi desemnat oricând un înlocuitor.</p>
        <p> În cazul neprezentării fără notificare în scris, suma achitata poate fi realocata, fără a se restitui,
            pentru un alt eveniment realizat de Organizator.</p>
        <p> Organizatorul își rezervă dreptul de a reprograma desfășurarea evenimentului, daca nu se întrunește grupa
            necesară de minim 4 persoane. La cerere, sumele achitate se vor restitui integral.</p>
        <p>Taxa se achită în contul <strong>RO65 BTRL RONC RT06 6358 1501</strong> deschis la Banca Transilvania până
            cel târziu cu 3 zile înainte de curs.</p>
        <p>Unitățile bugetare pot plăti în contul <strong>RO74 TREZ 7055 069X XX01 1133</strong> deschis la Trezoreria
            Sector 5, București.</p>
        <p>Prin transmiterea formularui de înscriere la curs sunteți de acord cu prelucrarea datelor transmise în scopul
            participării la curs și a eliberării diplomei de absolvire.</p>
        <h3>Revizuiri ale politicii</h3>
        <p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste
            politici prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de revizuiri și
            de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua la cunoștință
            Condițiile de Utilizare actualizate.</p>
        <?php
}
else 
{	
?>
        <p>Acest site nu colectează date cu caracter personal.</p>
        <h3>Jurnale de acces (loguri)</h3>
        <p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa
            (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură
            tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor
            incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual
            fără a fi citite, de regulă la 3-4 luni.</p>
        <h3>Candidaturi</h3>
        <p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție
            vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul
            dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei
            noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de
            colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca
            datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a
            restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este
            procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
        <h3>Rețele sociale</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe
            rețelele sociale vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate
            asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.
            <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de
            exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba
            de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de
            contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul
            în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
        <h3>Rețele de publicitate</h3>
        <p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu
            există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt
            colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate.
            Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu
            își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele
            dumneavoastră.</p>
        <?php }?>
              <p>Această pagină a fost revizuită la data de 19.12.2025</p>
        <h2>Legislație aplicabilă și Jurisdicție</h2>
        <p>Această politică de confidențialitate și utilizarea acestui site sunt guvernate de legile din România.
            Instanțele competente din România vor avea jurisdicție exclusivă asupra oricăror și tuturor disputelor ce
            vor apărea din sau se vor referi la sau vor fi în legătură cu prevederile acestei politici de confidențialitate
             în cazul disputelor. Instanțele relevante pentru dispute sunt cele care sunt aplicabile în sfera domiciliului 
             social al companiei, respectiv Sectorul 3 al Municipiului București.</p>
        <hr />
    </div>
</div>
<?php
include 'bottom.php';
?>