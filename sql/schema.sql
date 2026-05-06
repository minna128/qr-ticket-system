-- World Play QR-Based Smart Kiosk Ticketing System
-- Database Schema

CREATE DATABASE IF NOT EXISTS worldplay;
USE worldplay;

-- Pricing table: ticket duration options
CREATE TABLE IF NOT EXISTS pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    duration_minutes INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    extra_per_minute DECIMAL(10,2) NOT NULL DEFAULT 25.00,
    label VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tickets table: core ticket records
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    pricing_id INT NOT NULL,
    duration_minutes INT NOT NULL,
    payment_method ENUM('Cash','Card') NOT NULL,
    payment_status ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    qr_code_path VARCHAR(255) DEFAULT NULL,
    status ENUM('not_activated','active','expired','completed') NOT NULL DEFAULT 'not_activated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_phone (phone),
    INDEX idx_created (created_at),
    FOREIGN KEY (pricing_id) REFERENCES pricing(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Staff table: entry/exit staff accounts
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('entry','exit','both') NOT NULL DEFAULT 'both',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessions table: active/completed play sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL,
    entry_time DATETIME NOT NULL,
    expected_exit_time DATETIME NOT NULL,
    exit_time DATETIME DEFAULT NULL,
    overstay_minutes INT NOT NULL DEFAULT 0,
    extra_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    staff_entry_id INT DEFAULT NULL,
    staff_exit_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_ticket (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id),
    FOREIGN KEY (staff_entry_id) REFERENCES staff(id),
    FOREIGN KEY (staff_exit_id) REFERENCES staff(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments table: all financial transactions
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('initial','overstay') NOT NULL,
    payment_method ENUM('Cash','Card') NOT NULL,
    confirmed_by INT DEFAULT NULL,
    confirmed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id),
    FOREIGN KEY (confirmed_by) REFERENCES staff(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table: admin dashboard users
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
    last_login DATETIME DEFAULT NULL,
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SMS Logs table: audit trail for all SMS
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('purchase','activation','reminder_10min','reminder_5min','expiry','exit') NOT NULL,
    status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
    message_id VARCHAR(255) DEFAULT NULL COMMENT 'Text.lk message ID or response data',
    error_message TEXT DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    INDEX idx_type (type),
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings table: key-value system configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Games table: available games with pricing
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    icon VARCHAR(50) DEFAULT NULL COMMENT 'Emoji or icon identifier',
    category VARCHAR(50) DEFAULT NULL COMMENT 'e.g., VR, Arcade, Racing, Sports',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ticket games junction table: which games are included in a ticket
CREATE TABLE IF NOT EXISTS ticket_games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL,
    game_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id),
    FOREIGN KEY (game_id) REFERENCES games(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
