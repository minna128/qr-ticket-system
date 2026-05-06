-- Migration: Add Games Selection Feature
-- World Play QR Ticketing System
-- Run this SQL to add games functionality to your existing database

-- Create games table
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

-- Create ticket_games junction table
CREATE TABLE IF NOT EXISTS ticket_games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(20) NOT NULL,
    game_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket (ticket_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id),
    FOREIGN KEY (game_id) REFERENCES games(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default games
INSERT INTO games (name, description, price, icon, category, display_order, is_active) VALUES
('VR Racing Simulator', 'Immersive virtual reality racing experience', 500.00, '🏎️', 'VR', 1, 1),
('Zombie Shooter VR', 'Fight zombies in virtual reality', 600.00, '🧟', 'VR', 2, 1),
('Space Explorer VR', 'Explore the galaxy in VR', 550.00, '🚀', 'VR', 3, 1),
('Dance Machine', 'Dance to your favorite music', 300.00, '💃', 'Arcade', 4, 1),
('Basketball Hoops', 'Test your basketball skills', 250.00, '🏀', 'Sports', 5, 1),
('Air Hockey', 'Classic air hockey table', 200.00, '🏒', 'Arcade', 6, 1),
('Racing Simulator', 'Formula 1 racing experience', 450.00, '🏎️', 'Racing', 7, 1),
('Laser Tag Arena', 'Team-based laser tag battles', 400.00, '🔫', 'Action', 8, 1),
('Bowling Lane', 'Strike! Mini bowling experience', 350.00, '🎳', 'Sports', 9, 1),
('Claw Machine', 'Win prizes from the claw machine', 150.00, '🧸', 'Arcade', 10, 1);
