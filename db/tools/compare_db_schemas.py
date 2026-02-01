#!/usr/bin/env python3
"""
Database Schema Comparison Tool
Compară schema între două baze de date MySQL (master și producție)
Generează raport detaliat și opțional script de migrare

Usage:
    python3 compare_db_schemas.py --master-host localhost --master-db master_db \
                                   --prod-host prod.server.com --prod-db production_db \
                                   --user root --password secret \
                                   --output-report diff_report.txt \
                                   --output-migration migration_script.sql
"""

import argparse
import mysql.connector
from mysql.connector import Error
from collections import defaultdict
import sys

class DatabaseComparator:
    def __init__(self, master_config, prod_config):
        self.master_config = master_config
        self.prod_config = prod_config
        self.master_conn = None
        self.prod_conn = None
        self.differences = defaultdict(list)
        
    def connect(self):
        """Conectează la ambele baze de date"""
        try:
            self.master_conn = mysql.connector.connect(**self.master_config)
            self.prod_conn = mysql.connector.connect(**self.prod_config)
            print("✓ Connected to both databases")
            return True
        except Error as e:
            print(f"✗ Connection error: {e}")
            return False
    
    def disconnect(self):
        """Închide conexiunile"""
        if self.master_conn and self.master_conn.is_connected():
            self.master_conn.close()
        if self.prod_conn and self.prod_conn.is_connected():
            self.prod_conn.close()
    
    def get_tables(self, conn, db_name):
        """Obține lista tabelelor"""
        cursor = conn.cursor()
        cursor.execute(f"""
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = '{db_name}' 
              AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        """)
        tables = [row[0] for row in cursor.fetchall()]
        cursor.close()
        return tables
    
    def get_columns(self, conn, db_name, table_name):
        """Obține coloanele unei tabele"""
        cursor = conn.cursor(dictionary=True)
        cursor.execute(f"""
            SELECT 
                COLUMN_NAME,
                COLUMN_TYPE,
                IS_NULLABLE,
                COLUMN_DEFAULT,
                EXTRA,
                COLUMN_KEY,
                CHARACTER_SET_NAME,
                COLLATION_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '{db_name}'
              AND TABLE_NAME = '{table_name}'
            ORDER BY ORDINAL_POSITION
        """)
        columns = {row['COLUMN_NAME']: row for row in cursor.fetchall()}
        cursor.close()
        return columns
    
    def get_indexes(self, conn, db_name, table_name):
        """Obține indexurile unei tabele"""
        cursor = conn.cursor(dictionary=True)
        cursor.execute(f"""
            SELECT 
                INDEX_NAME,
                GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as columns,
                NON_UNIQUE,
                INDEX_TYPE
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = '{db_name}'
              AND TABLE_NAME = '{table_name}'
              AND INDEX_NAME != 'PRIMARY'
            GROUP BY INDEX_NAME, NON_UNIQUE, INDEX_TYPE
            ORDER BY INDEX_NAME
        """)
        indexes = {row['INDEX_NAME']: row for row in cursor.fetchall()}
        cursor.close()
        return indexes
    
    def get_foreign_keys(self, conn, db_name, table_name):
        """Obține foreign keys unei tabele"""
        cursor = conn.cursor(dictionary=True)
        cursor.execute(f"""
            SELECT 
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc 
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME 
                AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_SCHEMA = '{db_name}'
              AND kcu.TABLE_NAME = '{table_name}'
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY kcu.CONSTRAINT_NAME
        """)
        fks = {row['CONSTRAINT_NAME']: row for row in cursor.fetchall()}
        cursor.close()
        return fks
    
    def compare_tables(self):
        """Compară tabelele între cele două baze"""
        master_tables = set(self.get_tables(self.master_conn, self.master_config['database']))
        prod_tables = set(self.get_tables(self.prod_conn, self.prod_config['database']))
        
        # Tabele lipsă în producție
        missing_in_prod = master_tables - prod_tables
        if missing_in_prod:
            self.differences['missing_tables'].extend(sorted(missing_in_prod))
        
        # Tabele în plus în producție
        extra_in_prod = prod_tables - master_tables
        if extra_in_prod:
            self.differences['extra_tables'].extend(sorted(extra_in_prod))
        
        # Tabele comune - compară structura
        common_tables = master_tables & prod_tables
        return common_tables
    
    def compare_columns(self, table_name):
        """Compară coloanele unei tabele"""
        master_cols = self.get_columns(self.master_conn, self.master_config['database'], table_name)
        prod_cols = self.get_columns(self.prod_conn, self.prod_config['database'], table_name)
        
        master_col_names = set(master_cols.keys())
        prod_col_names = set(prod_cols.keys())
        
        # Coloane lipsă în producție
        missing = master_col_names - prod_col_names
        for col in missing:
            self.differences['missing_columns'].append({
                'table': table_name,
                'column': col,
                'definition': master_cols[col]
            })
        
        # Coloane în plus în producție
        extra = prod_col_names - master_col_names
        for col in extra:
            self.differences['extra_columns'].append({
                'table': table_name,
                'column': col,
                'definition': prod_cols[col]
            })
        
        # Coloane cu diferențe
        for col in master_col_names & prod_col_names:
            master_def = master_cols[col]
            prod_def = prod_cols[col]
            
            differences = []
            if master_def['COLUMN_TYPE'] != prod_def['COLUMN_TYPE']:
                differences.append(f"type: {prod_def['COLUMN_TYPE']} → {master_def['COLUMN_TYPE']}")
            if master_def['IS_NULLABLE'] != prod_def['IS_NULLABLE']:
                differences.append(f"nullable: {prod_def['IS_NULLABLE']} → {master_def['IS_NULLABLE']}")
            if master_def['COLUMN_DEFAULT'] != prod_def['COLUMN_DEFAULT']:
                differences.append(f"default: {prod_def['COLUMN_DEFAULT']} → {master_def['COLUMN_DEFAULT']}")
            if master_def['EXTRA'] != prod_def['EXTRA']:
                differences.append(f"extra: {prod_def['EXTRA']} → {master_def['EXTRA']}")
            
            if differences:
                self.differences['column_differences'].append({
                    'table': table_name,
                    'column': col,
                    'differences': differences
                })
    
    def compare_indexes(self, table_name):
        """Compară indexurile unei tabele"""
        master_indexes = self.get_indexes(self.master_conn, self.master_config['database'], table_name)
        prod_indexes = self.get_indexes(self.prod_conn, self.prod_config['database'], table_name)
        
        master_idx_names = set(master_indexes.keys())
        prod_idx_names = set(prod_indexes.keys())
        
        # Indexuri lipsă în producție
        missing = master_idx_names - prod_idx_names
        for idx in missing:
            self.differences['missing_indexes'].append({
                'table': table_name,
                'index': idx,
                'definition': master_indexes[idx]
            })
        
        # Indexuri în plus în producție
        extra = prod_idx_names - master_idx_names
        for idx in extra:
            self.differences['extra_indexes'].append({
                'table': table_name,
                'index': idx,
                'definition': prod_indexes[idx]
            })
    
    def compare_foreign_keys(self, table_name):
        """Compară foreign keys ale unei tabele"""
        master_fks = self.get_foreign_keys(self.master_conn, self.master_config['database'], table_name)
        prod_fks = self.get_foreign_keys(self.prod_conn, self.prod_config['database'], table_name)
        
        master_fk_names = set(master_fks.keys())
        prod_fk_names = set(prod_fks.keys())
        
        # Foreign keys lipsă în producție
        missing = master_fk_names - prod_fk_names
        for fk in missing:
            self.differences['missing_foreign_keys'].append({
                'table': table_name,
                'constraint': fk,
                'definition': master_fks[fk]
            })
        
        # Foreign keys în plus în producție
        extra = prod_fk_names - master_fk_names
        for fk in extra:
            self.differences['extra_foreign_keys'].append({
                'table': table_name,
                'constraint': fk,
                'definition': prod_fks[fk]
            })
    
    def run_comparison(self):
        """Rulează comparația completă"""
        print("\n" + "="*70)
        print("DATABASE SCHEMA COMPARISON")
        print("="*70)
        print(f"Master: {self.master_config['database']} @ {self.master_config['host']}")
        print(f"Production: {self.prod_config['database']} @ {self.prod_config['host']}")
        print("="*70 + "\n")
        
        # Compară tabele
        print("Comparing tables...")
        common_tables = self.compare_tables()
        print(f"  ✓ Found {len(common_tables)} common tables")
        
        # Compară structura pentru fiecare tabel comun
        for i, table in enumerate(sorted(common_tables), 1):
            print(f"  [{i}/{len(common_tables)}] Analyzing {table}...")
            self.compare_columns(table)
            self.compare_indexes(table)
            self.compare_foreign_keys(table)
        
        print("\n✓ Comparison completed!\n")
    
    def generate_report(self, output_file=None):
        """Generează raport text"""
        lines = []
        lines.append("="*70)
        lines.append("DATABASE SCHEMA COMPARISON REPORT")
        lines.append("="*70)
        lines.append(f"Master: {self.master_config['database']} @ {self.master_config['host']}")
        lines.append(f"Production: {self.prod_config['database']} @ {self.prod_config['host']}")
        lines.append("="*70)
        lines.append("")
        
        # Summary
        total_issues = sum(len(v) for v in self.differences.values())
        lines.append(f"SUMMARY: {total_issues} differences found")
        lines.append("")
        
        # Missing tables
        if self.differences['missing_tables']:
            lines.append(f"1. MISSING TABLES IN PRODUCTION ({len(self.differences['missing_tables'])})")
            lines.append("-"*70)
            for table in self.differences['missing_tables']:
                lines.append(f"  • {table}")
            lines.append("")
        
        # Extra tables
        if self.differences['extra_tables']:
            lines.append(f"2. EXTRA TABLES IN PRODUCTION ({len(self.differences['extra_tables'])})")
            lines.append("-"*70)
            for table in self.differences['extra_tables']:
                lines.append(f"  • {table}")
            lines.append("")
        
        # Missing columns
        if self.differences['missing_columns']:
            lines.append(f"3. MISSING COLUMNS IN PRODUCTION ({len(self.differences['missing_columns'])})")
            lines.append("-"*70)
            for item in self.differences['missing_columns']:
                lines.append(f"  • {item['table']}.{item['column']}")
                lines.append(f"    Type: {item['definition']['COLUMN_TYPE']}")
            lines.append("")
        
        # Column differences
        if self.differences['column_differences']:
            lines.append(f"4. COLUMN DIFFERENCES ({len(self.differences['column_differences'])})")
            lines.append("-"*70)
            for item in self.differences['column_differences']:
                lines.append(f"  • {item['table']}.{item['column']}")
                for diff in item['differences']:
                    lines.append(f"    - {diff}")
            lines.append("")
        
        # Missing indexes
        if self.differences['missing_indexes']:
            lines.append(f"5. MISSING INDEXES IN PRODUCTION ({len(self.differences['missing_indexes'])})")
            lines.append("-"*70)
            for item in self.differences['missing_indexes']:
                lines.append(f"  • {item['table']}.{item['index']}")
                lines.append(f"    Columns: {item['definition']['columns']}")
            lines.append("")
        
        # Missing foreign keys
        if self.differences['missing_foreign_keys']:
            lines.append(f"6. MISSING FOREIGN KEYS IN PRODUCTION ({len(self.differences['missing_foreign_keys'])})")
            lines.append("-"*70)
            for item in self.differences['missing_foreign_keys']:
                fk_def = item['definition']
                lines.append(f"  • {item['table']}.{item['constraint']}")
                lines.append(f"    {fk_def['COLUMN_NAME']} → {fk_def['REFERENCED_TABLE_NAME']}.{fk_def['REFERENCED_COLUMN_NAME']}")
                lines.append(f"    ON DELETE {fk_def['DELETE_RULE']}, ON UPDATE {fk_def['UPDATE_RULE']}")
            lines.append("")
        
        report = "\n".join(lines)
        
        if output_file:
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(report)
            print(f"✓ Report saved to: {output_file}")
        else:
            print(report)
        
        return report
    
    def generate_migration_script(self, output_file):
        """Generează script SQL de migrare"""
        lines = []
        lines.append("-- =====================================================")
        lines.append("-- Auto-Generated Migration Script")
        lines.append("-- Generated based on schema comparison")
        lines.append("-- =====================================================")
        lines.append("")
        lines.append("SET FOREIGN_KEY_CHECKS=0;")
        lines.append("")
        
        # Add missing columns
        if self.differences['missing_columns']:
            lines.append("-- Add missing columns")
            for item in self.differences['missing_columns']:
                col_def = item['definition']
                nullable = "NULL" if col_def['IS_NULLABLE'] == 'YES' else "NOT NULL"
                default = f"DEFAULT {col_def['COLUMN_DEFAULT']}" if col_def['COLUMN_DEFAULT'] else ""
                extra = col_def['EXTRA'] if col_def['EXTRA'] else ""
                
                lines.append(f"ALTER TABLE `{item['table']}` ADD COLUMN `{item['column']}` {col_def['COLUMN_TYPE']} {nullable} {default} {extra};")
            lines.append("")
        
        # Modify different columns
        if self.differences['column_differences']:
            lines.append("-- Modify columns with differences")
            for item in self.differences['column_differences']:
                lines.append(f"-- {item['table']}.{item['column']}: {', '.join(item['differences'])}")
                lines.append(f"-- Manual review recommended for {item['table']}.{item['column']}")
            lines.append("")
        
        # Add missing indexes
        if self.differences['missing_indexes']:
            lines.append("-- Add missing indexes")
            for item in self.differences['missing_indexes']:
                lines.append(f"ALTER TABLE `{item['table']}` ADD INDEX `{item['index']}` ({item['definition']['columns']});")
            lines.append("")
        
        # Add missing foreign keys
        if self.differences['missing_foreign_keys']:
            lines.append("-- Add missing foreign keys")
            for item in self.differences['missing_foreign_keys']:
                fk_def = item['definition']
                lines.append(f"ALTER TABLE `{item['table']}` ADD CONSTRAINT `{item['constraint']}` ")
                lines.append(f"  FOREIGN KEY (`{fk_def['COLUMN_NAME']}`) ")
                lines.append(f"  REFERENCES `{fk_def['REFERENCED_TABLE_NAME']}` (`{fk_def['REFERENCED_COLUMN_NAME']}`) ")
                lines.append(f"  ON DELETE {fk_def['DELETE_RULE']} ON UPDATE {fk_def['UPDATE_RULE']};")
            lines.append("")
        
        lines.append("SET FOREIGN_KEY_CHECKS=1;")
        lines.append("")
        lines.append("-- =====================================================")
        
        script = "\n".join(lines)
        
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(script)
        
        print(f"✓ Migration script saved to: {output_file}")
        return script

