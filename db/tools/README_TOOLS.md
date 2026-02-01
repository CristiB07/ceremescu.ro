# Database Tools Documentation

## Prezentare Generală

Aceste tools au fost create pentru a gestiona un CRM multi-tenant unde diferite instanțe pot avea configurații diferite de module.

## 🛠 Tools Disponibile

### 1. Script de Migrare Inteligent (v2.0)

**Fișier**: `migrations/001_add_foreign_keys_and_indexes_v2.sql`

#### Caracteristici

- ✅ **Verifică existența tabelelor** înainte de modificare
- ✅ **Verifică existența constraints** înainte de adăugare
- ✅ **Verifică existența indexes** înainte de adăugare
- ✅ **Idempotent** - poate fi rulat multiple ori fără erori
- ✅ **Multi-tenant safe** - funcționează pe instanțe cu module diferite

#### Utilizare

```bash
# Aplicare pe producție
mysql -u root -p production_db < migrations/001_add_foreign_keys_and_indexes_v2.sql

# Rezultat:
# - Adaugă doar ce lipsește
# - Afișează status pentru fiecare operație
# - ✓ = adăugat cu succes
# - ⊘ = există deja (skip)
# - ⊗ = tabel nu există (skip)
```

#### Proceduri Helper

**AddIndexIfNotExists**:
```sql
CALL AddIndexIfNotExists('table_name', 'index_name', '`column1`, `column2`');
```

**AddForeignKeyIfNotExists**:
```sql
CALL AddForeignKeyIfNotExists(
    'table_name', 
    'constraint_name',
    'column_name',
    'referenced_table',
    'referenced_column',
    'CASCADE',  -- ON DELETE
    'CASCADE'   -- ON UPDATE
);
```

### 2. Schema Comparison Tool (SQL)

**Fișier**: `tools/compare_schemas.sql`

#### Caracteristici

- Compară schema între master și producție
- Identifică tabele lipsă
- Detectează diferențe de coloane
- Verifică indexes lipsă
- Verifică foreign keys lipsă
- Raportează probleme de charset/collation
- Identifică tabele fără primary keys

#### Utilizare

```bash
# Generare raport
mysql -u root -p production_db < tools/compare_schemas.sql > schema_report.txt

# Review raport
cat schema_report.txt
```

#### Output Secțiuni

1. **Missing Tables** - Tabele din master care lipsesc în producție
2. **Column Differences** - Coloane cu tipuri diferite
3. **Missing Indexes** - Indexes care ar trebui adăugate
4. **Missing Foreign Keys** - FK care lipsesc
5. **Database Statistics** - Statistici generale
6. **Tables Without PK** - Tabele fără primary key
7. **Columns Needing Indexes** - Sugestii de indexes
8. **Charset Issues** - Probleme de encoding
9. **Engine Issues** - Tabele non-InnoDB

### 3. Python Schema Comparator

**Fișier**: `tools/compare_db_schemas.py`

#### Caracteristici

- ✅ Compară două baze de date complet
- ✅ Generează raport detaliat
- ✅ **Generează automat script de migrare**
- ✅ Suport pentru host-uri remote
- ✅ Output formatat și ușor de citit

#### Instalare Dependințe

```bash
pip3 install mysql-connector-python
```

#### Utilizare

```bash
# Compară local master cu producție locală
./tools/compare_db_schemas.py \
    --master-db cnsx001_master \
    --prod-db cnsx001_production \
    --user root \
    --password secret \
    --output-report reports/diff_$(date +%Y%m%d).txt \
    --output-migration migrations/auto_migration_$(date +%Y%m%d).sql

# Compară local cu server remote
./tools/compare_db_schemas.py \
    --master-host localhost \
    --master-db cnsx001_master \
    --prod-host production.server.com \
    --prod-db cnsx001_production \
    --user root \
    --password secret \
    --prod-port 3306 \
    --output-report reports/prod_diff.txt \
    --output-migration migrations/prod_migration.sql
```

#### Parametri

| Parametru | Descriere | Default |
|-----------|-----------|---------|
| `--master-host` | Host bază master | localhost |
| `--master-db` | Nume bază master | *required* |
| `--master-port` | Port bază master | 3306 |
| `--prod-host` | Host bază producție | localhost |
| `--prod-db` | Nume bază producție | *required* |
| `--prod-port` | Port bază producție | 3306 |
| `--user` | Utilizator MySQL | *required* |
| `--password` | Parolă MySQL | *required* |
| `--output-report` | Fișier raport text | stdout |
| `--output-migration` | Fișier script migrare | none |

