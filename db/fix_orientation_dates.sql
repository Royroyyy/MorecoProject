-- Run this in phpMyAdmin → moreco_db → Import tab
-- Updates orientation schedule dates to future dates

UPDATE orientation_schedules SET scheduled_date = DATE_ADD(CURDATE(), INTERVAL 7 DAY)  WHERE id = 1;
UPDATE orientation_schedules SET scheduled_date = DATE_ADD(CURDATE(), INTERVAL 14 DAY) WHERE id = 2;
UPDATE orientation_schedules SET scheduled_date = DATE_ADD(CURDATE(), INTERVAL 21 DAY) WHERE id = 3;

-- Insert fresh schedules if none exist
INSERT INTO orientation_schedules (title, scheduled_date, scheduled_time, location, max_slots, is_active)
SELECT * FROM (SELECT
  'New Member Orientation — Batch A', DATE_ADD(CURDATE(), INTERVAL 7 DAY),  '09:00:00', 'MORECO Main Hall, Morong Rizal', 20, 1
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM orientation_schedules LIMIT 1);
