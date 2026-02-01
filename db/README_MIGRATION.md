# Database Migration Guide

## Prezentare Generală

Acest ghid descrie procesul de îmbunătățire a bazei de date prin adăugarea de foreign keys și indexuri pentru performanță și integritate optimă a datelor.

## Fișiere

### 1. `masterdb.sql`
- **Scop**: Schema curată de bază, fără date
- **Utilizare**: Pentru setup-uri noi, dezvoltare locală
- **Caracteristici**:
  - AUTO_INCREMENT resetat la 1
  - Charset standardizat la utf8mb4
  - Fără CHARACTER SET la nivel de câmp

### 2. `masterdb_improved.sql`
- **Scop**: Schema completă cu toate foreign keys și indexuri
- **Utilizare**: Pentru setup-uri noi care vor avea integritate completă din start
- **Include**: 
  - Tot din masterdb.sql
  - + Foreign Keys (~50)
  - + Indexuri (~40)

### 3. `migrations/001_add_foreign_keys_and_indexes.sql`
- **Scop**: Script de migrare pentru baze de date EXISTENTE
- **Utilizare**: Pentru aplicare pe producție sau baze cu date
- **Caracteristici**:
  - NU include DROP TABLE
  - Adaugă doar ALTER TABLE
  - Sigur pentru producție

## Îmbunătățiri Aplicate

### Foreign Keys Adăugate (50+)

#### Relații Client (9 FK)
- `clienti_abonamente` → `clienti_date`
- `clienti_activitati` → `clienti_date`
- `clienti_activitati_clienti` → `clienti_date`
- `clienti_autorizatii_clienti` → `clienti_date`
- `clienti_contacte` → `clienti_date`
- `clienti_contracte` → `clienti_date`
- `clienti_fisa` → `clienti_date`
- `clienti_programari` → `clienti_date`
- `clienti_vizite` → `clienti_date`

#### Relații Utilizator (7 FK)
- `clienti_programari` → `date_utilizatori`
- `sales_programari` → `date_utilizatori`
- `elearning_trainers` → `date_utilizatori`
- `proiecte_activitati` → `date_utilizatori`
- `proiecte_status` → `date_utilizatori`

#### Relații Facturare (4 FK)
- `efactura` → `facturare_facturi`
- `facturare_articole_facturi` → `facturare_facturi`
- `facturare_facturi` → `clienti_date`

#### Relații E-Learning (12 FK)
- `elearning_answers` → `site_accounts`, `elearning_tests`
- `elearning_enrollments` → `site_accounts`, `elearning_courses`, `elearning_courseschedules`
- `elearning_lessons` → `elearning_courses`
- `elearning_tests` → `elearning_courses`
- `elearning_questions` → `elearning_tests`
- `elearning_courseschedules` → `elearning_courses`
- `elearning_tests_takes` → `site_accounts`, `elearning_tests`

#### Relații Magazin (5 FK)
- `magazin_articole` → `magazin_produse`, `magazin_comenzi`
- `magazin_comenzi` → `magazin_cumparatori`, `magazin_firme`
- `site_companies` → `site_accounts`

#### Relații Sales (3 FK)
- `sales_programari` → `date_utilizatori`, `sales_prospecti`
- `sales_vizite_prospecti` → `sales_prospecti`

#### Relații Waste Management (4 FK)
- `ambalaje_deseuri` → `clienti_date`
- `ambalaje_gestionate` → `clienti_date`
- `deseuri_raportari` → `clienti_date`
- `deseuri_stocuri` → `clienti_date`

#### Relații Proiecte (5 FK)
- `proiecte` → `clienti_date`
- `proiecte_activitati` → `proiecte`, `date_utilizatori`
- `proiecte_status` → `proiecte_activitati`, `date_utilizatori`

### Indexuri Adăugate (40+)

#### Căutări și Filtre
- `clienti_date`: CUI, Email, Tip, Judet
- `sales_prospecti`: CUI, Email, Status, Alocat
- `date_utilizatori`: Email, Code, Role
- `site_accounts`: Email, Active, Company

#### Performanță Facturare
- `facturare_facturi`: Data emiterii, Client ID, Achitat, Închisă, Anulat, CUI, Tip
- `facturare_articole_facturi`: Factura ID
- `facturare_chitante`: Data, Închisă, Anulat

#### Raportare și Logging
- `application_logs`: Time, User ID
- `clienti_contracte`: Client ID, Activ, Data
- `clienti_abonamente`: Client ID, Activ

#### Calendar și Programări
- `clienti_programari`: User, Client, Data început, Finalizată
- `sales_programari`: User, Client, Data, Finalizată

#### E-Learning
- `elearning_enrollments`: Student, Course, Schedule, Active
- `elearning_answers`: Test Take

