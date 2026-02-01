# Analiză și Îmbunătățiri Bază de Date

## Starea Curentă

- **Număr total tabele**: 93
- **Foreign keys existente**: 10
- **Indexes existente**: ~20

## Probleme Identificate

### 1. Lipsa Foreign Keys
Multe tabele au relații implicite dar nu au foreign keys definite:

#### Relații Client
- `clienti_abonamente.abonament_client_ID` → `clienti_date.ID_Client`
- `clienti_activitati.ID_Client` → `clienti_date.ID_Client`
- `clienti_activitati_clienti.ID_Client` → `clienti_date.ID_Client`
- `clienti_autorizatii_clienti.ID_Client` → `clienti_date.ID_Client`
- `clienti_contacte.client_ID` → `clienti_date.ID_Client`
- `clienti_contracte.ID_Client` → `clienti_date.ID_Client`
- `clienti_fisa.ID_Client` → `clienti_date.ID_Client`
- `clienti_programari.programare_client` → `clienti_date.ID_Client`
- `clienti_vizite.client_vizita` → `clienti_date.ID_Client`

#### Relații Utilizator
- `administrative_deconturi.decont_user` → `date_utilizatori.utilizator_Code`
- `administrative_pontaje.pontaj_user` → `date_utilizatori.utilizator_Code`
- `application_errors.error_utilizator_id` → `date_utilizatori.utilizator_ID`
- `application_logs.log_utilizator_id` → `date_utilizatori.utilizator_ID`
- `clienti_programari.programare_user` → `date_utilizatori.utilizator_ID`
- `elearning_trainers.trainer_utilizator_ID` → `date_utilizatori.utilizator_ID`

#### Relații Facturare
- `efactura.factura_ID` → `facturare_facturi.factura_ID`
- `facturare_articole_facturi.factura_ID` → `facturare_facturi.factura_ID`
- `facturare_chitante.chitanta_factura_ID` → `facturare_facturi.factura_ID` (VARCHAR issue!)

#### Relații E-Learning
- `elearning_answers.answer_student` → `site_accounts.account_id`
- `elearning_answers.answer_question` → `elearning_questions.question_ID`
- `elearning_answers.answer_test` → `elearning_tests.test_ID`
- `elearning_enrollments.elearning_enrollments_stud_id` → `site_accounts.account_id`
- `elearning_enrollments.elearning_enrollments_course_id` → `elearning_courses.Course_id`
- `elearning_enrollments.elearning_enrollments_courseschedule_id` → `elearning_courseschedules.schedule_ID`
- `elearning_lessons.lesson_course` → `elearning_courses.Course_id`
- `elearning_questions.question_test` → `elearning_tests.test_ID`
- `elearning_tests.test_course` → `elearning_courses.Course_id`

#### Relații Tickets/Helpdesk
- `tickets.ticket_createdby` → `site_accounts.account_id`
- `tickets.ticket_asignedto` → `date_utilizatori.utilizator_ID`
- `tickets_replies.reply_by_ui` → mixed (accounts/utilizatori)
- `tickets_log.actor_ui` → mixed (accounts/utilizatori)

#### Relații Shop/Magazin
- `magazin_articole.articol_produs` → `magazin_produse.produs_id`
- `magazin_articole.articol_idcomanda` → `magazin_comenzi.comanda_ID`
- `magazin_comenzi.comanda_cont_id` → `magazin_cumparatori.cumparator_id`
- `magazin_comenzi.company_id` → `magazin_firme.firma_id`

#### Relații Sales
- `sales_programari.programare_user` → `date_utilizatori.utilizator_ID`
- `sales_programari.programare_client` → `sales_prospecti.prospect_id`
- `sales_vizite_prospecti.client_vizita` → `sales_prospecti.prospect_id`

### 2. Lipsa Indexes pe Câmpuri Frecvent Folosite

#### Căutări după CUI/Email
- `clienti_date.Client_CUI` - folosit în căutări și join-uri
- `clienti_date.Client_Email` - folosit în notificări
- `clienti_date_fiscale.cui` - deja UNIQUE KEY ✓
- `sales_prospecti.prospect_cui` - folosit în căutări
- `site_accounts.account_email` - login
- `date_utilizatori.utilizator_Email` - login

#### Date și Filtre Temporale
- `facturare_facturi.factura_data_emiterii` - rapoarte frecvente
- `facturare_facturi.factura_client_achitat` - filtre status
- `application_logs.log_utilizator_time` - rapoarte
- `clienti_programari.programare_data_inceput` - calendar

#### Status și Stări
- `tickets.ticket_status` - deja există ✓
- `facturare_facturi.factura_client_inchisa` - filtre
- `facturare_facturi.factura_client_anulat` - filtre
- `elearning_enrollments.elearning_enrollments_active` - filtre

### 3. Probleme de Integritate Date

#### Tipuri de Date Inconsistente
- `facturare_chitante.chitanta_factura_ID` VARCHAR(256) ar trebui să fie INT
- `clienti_activitati.ID_User` VARCHAR(10) vs `date_utilizatori.utilizator_ID` INT
- `administrative_deconturi.decont_user` VARCHAR(4) vs `date_utilizatori.utilizator_Code` VARCHAR(4) ✓
- `administrative_pontaje.pontaj_user` VARCHAR(4) - inconsistent

#### Lipsa Constraints
- Fără CHECK constraints pentru:
  - Status-uri (ar trebui să fie ENUM sau CHECK)
  - Date (data_emiterii <= data_scadenta)
  - Sume (> 0)
  - Email format

### 4. Probleme de Performanță

#### Tabele Fără Primary Keys
Toate tabelele par să aibă PK ✓

#### Tabele Mari Fără Partiționare
- `application_logs` - 2649 înregistrări, va crește rapid
- `facturare_facturi` - 7916 înregistrări
- `bilanturi` - 349 înregistrări

## Recomandări Prioritare

### Prioritate ÎNALTĂ
1. ✅ Adaugă foreign keys pentru relațiile client-date
2. ✅ Adaugă indexes pe câmpurile CUI, email, date
3. ✅ Corectează tipul `facturare_chitante.chitanta_factura_ID`

### Prioritate MEDIE
4. ✅ Adaugă foreign keys pentru relațiile utilizator
5. ✅ Adaugă foreign keys pentru relațiile facturare
6. ✅ Adaugă indexes pe status-uri și filtre frecvente

### Prioritate SCĂZUTĂ
7. Adaugă CHECK constraints pentru validare
8. Consideră partitionarea tabelelor mari
9. Normalizează tipurile de date inconsistente

## Next Steps

1. Creează script de migrare pentru producție (fără DROP/ALTER destructive)
2. Testează pe copie de dezvoltare
3. Backup complet înainte de aplicare
4. Aplică pe producție în fereastră de mentenanță