#### Output Example

```
==================================================================
DATABASE SCHEMA COMPARISON REPORT
==================================================================
Master: cnsx001_master @ localhost
Production: cnsx001_production @ production.server.com
==================================================================

SUMMARY: 15 differences found

1. MISSING TABLES IN PRODUCTION (2)
----------------------------------------------------------------------
  • elearning_courses
  • elearning_lessons

3. MISSING COLUMNS IN PRODUCTION (3)
----------------------------------------------------------------------
  • clienti_date.date_fiscale
    Type: int DEFAULT NULL
  • facturare_facturi.factura_cod_factura
    Type: varchar(4) DEFAULT NULL

5. MISSING INDEXES IN PRODUCTION (8)
----------------------------------------------------------------------
  • clienti_date.idx_client_cui
    Columns: Client_CUI
  • facturare_facturi.idx_factura_data_emiterii
    Columns: factura_data_emiterii

6. MISSING FOREIGN KEYS IN PRODUCTION (2)
----------------------------------------------------------------------
  • clienti_abonamente.fk_abonament_client
    abonament_client_ID → clienti_date.ID_Client
    ON DELETE CASCADE, ON UPDATE CASCADE
```

## 🔄 Workflow Recomandat

### Scenario 1: Dezvoltare Nouă Funcționalitate

```bash
# 1. Modifică schema în master (masterdb.sql)
vim db/masterdb.sql

# 2. Compară master cu dev local
./tools/compare_db_schemas.py \
    --master-db cnsx001_master \
    --prod-db cnsx001_dev \
    --user root --password secret \
    --output-migration migrations/feature_X_$(date +%Y%m%d).sql

# 3. Review și test migrare pe dev
mysql -u root -p cnsx001_dev < migrations/feature_X_YYYYMMDD.sql

# 4. Test aplicație pe dev

# 5. Commit master + migration
git add db/masterdb.sql migrations/feature_X_YYYYMMDD.sql
git commit -m "Add feature X database changes"
```

### Scenario 2: Deploy pe Producție

```bash
# 1. Backup producție
mysqldump -u root -p production_db > backups/prod_backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Compară master cu producție
./tools/compare_db_schemas.py \
    --master-db cnsx001_master \
    --prod-host prod.server.com \
    --prod-db cnsx001_production \
    --user root --password secret \
    --output-report reports/prod_diff_$(date +%Y%m%d).txt \
    --output-migration migrations/prod_migration_$(date +%Y%m%d).sql

# 3. Review diferențe
cat reports/prod_diff_YYYYMMDD.txt

# 4. Review script migrare
cat migrations/prod_migration_YYYYMMDD.sql

# 5. Test pe staging (dacă există)
mysql -u root -p staging_db < migrations/prod_migration_YYYYMMDD.sql

# 6. Aplică pe producție (în maintenance window)
mysql -u root -p production_db < migrations/prod_migration_YYYYMMDD.sql

# 7. Verificare post-deployment
mysql -u root -p production_db -e "
    SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE();
    SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE();
"
```

### Scenario 3: Audit Periodic

```bash
# 1. Raport complet toate instanțele
for instance in client1_db client2_db client3_db; do
    echo "Checking $instance..."
    ./tools/compare_db_schemas.py \
        --master-db cnsx001_master \
        --prod-db $instance \
        --user root --password secret \
        --output-report reports/${instance}_diff_$(date +%Y%m%d).txt
done

# 2. Sumarizare diferențe
grep "SUMMARY:" reports/*_diff_*.txt

# 3. Acțiune pentru instanțele cu diferențe mari
```

## 🔧 Relații Aloc/Alocat

### Contexul Problemei

Câmpurile `aloc`, `alocat`, `Client_Aloc` etc. sunt VARCHAR care referențiază `utilizator_Code` (tot VARCHAR).

MySQL **permite** foreign keys între VARCHAR-uri, DAR:
- Trebuie să fie **exact același tip**
- Trebuie să fie **exact același charset/collation**
- Pot apărea probleme de performanță

### Soluții Recomandate

#### Opțiunea 1: Foreign Keys (Simplă)

În scriptul v2, găsești secțiunea comentată:

```sql
-- Decomentează și testează pe dev:
CALL AddForeignKeyIfNotExists('clienti_date', 'fk_client_aloc_user', 
    'Client_Aloc', 'date_utilizatori', 'utilizator_Code', 
    'SET NULL', 'CASCADE');
```

