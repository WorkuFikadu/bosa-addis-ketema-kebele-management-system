-- fix_individuals_v2.sql
USE kebele_system;

ALTER TABLE individuals 
ADD COLUMN IF NOT EXISTS mother_full_name VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS father_full_name VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS mother_nat VARCHAR(50) DEFAULT 'Ethiopian',
ADD COLUMN IF NOT EXISTS father_nat VARCHAR(50) DEFAULT 'Ethiopian';
