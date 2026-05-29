@echo off
echo Resetting demo tickets...
C:\xampp\mysql\bin\mysql.exe -u root -e "DELETE FROM sessions WHERE ticket_id LIKE 'WP-DEMO%%' OR ticket_id = 'WP-TEST01'; UPDATE tickets SET status = 'not_activated' WHERE ticket_id LIKE 'WP-DEMO%%' OR ticket_id = 'WP-TEST01';" worldplay
echo Done! All demo tickets reset.