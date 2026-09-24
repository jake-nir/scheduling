-- ---------------------------------------------------------------------------
-- Migration: Default-availability model (2026-09-25)
--
-- Strategy:
--   1. Archive (not destroy) any legacy 'Available' rows. Under the new model
--      personnel are considered available by default, so an explicit
--      'Available' record carries no information. Preserving the rows in an
--      archive table keeps the data recoverable.
--   2. Remove the archived rows from the live table.
--   3. Narrow the status ENUM so only unavailability exception types can be
--      stored. The table name personnel_availability is intentionally kept.
-- ---------------------------------------------------------------------------

USE duty_scheduling;

CREATE TABLE IF NOT EXISTS personnel_availability_archive_20260925 AS
SELECT * FROM personnel_availability WHERE status = 'Available';

DELETE FROM personnel_availability WHERE status = 'Available';

ALTER TABLE personnel_availability
  MODIFY status ENUM('Leave','Schooling','Sick','Official Assignment','Training','Temporarily Unavailable','Other') NOT NULL;