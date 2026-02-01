#!/bin/bash

# =====================================================
# Database Improvements Summary Script
# Data: 2026-02-01
# =====================================================

echo "╔════════════════════════════════════════════════════╗"
echo "║     DATABASE IMPROVEMENTS SUMMARY                  ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Colori
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verifică dacă există fișierul de migrare
if [ ! -f "migrations/001_add_foreign_keys_and_indexes.sql" ]; then
    echo -e "${RED}✗ Migration file not found!${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Migration files found${NC}"
echo ""

# Număr Foreign Keys
FK_COUNT=$(grep -c "ADD CONSTRAINT.*FOREIGN KEY" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "${BLUE}Foreign Keys to be added:${NC} ${GREEN}${FK_COUNT}${NC}"

# Număr Indexes
INDEX_COUNT=$(grep -c "ADD INDEX" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "${BLUE}Indexes to be added:${NC} ${GREEN}${INDEX_COUNT}${NC}"

echo ""
echo "════════════════════════════════════════════════════"
echo ""

# Breakdown pe categorii
echo -e "${YELLOW}Foreign Keys by Category:${NC}"
echo ""

# Client Relations
CLIENT_FK=$(grep -c "fk.*client" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Client Relations: ${GREEN}${CLIENT_FK}${NC}"

# User Relations
USER_FK=$(grep -c "fk.*user\|fk.*utilizator" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • User Relations: ${GREEN}${USER_FK}${NC}"

# Billing Relations
BILLING_FK=$(grep -c "fk.*factura\|fk.*efactura" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Billing Relations: ${GREEN}${BILLING_FK}${NC}"

# E-Learning Relations
ELEARNING_FK=$(grep -c "fk.*enrollment\|fk.*lesson\|fk.*test\|fk.*answer\|fk.*question\|fk.*schedule\|fk.*course" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • E-Learning Relations: ${GREEN}${ELEARNING_FK}${NC}"

# Shop Relations
SHOP_FK=$(grep -c "fk.*magazin\|fk.*produs\|fk.*comanda\|fk.*articol\|fk.*company" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Shop Relations: ${GREEN}${SHOP_FK}${NC}"

# Sales Relations
SALES_FK=$(grep -c "fk.*sales\|fk.*prospect\|fk.*programare" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Sales Relations: ${GREEN}${SALES_FK}${NC}"

# Waste Management Relations
WASTE_FK=$(grep -c "fk.*ambalaj\|fk.*deseu" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Waste Management: ${GREEN}${WASTE_FK}${NC}"

# Project Relations
PROJECT_FK=$(grep -c "fk.*proiect\|fk.*activitate\|fk.*status" migrations/001_add_foreign_keys_and_indexes.sql)
echo -e "  • Project Management: ${GREEN}${PROJECT_FK}${NC}"

echo ""
echo "════════════════════════════════════════════════════"
echo ""

echo -e "${YELLOW}Indexes by Category:${NC}"
echo ""

# Client Indexes
CLIENT_IDX=$(grep "ALTER TABLE.*clienti" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • Client Tables: ${GREEN}${CLIENT_IDX}${NC}"

# Billing Indexes
BILLING_IDX=$(grep "ALTER TABLE.*factur" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • Billing Tables: ${GREEN}${BILLING_IDX}${NC}"

# User Indexes
USER_IDX=$(grep "ALTER TABLE.*date_utilizatori\|ALTER TABLE.*site_accounts" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • User Tables: ${GREEN}${USER_IDX}${NC}"

# E-Learning Indexes
ELEARNING_IDX=$(grep "ALTER TABLE.*elearning" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • E-Learning Tables: ${GREEN}${ELEARNING_IDX}${NC}"

# Shop Indexes
SHOP_IDX=$(grep "ALTER TABLE.*magazin" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • Shop Tables: ${GREEN}${SHOP_IDX}${NC}"

# Sales Indexes
SALES_IDX=$(grep "ALTER TABLE.*sales" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • Sales Tables: ${GREEN}${SALES_IDX}${NC}"

# Logs Indexes
LOG_IDX=$(grep "ALTER TABLE.*log" migrations/001_add_foreign_keys_and_indexes.sql | grep -c "ADD INDEX")
echo -e "  • Logging Tables: ${GREEN}${LOG_IDX}${NC}"

echo ""
echo "════════════════════════════════════════════════════"
echo ""

# File Sizes
echo -e "${YELLOW}File Sizes:${NC}"
echo ""
ls -lh masterdb.sql | awk '{printf "  • masterdb.sql: %s\n", $5}'
ls -lh masterdb_improved.sql 2>/dev/null | awk '{printf "  • masterdb_improved.sql: %s\n", $5}' || echo -e "  • masterdb_improved.sql: ${RED}Not generated${NC}"
ls -lh migrations/001_add_foreign_keys_and_indexes.sql | awk '{printf "  • Migration script: %s\n", $5}'

echo ""
echo "════════════════════════════════════════════════════"
echo ""

# Benefits
echo -e "${YELLOW}Expected Benefits:${NC}"
echo ""
echo -e "  ${GREEN}✓${NC} Referential integrity guaranteed"
echo -e "  ${GREEN}✓${NC} Automatic cascade deletes"
echo -e "  ${GREEN}✓${NC} 10-100x faster searches on CUI/Email"
echo -e "  ${GREEN}✓${NC} 5-20x faster billing reports"
echo -e "  ${GREEN}✓${NC} 3-10x faster JOIN queries"
echo -e "  ${GREEN}✓${NC} Orphaned records prevented"
echo ""

# Next Steps
echo "════════════════════════════════════════════════════"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo ""
echo "  1. Review analysis: database_improvements_analysis.md"
echo "  2. Run pre-checks: mysql < migrations/000_pre_migration_checks.sql"
echo "  3. Create backup: mysqldump > backup.sql"
echo "  4. Apply migration: mysql < migrations/001_add_foreign_keys_and_indexes.sql"
echo "  5. Verify results: Check foreign keys and indexes"
echo ""

# Tables affected
TABLES_WITH_FK=$(grep "ALTER TABLE" migrations/001_add_foreign_keys_and_indexes.sql | grep "ADD CONSTRAINT" | awk '{print $3}' | sort -u | wc -l)
TABLES_WITH_IDX=$(grep "ALTER TABLE" migrations/001_add_foreign_keys_and_indexes.sql | grep "ADD INDEX" | awk '{print $3}' | sort -u | wc -l)

echo "════════════════════════════════════════════════════"
echo ""
echo -e "${BLUE}Summary:${NC}"
echo -e "  • Tables with new Foreign Keys: ${GREEN}${TABLES_WITH_FK}${NC}"
echo -e "  • Tables with new Indexes: ${GREEN}${TABLES_WITH_IDX}${NC}"
echo -e "  • Total Foreign Keys: ${GREEN}${FK_COUNT}${NC}"
echo -e "  • Total Indexes: ${GREEN}${INDEX_COUNT}${NC}"
echo ""
echo "════════════════════════════════════════════════════"
