DELETE FROM sessions WHERE ticket_id LIKE 'WP-DEMO%' OR ticket_id = 'WP-DEMO01';
UPDATE tickets SET status = 'not_activated' WHERE ticket_id LIKE 'WP-DEMO%' OR ticket_id = 'WP-DEMO01';