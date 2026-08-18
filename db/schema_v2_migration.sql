-- =============================================
-- MORECO v1.1 → v1.2 Migration
-- Run this in phpMyAdmin AFTER existing schema
-- =============================================

USE moreco_db;

-- Add branch column to announcements
ALTER TABLE announcements
  ADD COLUMN branch VARCHAR(60) DEFAULT 'all' AFTER priority;

-- Add form_url column to benefits (if not exists)
ALTER TABLE benefits
  ADD COLUMN IF NOT EXISTS form_url VARCHAR(500) NULL AFTER emoji;

-- Update existing announcements to 'all' branch
UPDATE announcements SET branch = 'all' WHERE branch IS NULL;

-- Add account_name to withdrawals table
ALTER TABLE withdrawals
  ADD COLUMN IF NOT EXISTS account_name VARCHAR(100) NOT NULL DEFAULT '' AFTER account_number;