#### Magazin
- `magazin_comenzi`: Utilizator, Status, Cont ID, Company
- `magazin_articole`: Comanda, Produs

## Procedură de Aplicare

### Opțiunea 1: Setup Nou (Dezvoltare/Testing)

```bash
# Folosește masterdb_improved.sql pentru o bază complet nouă
mysql -u username -p database_name < db/masterdb_improved.sql
```

### Opțiunea 2: Migrare Bază Existentă (PRODUCȚIE)

#### Pas 1: Backup Complet

```bash
# Backup complet cu date
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Verificare backup
mysql -u username -p -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='database_name'"
```

#### Pas 2: Verificare Integritate Date

```bash
# Rulează verificări înainte de migrare
mysql -u username -p database_name < db/migrations/000_pre_migration_checks.sql
```

#### Pas 3: Aplicare Migrare

```bash
# Aplică scriptul de migrare
mysql -u username -p database_name < db/migrations/001_add_foreign_keys_and_indexes.sql
```

#### Pas 4: Verificare Post-Migrare

```sql
-- Verifică numărul de foreign keys adăugate
SELECT 
    COUNT(*) as total_foreign_keys
FROM 
    information_schema.KEY_COLUMN_USAGE
WHERE 
    REFERENCED_TABLE_SCHEMA = DATABASE()
    AND CONSTRAINT_NAME != 'PRIMARY';

-- Ar trebui să returneze ~60-70 (10 existente + ~50-60 noi)

-- Verifică indexurile
SELECT 
    TABLE_NAME,
    COUNT(*) as index_count
FROM 
    information_schema.STATISTICS
WHERE 
    TABLE_SCHEMA = DATABASE()
GROUP BY 
    TABLE_NAME
ORDER BY 
    index_count DESC;
```

## Probleme Cunoscute și Soluții

### 1. Foreign Key Constraint Fails

**Problemă**: Date inconsistente în tabele copil care nu au corespondent în tabela părinte.

**Soluție**:
```sql
-- Identifică înregistrări orfane
SELECT ca.* 
FROM clienti_abonamente ca
LEFT JOIN clienti_date cd ON ca.abonament_client_ID = cd.ID_Client
WHERE cd.ID_Client IS NULL;

-- Curăță sau corectează înainte de migrare
```

### 2. Duplicate Key Entries

**Problemă**: Index-uri nu pot fi create din cauza duplicate entries.

**Soluție**:
```sql
-- Găsește duplicate
SELECT Client_CUI, COUNT(*) 
FROM clienti_date 
GROUP BY Client_CUI 
HAVING COUNT(*) > 1;

-- Rezolvă duplicate înainte de migrare
```

### 3. Performance Issues During Migration

**Recomandări**:
- Rulează migrarea în afara orelor de vârf
- Consideră migrarea pe batch-uri (comentează secțiuni din script)
- Monitorizează `SHOW PROCESSLIST` în timpul migrării

## Rollback Plan

În caz de probleme, restaurează backup-ul:

```bash
# Stop aplicația
systemctl stop application_name

# Restaurare backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

# Restart aplicație
systemctl start application_name

# Verificare
mysql -u username -p -e "SELECT COUNT(*) FROM database_name.clienti_date"
```

## Beneficii Așteptate

### Integritate Date
- ✅ Eliminarea înregistrărilor orfane
- ✅ Cascade delete automat
- ✅ Referential integrity garantată

### Performanță
- ✅ Căutări după CUI: ~10-100x mai rapide
- ✅ Rapoarte facturare: ~5-20x mai rapide
- ✅ Query-uri JOIN: ~3-10x mai rapide

### Mentenanță
- ✅ Schema mai clară și documentată
- ✅ Debugging mai ușor
- ✅ Mai puține bug-uri de integritate

## Monitoring Post-Migrare

### Verificare Performance

```sql
-- Query performance înainte și după
EXPLAIN SELECT * FROM clienti_date WHERE Client_CUI = 'RO12345678';

-- Ar trebui să folosească indexul idx_client_cui
-- type: ref
-- key: idx_client_cui
-- rows: 1 (sau foarte puține)
```

### Slow Query Log

```sql
-- Activează slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Monitorizează pentru query-uri lente
```

## Suport

Pentru probleme sau întrebări:
1. Verifică `database_improvements_analysis.md` pentru detalii tehnice
2. Consultă scriptul de migrare pentru comentarii în-line
3. Contactează echipa de dezvoltare

## Changelog

### v1.0.0 - 2026-02-01
- Adăugate ~50 foreign keys
- Adăugate ~40 indexuri
- Standardizat charset la utf8mb4
- Resetat auto_increment la 1 pentru masterdb.sql
