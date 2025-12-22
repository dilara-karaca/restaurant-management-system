-- Migration: MenuCategories tablosuna image_url alanı ekle
ALTER TABLE MenuCategories ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER display_order;
