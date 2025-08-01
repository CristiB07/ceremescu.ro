<?php
//update 08.01.2025
include 'settings.php';
include 'classes/common.php';
$strKeywords="Termeni și condiții de utilizare";
$strDescription="termeni și condiții de utilizare a site-ului ". $strSiteName;
$strPageTitle="Termeni și condiții";
$url='termeni.php';
include 'header.php';
$i = date('d');
$m = date('m');
$yn = date('Y');
$sdata=$yn."-".$m."-".$i." 00:00:00";
?>
          <div class="grid-x grid-margin-x">
            <div class="large-12 cell">
  <h1><?php echo $strPageTitle?></span></h1>
<h2>Acord de utilizare</h2>
<p>Prin utilizarea acestui site, vă exprimați acordul cu aceste condiții de utilizare.</p>
<h2>Proprietatea Conținutului</h2>
<p>Site-ul și tot ceea ce cuprinde acesta, incluzând fără limitare toate textele și imaginile („Conținut”) sunt în proprietatea și sub dreptul de autor (copyright) al <?php echo $strSiteOwner?> sau al altora, după indicații, cu toate drepturile rezervate, cu excepția cazului în care nu este altfel specificat. Imaginile care ilustrează acest site sunt preluate de pe <a href="https://pixabay.com/service/license/">Pixabay</a> și se supun licenței de utilizare a acestuia. Este strict interzisă utilizarea oricărui conținut fără permisiunea proprietarului drepturilor de utilizare.</p>
<h2>Date personale</h2>
<?php if ($cartenabled==1)
{?>
<h3>Date privind operatorul de date</h3>
<p><?php echo $strSiteOwnerData?>.</p>
<h3>Politica de confidențialitate</h3>
<p><?php echo $strSiteOwner?> apreciază încrederea pe care o manifestați navigând pe site-ul nostru, scriindu-ne sau făcând comenzi on-line, de aceea dorim să vă asigurăm că nu vom furniza, vinde sau ceda nici unei alte părți baza de date referitoare la datele dumneavoastră personale.</p>
<p>Informațiile cu caracter personal ale Cumpărătorului pot fi furnizate și către Parchetul General, Poliție, instanțele judecătorești și altor organe abilitate ale statului, în baza și în limitele prevederilor legale și ca urmare a unor cereri expres formulate.</p>
<p>Conform cerințelor Regulamentului European privind Protecția Datelor cu Caracter Personal (The General Data Protection Regulation (GDPR) (EU) 2016/679), <?php echo $strSiteOwner?> are obligația de a administra în condiții de siguranță și numai pentru scopurile specificate datele personale pe care ni le furnizați despre dumneavoastră, un membru al familiei dumneavoastră ori o altă persoană.</p>
<p>Scopul colectării datelor este informarea Clienților/Cumpărătorilor privind situația Contului lor inclusiv validarea, procesarea și facturarea Comenzilor, rezolvarea anulărilor sau a problemelor de orice natură referitoare la o Comandă sau produsele achiziționate.</p>
<p>Opțional, numai cu acordul dumneavoastră, pentru trimiterea de Newslettere și/sau alerte periodice, prin folosirea poștei electronice. Acest acord poate fi revocat oricând, în subsolului emailului fiind un link de dezabonare.</p>
<p>În cazul în care livrarea produselor comandate se va face de o altă entitate juridică decât <?php echo $strSiteOwner?>, datele colectate vor fi comunicate și acelei entități juridice în scopurile menționate mai sus.</p>
<p>Sunteți obligat(ă) să furnizați datele solicitate, acestea fiind necesare pentru crearea facturii pentru produsele comandate și, ulterior, livrarea produselor. Refuzul dvs. determină anularea comenzii. Informațiile înregistrate sunt destinate utilizării de către operator în scopul livrării serviciilor și sunt comunicate numai de către dumneavoastră.</p>
<p>În cazul în care este necesar, vom externaliza către alte companii anumite sarcini contribuind la serviciile noastre în numele nostru, în cadrul acordurilor de prelucrare a datelor. Este posibil, de exemplu, să furnizăm date cu caracter personal către agenți, contractori sau parteneri autorizați pentru găzduirea bazelor noastre de date, a site-ului web, pentru serviciile de prelucrare a datelor sau pentru a vă trimite informațiile pe care ni le-ați solicitat.</p>
<p>Vom partaja sau permite accesul la astfel de informații NUMAI furnizorilor externi de servicii după cum este necesar pentru furnizarea serviciilor noastre. Aceste informații nu pot fi utilizate de către furnizorii externi de servicii pentru orice alte scopuri și sunt obligați prin contract să respecte confidențialitatea datelor personale încredințate.</p>
<p>Toate transferurile de date cu caracter personal către terțe părți vor fi efectuate cu notificarea dumneavoastră anterioară și, după caz, cu acordul dumneavoastră. Orice transferuri de date cu caracter personal în alte țări decât cele pentru care a fost luată o decizie de adecvare în ceea ce privește nivelul de protecție a datelor de către Comisia Europeană, după cum este prezentat la adresa http://ec.europa.eu/justice/data-protection/international-transfers/adequacy/index_en.htm, au loc pe baza unor acorduri contractuale ce utilizează clauzele contractuale standard adoptate de Comisia Europeană sau alte garanții corespunzătoare, în conformitate cu legea în vigoare.</p>
<p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii, sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de proprietate intelectuală determinate.
<h3>Candidaturi</h3>
<p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
<h3>Drepturile utilizatorului</h3>
<p>Beneficiați de dreptul de acces, de intervenție asupra datelor, dreptul de a nu fi supus unei decizii individuale și dreptul de a vă adresa justiției. Totodată, aveți dreptul să vă opuneți prelucrării datelor personale care vă privesc și să solicitați ștergerea datelor. </p>
<p>Dacă unele din datele despre dumneavoastră sunt incorecte, vă rugăm să ne informați cât mai curând posibil. Pentru exercitarea acestor drepturi, vă puteți adresa cu o cerere scrisă, datată și semnată la Departamentul relații cu Clienții, la adresa de e-mail office at consaltis . ro, sau prin poștă la adresa din partea de jos a site-ului. De asemenea, vă este recunoscut dreptul de a vă adresa justiției.</p>
<p>Aveți dreptul de a depune plângere în fața unei autorități de supraveghere. În Romania puteți depune plângere în fața Autorității Naționale de Supraveghere a Datelor cu Caracter Personal cu sediul în B-dul G-ral. Gheorghe Magheru 28-30, sector 1, cod poștal 010336, București, email anspdcp@dataprotection.ro, telefon  +40.318.059.211, +40.318.059.212, website www.dataprotection.ro.</p>
<p>Aveți dreptul de a vă adresa justiției pentru apărarea oricăror drepturi garantate de lege, care v-au fost încălcate.
<p>Dreptul de intervenție asupra datelor se aplică pentru:</p>
<ul>
<li class="tiny">rectificarea, actualizarea, blocarea sau ștergerea datelor a căror prelucrare nu este conformă legii, în special a datelor incomplete sau inexacte;</li>
<li class="tiny">transformarea în date anonime a datelor a căror prelucrare nu este conformă legii;</li>
<li class="tiny">notificarea către terții cărora le-au fost dezvăluite datele, dacă această notificare nu se dovedește imposibilă sau nu presupune un efort disproporționat față de interesul legitim care ar putea fi lezat.</li>
</ul>
<p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii, sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de proprietate intelectuală determinate.</p>
<h3>Jurnale de acces (loguri)</h3>
<p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual fără a fi citite, de regulă la 3-4 luni.</p>
<h3>Rețele sociale</h3>
<p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe rețelele sociale vă supuneți termenilor și condițiilor acestora.  <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră. <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
<h3>Rețele de publicitate</h3>
<p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate. Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.</p>
<h3>Revizuiri ale politicii</h3>
<p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste politici prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de revizuiri și de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua la cunoștință Condițiile de Utilizare actualizate.</p>
<h3>Produse</h3>
<h4><?php echo $strSiteOwner?> vinde produsele sale exclusiv societăților comerciale, fiind vorba de template-uri de documente pentru uzul exclusiv al acestora.</h4>
<p>Toate materialele vândute pe acest site sunt vândute „aşa cum sunt”, fiind produse „generice” şi ele trebuie adaptate situaţiei din organizaţia dumneavoastră de către o persoană competentă. <?php echo $strSiteOwner?> garantează conformarea acestora cu standardele respective, dar nu îşi poate asuma responsabilitatea pentru diferenţele între situaţiile descrise în document şi situaţia reală din organizaţia dumneavoastră sau pentru modificările ulterioare ale documentelor. </p>
<p>Din acest motiv, <?php echo $strSiteOwner?> nu poate fi făcută responsabilă pentru orice daune sau pierderi cauzate de utilizarea produselor achiziţionate de pe acest site.</p>
<p>La cerere, contra unei taxe suplimentare, <?php echo $strSiteOwner?> asigură personalizarea documentelor.</p>
<p>Toate informațiile folosite pentru descrierea bunurilor disponibile pe Site (imagini statice, dinamice, prezentari multimedia, etc.) nu reprezintă o obligație contractuală din partea vânzătorului, acestea fiind utilizate exclusiv cu titlu de prezentare.</p>
<p>Comanda înseamnă acțiunea de a solicita produse folosind butonul „Trimite Comanda” din coșul de cumpărături. Comanda este considerată contract între părți. Desfacerea unui contract se face prin notificarea firmei de catre client în temeiul termenilor legislației în vigoare sau de către <?php echo $strSiteOwner?> cu notificarea clientului, în cazul în care comanda nu poate fi onorata din motive intemeiate, fapt care implică restituirea sumei în cazul comenzilor pre-platite.</p>
<p>Comenzile plasate cu succes pe acest site sunt urmate de trimiterea automată a unei notificări pe e-mail-ul înscris în formularul de comandă. Din motive obiective, <?php echo $strSiteOwner?> își rezervă dreptul de a notifica exclusiv telefonic clientul în cazul în care este necesară modificarea numărului de produse din comandă. Mai departe, clientul va decide dacă acceptă modificarea sau anulează comanda, cu restituirea integrală a sumei achitate acolo unde este cazul.</p>
<p>Prin plasarea unei comenzi, clientul este de acord cu termenii și condițiile acestea și se consideră că este la curent cu forma de confirmare și livrare a comenzilor, cât și cu termenii și condițiile.</p>
<h2>Returnarea produselor</h2>	
<p>Materialele vândute pe acest site intră sub incidenţa articolului 18, aliniat m) din cadrul Ordonanţa de urgenţă nr. 34/2014 privind drepturile consumatorilor în cadrul contractelor încheiate cu profesioniştii și completările sale, fiind astfel încadrabile în grupa produselor care nu pot fi returnate prin natura lor. Prin acceptarea acestor termeni şi condiţii la validarea comenzii, cumpărătorul este de acord să nu solicite returnarea produselor.</p>
<?php
}
Elseif ($trainingenabled==1)
{?>
<h3>Date privind operatorul de date</h3>
<p><?php echo $strSiteOwnerData?>.</p>
<h3>Politica de confidențialitate</h3>
<p><?php echo $strSiteOwner?> apreciază încrederea pe care o manifestați navigând pe site-ul nostru, scriindu-ne sau făcând comenzi on-line, de aceea dorim să vă asigurăm că nu vom furniza, vinde sau ceda nici unei alte părți baza de date referitoare la datele dumneavoastră personale.</p>
<p>Informațiile cu caracter personal ale Cumpărătorului pot fi furnizate și către Parchetul General, Poliție, instanțele judecătorești și altor organe abilitate ale statului, în baza și în limitele prevederilor legale și ca urmare a unor cereri expres formulate.</p>
<p>Conform cerințelor Regulamentului European privind Protecția Datelor cu Caracter Personal (The General Data Protection Regulation (GDPR) (EU) 2016/679), <?php echo $strSiteOwner?> are obligația de a administra în condiții de siguranță și numai pentru scopurile specificate datele personale pe care ni le furnizați despre dumneavoastră, un membru al familiei dumneavoastră ori o altă persoană.</p>
<p>Organizatorul procesează aceste date cu caracter personal exclusiv în scopul pentru care au fost colectate, respectiv livrarea cursului, eliberarea diplomei de participare. Durata de păstrare este de 5 ani pentru documentele de curs (diploma de participare) și de 1 an pentru restul datelor. Este posibil ca datele să se regăsească în backupuri realizate pentru protecție și asigurarea continuității serviciilor. Aceste backupuri sunt șterse de regulă anual.</p>
<p>Opțional, cu acordul dumneavoastră, pentru trimiterea de Newslettere și/sau alerte periodice, prin folosirea poștei electronice. Acest acord poate fi revocat oricând, în subsolului emailului fiind un link de dezabonare.</p>
<p>În cazul în care livrarea  se va face de o altă entitate juridică decât <?php echo $strSiteOwner?>, datele colectate vor fi comunicate și acelei entități juridice în scopurile menționate mai sus.</p>
<p>Sunteți obligat(ă) să furnizați datele solicitate, acestea fiind necesare pentru crearea facturii și, ulterior, eliberarea diplomei. Refuzul dvs. determină anularea comenzii. Informațiile înregistrate sunt destinate utilizării de către operator în scopul livrării serviciilor achiziționate și sunt comunicate numai de către dumneavoastră.</p>
<p>În cazul în care este necesar, vom externaliza către alte companii anumite sarcini contribuind la serviciile noastre în numele nostru, în cadrul acordurilor de prelucrare a datelor. Este posibil, de exemplu, să furnizăm date cu caracter personal către agenți, contractori sau parteneri autorizați pentru găzduirea bazelor noastre de date, a site-ului web, pentru serviciile de prelucrare a datelor sau pentru a vă trimite informațiile pe care ni le-ați solicitat.</p>
<p>Vom partaja sau permite accesul la astfel de informații NUMAI furnizorilor externi de servicii după cum este necesar pentru furnizarea serviciilor noastre. Aceste informații nu pot fi utilizate de către furnizorii externi de servicii pentru orice alte scopuri și sunt obligați prin contract să respecte confidențialitatea datelor personale încredințate.</p>
<p>Toate transferurile de date cu caracter personal către terțe părți vor fi efectuate cu notificarea dumneavoastră anterioară și, după caz, cu acordul dumneavoastră. Orice transferuri de date cu caracter personal în alte țări decât cele pentru care a fost luată o decizie de adecvare în ceea ce privește nivelul de protecție a datelor de către Comisia Europeană, după cum este prezentat la adresa http://ec.europa.eu/justice/data-protection/international-transfers/adequacy/index_en.htm, au loc pe baza unor acorduri contractuale ce utilizează clauzele contractuale standard adoptate de Comisia Europeană sau alte garanții corespunzătoare, în conformitate cu legea în vigoare.</p>
<p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii, sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de proprietate intelectuală determinate.
<h3>Candidaturi</h3>
<p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
<h3>Drepturile utilizatorului</h3>
<p>Beneficiați de dreptul de acces, de intervenție asupra datelor, dreptul de a nu fi supus unei decizii individuale și dreptul de a vă adresa justiției. Totodată, aveți dreptul să vă opuneți prelucrării datelor personale care vă privesc și să solicitați ștergerea datelor. </p>
<p>Dacă unele din datele despre dumneavoastră sunt incorecte, vă rugăm să ne informați cât mai curând posibil. Pentru exercitarea acestor drepturi, vă puteți adresa cu o cerere scrisă, datată și semnată la Departamentul relații cu Clienții, la adresa de e-mail office at consaltis . ro, sau prin poștă la adresa din partea de jos a site-ului. De asemenea, vă este recunoscut dreptul de a vă adresa justiției.</p>
<p>Aveți dreptul de a depune plângere în fața unei autorități de supraveghere. În Romania puteți depune plângere în fața Autorității Naționale de Supraveghere a Datelor cu Caracter Personal cu sediul în B-dul G-ral. Gheorghe Magheru 28-30, sector 1, cod poștal 010336, București, email anspdcp@dataprotection.ro, telefon  +40.318.059.211, +40.318.059.212, website www.dataprotection.ro.</p>
<p>Aveți dreptul de a vă adresa justiției pentru apărarea oricăror drepturi garantate de lege, care v-au fost încălcate.
<p>Dreptul de intervenție asupra datelor se aplică pentru:</p>
<ul>
<li class="tiny">rectificarea, actualizarea, blocarea sau ștergerea datelor a căror prelucrare nu este conformă legii, în special a datelor incomplete sau inexacte;</li>
<li class="tiny">transformarea în date anonime a datelor a căror prelucrare nu este conformă legii;</li>
<li class="tiny">notificarea către terții cărora le-au fost dezvăluite datele, dacă această notificare nu se dovedește imposibilă sau nu presupune un efort disproporționat față de interesul legitim care ar putea fi lezat.</li>
</ul>
<p>Orice alt fel de comunicări sau materiale pe care le transmiteți pe acest Site, precum întrebări, comentarii, sugestii sau alte mesaje de acest fel, vor fi considerate ca neconfidențiale și neprotejate de drepturi de proprietate intelectuală determinate.</p>
<h3>Jurnale de acces (loguri)</h3>
<p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual fără a fi citite, de regulă la 3-4 luni.</p>
<h3>Rețele sociale</h3>
<p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe rețelele sociale vă supuneți termenilor și condițiilor acestora.  <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră. <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
<h3>Rețele de publicitate</h3>
<p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate. Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.</p>
<h3>Înscriere și plată</h3>
<p> Plata trebuie efectuată și confirmată înainte de data desfășurării evenimentului.</p>
<p> Anularea participării se poate face prin e-mail cu cel puțin 3 zile lucrătoare înainte de data desfășurării evenimentului.</p>
<p> Daca persoana înscrisă la eveniment nu mai poate fi prezentă, poate fi desemnat oricând un înlocuitor.</p>
<p> În cazul neprezentării fără notificare în scris, suma achitata poate fi realocata, fără a se restitui, pentru un alt eveniment realizat de Organizator.</p>
<p> Organizatorul își rezervă dreptul de a reprograma desfășurarea evenimentului, daca nu se întrunește grupa necesară de minim 4 persoane. La cerere, sumele achitate se vor restitui integral.</p>
<p>Taxa se achită în contul <strong>RO65 BTRL RONC RT06 6358 1501</strong> deschis la Banca Transilvania până cel târziu cu 3 zile înainte de curs.</p>
<p>Unitățile bugetare pot plăti în contul <strong>RO74 TREZ 7055 069X XX01 1133</strong> deschis la Trezoreria Sector 5, București.</p>
<p>Prin transmiterea formularui de înscriere la curs sunteți de acord cu prelucrarea datelor transmise în scopul participării la curs și a eliberării diplomei de absolvire.</p><h3>Revizuiri ale politicii</h3>
<p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste politici prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de revizuiri și de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua la cunoștință Condițiile de Utilizare actualizate.</p>
<?php
}
Else 
{	
?>
<p>Acest site nu colectează date cu caracter personal.</p>
<h3>Jurnale de acces (loguri)</h3>
<p>Orice server web colectează automat o serie de date privind vizita dumnevoastră - adresa IP, sursa (referința), browserul, pagina solicitată, răspunsul serverului la solicitare. Acestea sunt date de natură tehnică și nu sunt procesate în nici o formă. Jurnalele de acces sunt consultate doar în cazul unor incidente de securitate sau de natură tehnică pentru identificarea cauzei, altfel, ele sunt șterse manual fără a fi citite, de regulă la 3-4 luni.</p>
<h3>Candidaturi</h3>
<p>În cazul în care ne trimiteți CV-ul dumneavoastră în mod spontan sau ca răspuns la un anunț de poziție vacantă, datele respective sunt stocate, analizate pentru a vedea dacă există o potrivire între profilul dumneavoastră și cerințele postului. În cazul unei potriviri, veți fi contactat de cineva din partea firmei noastre pentru a explora posibilitatea unei colaborări. În cazul în care nu se ajunge la o formă de colaborare, informațiile sunt șterse manual periodic între șase luni și un an. Există posibilitatea ca datele să fie salvate în cadrul unui backup, fără însă a mai fi procesate. Rolul backupului este de a restaura datele în caz de incident de natură tehnică, nici o informație din cadrul unui backup nu este procesată altfel. Aceste backupuri sunt șterse manual după maxim 3 ani.</p>
<h3>Rețele sociale</h3>
<p><?php echo $strSiteOwner?> folosește rețelele sociale pentru a promova produsele și serviciile sale. Nu există instalate scripturi, accesul se face direct. În momentul în care accesați paginile noastre de pe rețelele sociale vă supuneți termenilor și condițiilor acestora.  <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră. <?php echo $strSiteOwner?> folosește datele personale de pe acele site-uri conform politicii acestora, de exemplu, pentru a vă informa cu privire la oferte, produse și servicii sau posturi vacante. Dacă este vorba de CV-uri sau date profesionale, ele sunt procesate conform punctului de mai sus. Dacă este vorba de date de contact, acestea nu mai sunt păstrate sau procesate în măsura în care nu s-a realizat o tranzacție. În cazul în care este realizată o tranzacție, datele sunt gestionate conform contractului încheiat între părți.</p>
<h3>Rețele de publicitate</h3>
<p><?php echo $strSiteOwner?> folosește rețelele publicitare pentru a promova produsele și serviciile sale. Nu există instalate scripturi pe acest site. În momentul în care accesați un anunț publicitar, datele sunt colectate și procesate de furnizorul serviciului respectiv, noi având acces doar la date anonomizate. Folosind rețelele publicitare vă supuneți termenilor și condițiilor acestora. <?php echo $strSiteOwner?> nu își poate asuma nici o responsabilitate cu privire la modului în care aceste rețele procesează datele dumneavoastră.</p>
<?php }?>
<h2>Lipsa garanțiilor</h2>
<p>ÎNTREG CONȚINUTUL ACESTUI SITE POATE FI MODIFICAT ȘI VĂ ESTE OFERIT „CA ATARE” FĂRĂ A SE OFERI NICI O GARANȚIE DE NICI UN FEL, FIE ACEASTA EXPRESĂ SAU IMPLICITĂ.</p>
<h2>Link-uri pe site-urile unei terțe părți</h2>
<p>Site-ul poate conține link-uri către alte site-uri aflate în proprietatea sau operate de alte părți decât <?php echo $strSiteOwner?>. Astfel de link-uri vă sunt furnizate pentru a le folosi numai dacă veți dori aceasta. <?php echo $strSiteOwner?> nu controlează, și nu este răspunzătoare pentru conținutul și condițiile de confidențialitate sau securitate și de funcționalitatea acestor site-uri. Fără a se limita la cele mai sus menționate, <?php echo $strSiteOwner?> își declina în mod special orice răspundere dacă aceste site-uri:</p>
<ul>
<li class="tiny">Încalcă drepturile de proprietate intelectuală ale unei terțe părți;</li>
<li class="tiny">Sunt inexacte, incomplete sau conțin informații înșelătoare;</li>
<li class="tiny">Nu au caracter comercial sau nu răspund îndeplinirii unui anumit scop;</li>
<li class="tiny">Nu oferă o securitate adecvata;</li>
<li class="tiny">Conțin viruși sau alte elemente cu caracter distructiv;</li>
<li class="tiny">Sunt licențioase sau calomnioase.</li>
</ul>
<p>De asemenea, <?php echo $strSiteOwner?> nu autorizează conținutul sau orice alte produse și servicii promovate pe astfel de site-uri. Dacă intrați printr-un link pe astfel de site-uri sau pe acest site, va asumați personal riscul, fără a exista în acest sens permisiunea <?php echo $strSiteOwner?>.</p>
<h2>Revizuiri ale acestor Condiții de Utilizare</h2>
<p><?php echo $strSiteOwner?> poate, în orice moment și fără notificare prealabilă, să revizuiască aceste Condiții de Utilizare prin actualizarea acestora. Sunteți obligat să respectați oricare și toate astfel de revizuiri și de aceea va trebui să vizitați aceasta pagină a site-ului nostru în mod periodic pentru a lua la cunoștință Condițiile de Utilizare actualizate.</p>
<p>Această pagină a fost revizuită la data de 17.08.2023</p>
<h2>Legislație aplicabilă și Jurisdicție</h2>
<p>Acești Termeni și Condiții de Utilizare și utilizarea acestui site sunt guvernate de legile din România. Instanțele competente din România vor avea jurisdicție exclusivă asupra oricăror și tuturor disputelor ce vor apărea din sau se vor referi la sau vor fi în legătură cu prevederile Termenilor și Condițiilor de Utilizare și/sau Conținutul site-ului sau în cazul disputelor în care acești Termeni și Condiții de Utilizare și/sau acest site vor fi considerate fapte relevante pentru astfel de dispute.</p>
<hr />
</div>
</div>
<?php
include 'bottom.php';
?>