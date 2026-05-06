-- Migration: Replace Twilio with Text.lk
-- World Play QR Ticketing System
-- Run this SQL to update your existing database

-- Rename the twilio_sid column to message_id
ALTER TABLE sms_logs 
CHANGE COLUMN twilio_sid message_id VARCHAR(255) DEFAULT NULL COMMENT 'Text.lk message ID or response data';
