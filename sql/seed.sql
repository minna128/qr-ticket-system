-- World Play QR-Based Smart Kiosk Ticketing System
-- Seed Data

USE worldplay;

-- Default pricing tiers
INSERT INTO pricing (duration_minutes, price, extra_per_minute, label, is_active) VALUES
(30, 800.00, 25.00, '30 Minutes - Standard', 1),
(60, 1500.00, 25.00, '60 Minutes - Regular', 1),
(90, 2000.00, 25.00, '90 Minutes - Extended', 1),
(120, 2500.00, 25.00, '120 Minutes - Premium', 1);

-- Default admin account (password: admin123)
INSERT INTO admins (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'superadmin');

-- Default staff accounts (password: staff123)
INSERT INTO staff (name, username, password_hash, role) VALUES
('Entry Staff', 'entry_staff', '$2y$10$Rl3H5YVxvCgGh0g5PfTJXOqFzx0vN3pVxP6YdJhKQo7sRlEaFyJOe', 'entry'),
('Exit Staff', 'exit_staff', '$2y$10$Rl3H5YVxvCgGh0g5PfTJXOqFzx0vN3pVxP6YdJhKQo7sRlEaFyJOe', 'exit'),
('General Staff', 'staff', '$2y$10$Rl3H5YVxvCgGh0g5PfTJXOqFzx0vN3pVxP6YdJhKQo7sRlEaFyJOe', 'both');

-- Default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('venue_name', 'World Play', 'Name of the entertainment venue'),
('currency', 'LKR', 'Currency code for pricing display'),
('timezone', 'Asia/Colombo', 'Server timezone setting'),
('overstay_grace_minutes', '2', 'Grace period in minutes before overstay charges apply'),
('sms_enabled', '1', 'Enable SMS notifications (1=yes, 0=no)'),
('max_tickets_per_phone_per_hour', '5', 'Maximum tickets per phone number per hour'),
('reminder_10min_enabled', '1', 'Enable 10-minute reminder SMS'),
('reminder_5min_enabled', '1', 'Enable 5-minute reminder SMS'),
('supervisor_alerts_enabled', '0', 'Enable floor supervisor alerts via SMS (1=yes, 0=no)'),
('supervisor_phone', '', 'Floor supervisor phone number (used for system alerts)');

-- Default games with pricing
INSERT INTO games (name, description, price, icon, category, display_order, is_active) VALUES
('VR Racing Simulator', 'Immersive virtual reality racing experience', 500.00, '\U0001f3ce\ufe0f', 'VR', 1, 1),
('Zombie Shooter VR', 'Fight zombies in virtual reality', 600.00, '\U0001f9df', 'VR', 2, 1),
('Space Explorer VR', 'Explore the galaxy in VR', 550.00, '\U0001f680', 'VR', 3, 1),
('Dance Machine', 'Dance to your favorite music', 300.00, '\U0001f483', 'Arcade', 4, 1),
('Basketball Hoops', 'Test your basketball skills', 250.00, '\U0001f3c0', 'Sports', 5, 1),
('Air Hockey', 'Classic air hockey table', 200.00, '\U0001f3d2', 'Arcade', 6, 1),
('Racing Simulator', 'Formula 1 racing experience', 450.00, '\U0001f3ce\ufe0f', 'Racing', 7, 1),
('Laser Tag Arena', 'Team-based laser tag battles', 400.00, '\U0001f52b', 'Action', 8, 1),
('Bowling Lane', 'Strike! Mini bowling experience', 350.00, '\U0001f3b3', 'Sports', 9, 1),
('Claw Machine', 'Win prizes from the claw machine', 150.00, '\U0001f9f8', 'Arcade', 10, 1);