**Pro**: Integritate referențială automată  
**Con**: Pot apărea erori de collation, performance overhead

#### Opțiunea 2: Triggers (Robustă)

```sql
-- Trigger pentru validare Client_Aloc
DELIMITER $$
CREATE TRIGGER validate_client_aloc_before_insert
BEFORE INSERT ON clienti_date
FOR EACH ROW
BEGIN
    IF NEW.Client_Aloc IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM date_utilizatori 
            WHERE utilizator_Code = NEW.Client_Aloc
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid Client_Aloc: user code does not exist';
        END IF;
    END IF;
END$$

CREATE TRIGGER validate_client_aloc_before_update
BEFORE UPDATE ON clienti_date
FOR EACH ROW
BEGIN
    IF NEW.Client_Aloc IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM date_utilizatori 
            WHERE utilizator_Code = NEW.Client_Aloc
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid Client_Aloc: user code does not exist';
        END IF;
    END IF;
END$$
DELIMITER ;
```

**Pro**: Control complet, mesaje custom  
**Con**: Mai mult cod de întreținut

#### Opțiunea 3: Application-Level (Pragmatică)

Validare în codul aplicației + indexes pentru performanță.

```sql
-- Doar indexes, fără FK
CREATE INDEX idx_client_aloc ON clienti_date(Client_Aloc);
CREATE INDEX idx_utilizator_code ON date_utilizatori(utilizator_Code);
```

**Pro**: Flexibilitate maximă, fără overhead DB  
**Con**: Integritate depinde de aplicație

### Recomandare

Pentru CRM-ul tău, sugerez **Opțiunea 3** deoarece:
1. Ai deja validare în aplicație (presupun)
2. VARCHAR foreign keys pot cauza probleme de performanță
3. Indexes sunt suficiente pentru join-uri rapide
4. Flexibilitate pentru instanțe cu configurații diferite

## 📊 Best Practices

### 1. Versionare Migrări

```bash
migrations/
├── 001_add_foreign_keys_and_indexes_v2.sql
├── 002_add_elearning_module_YYYYMMDD.sql
├── 003_alter_facturare_columns_YYYYMMDD.sql
└── ...
```

### 2. Testare

```bash
# Rulează pe copie dev înainte de producție
mysql -u root -p cnsx001_dev_copy < migration.sql

# Verifică integritatea
mysqlcheck -u root -p --check --databases cnsx001_dev_copy
```

### 3. Rollback Plan

```bash
# Backup înainte
mysqldump > backup_before_migration.sql

# Dacă migrarea eșuează:
mysql < backup_before_migration.sql
```

### 4. Monitorizare Post-Migrare

```sql
-- Verifică foreign keys
SELECT TABLE_NAME, CONSTRAINT_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = DATABASE();

-- Verifică indexes
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME 
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE();

-- Verifică orphaned records (ar trebui 0)
SELECT COUNT(*) FROM clienti_abonamente ca
LEFT JOIN clienti_date cd ON ca.abonament_client_ID = cd.ID_Client
WHERE cd.ID_Client IS NULL;
```

## 🆘 Troubleshooting

### Error: Foreign Key Constraint Fails

```
ERROR 1452: Cannot add or update a child row: 
a foreign key constraint fails
```

**Soluție**: Date orfane în tabel. Curăță înainte:

```bash
mysql < migrations/000_pre_migration_checks.sql
# Review output pentru orphaned records
# Curăță sau corectează date
```

### Error: Duplicate Key Entry

```
ERROR 1062: Duplicate entry 'value' for key 'index_name'
```

**Soluție**: Există duplicate. Rezolvă înainte de index:

```sql
SELECT column_name, COUNT(*) 
FROM table_name 
GROUP BY column_name 
HAVING COUNT(*) > 1;
```

### Error: Table Doesn't Exist

```
⊗ Table does not exist: elearning_courses
```

**Soluție**: Normal - instanța nu are modulul respectiv. Skip-ul este automat în v2.

## 📝 Changelog

### v2.0.0 - 2026-02-01
- ✅ Adăugat verificare existență tabele
- ✅ Adăugat verificare existență constraints/indexes
- ✅ Script idempotent (re-runnable)
- ✅ Tool Python pentru comparare automată
- ✅ Generare automată script migrare
- ✅ Suport multi-tenant

### v1.0.0 - 2026-02-01
- Initial release
- 42 Foreign Keys
- 51 Indexes