def main():
    parser = argparse.ArgumentParser(description='Compare MySQL database schemas')
    parser.add_argument('--master-host', default='localhost', help='Master database host')
    parser.add_argument('--master-db', required=True, help='Master database name')
    parser.add_argument('--prod-host', default='localhost', help='Production database host')
    parser.add_argument('--prod-db', required=True, help='Production database name')
    parser.add_argument('--user', required=True, help='Database user')
    parser.add_argument('--password', required=True, help='Database password')
    parser.add_argument('--master-port', type=int, default=3306, help='Master database port')
    parser.add_argument('--prod-port', type=int, default=3306, help='Production database port')
    parser.add_argument('--output-report', help='Output file for text report')
    parser.add_argument('--output-migration', help='Output file for migration script')
    
    args = parser.parse_args()
    
    master_config = {
        'host': args.master_host,
        'port': args.master_port,
        'database': args.master_db,
        'user': args.user,
        'password': args.password
    }
    
    prod_config = {
        'host': args.prod_host,
        'port': args.prod_port,
        'database': args.prod_db,
        'user': args.user,
        'password': args.password
    }
    
    comparator = DatabaseComparator(master_config, prod_config)
    
    if not comparator.connect():
        sys.exit(1)
    
    try:
        comparator.run_comparison()
        comparator.generate_report(args.output_report)
        
        if args.output_migration:
            comparator.generate_migration_script(args.output_migration)
    finally:
        comparator.disconnect()

if __name__ == '__main__':
    main()
