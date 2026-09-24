USE duty_scheduling;

INSERT INTO roles (id, role_name, description, permissions, is_active) VALUES
  (1, 'Admin', 'System administrator with full access.', '["dashboard.view","calendar.view","personnel.view","personnel.manage","availability.view","availability.manage","dutyhistory.view","dutyconfig.manage","rotationconfig.view","schedule.manage","rotation.view","pod.manage","pod.revisions","reports.view","users.manage","audit.view","settings.manage"]', 1),
  (2, 'Scheduler', 'Duty scheduler with operational access.', '["dashboard.view","calendar.view","personnel.view","availability.view","availability.manage","dutyhistory.view","rotationconfig.view","schedule.manage","rotation.view","pod.manage","pod.revisions","reports.view","audit.view"]', 1)
ON DUPLICATE KEY UPDATE
  role_name = VALUES(role_name),
  description = VALUES(description),
  permissions = VALUES(permissions),
  is_active = VALUES(is_active);

INSERT INTO settings (setting_key, setting_value, description) VALUES
  ('org_name', 'PCG DU SUPPORT UNIT', 'Organisation name displayed in the system header.'),
  ('org_logo', '', 'Path or filename to the organisation logo.'),
  ('print_orientation', 'portrait', 'Default print orientation for POD and reports.'),
  ('rest_gap_hours', '12', 'Minimum rest gap between assigned duties.'),
  ('pod_prepared_by', 'Operations', 'Prepared by label for POD documents.'),
  ('pod_reviewed_by', 'Duty Supervisor', 'Reviewed by label for POD documents.'),
  ('pod_approved_by', 'Commanding Officer', 'Approved by label for POD documents.')
ON DUPLICATE KEY UPDATE
  setting_value = VALUES(setting_value),
  description = VALUES(description);

INSERT INTO users (id, username, password_hash, full_name, email, role_id, status) VALUES
  (1, 'admin', '$2y$10$9nVjnalLIV6Bp0jz4fLFG.LSbGVAYB2d8kZMpdKqhE1B04hwvi9QK', 'System Administrator', 'admin@example.com', 1, 'active'),
  (2, 'scheduler', '$2y$10$9nVjnalLIV6Bp0jz4fLFG.LSbGVAYB2d8kZMpdKqhE1B04hwvi9QK', 'Duty Scheduler', 'scheduler@example.com', 2, 'active')
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  full_name = VALUES(full_name),
  email = VALUES(email),
  role_id = VALUES(role_id),
  status = VALUES(status);

INSERT INTO ranks (id, rank_name, rank_abbr, rank_order, is_active) VALUES
  (1, 'CDR', 'CDR', 1, 1),
  (2, 'LCDR', 'LCDR', 2, 1),
  (3, 'LT', 'LT', 3, 1),
  (4, 'ENS', 'ENS', 4, 1),
  (5, 'PO1', 'PO1', 5, 1),
  (6, 'PO2', 'PO2', 6, 1),
  (7, 'PO3', 'PO3', 7, 1),
  (8, 'SN1', 'SN1', 8, 1),
  (9, 'SN2', 'SN2', 9, 1),
  (10, 'Other Rank', 'OR', 10, 1)
ON DUPLICATE KEY UPDATE
  rank_name = VALUES(rank_name),
  rank_abbr = VALUES(rank_abbr),
  rank_order = VALUES(rank_order),
  is_active = VALUES(is_active);

INSERT INTO personnel (id, service_number, first_name, middle_name, last_name, suffix, designation, unit, contact_number, email, status, rank_id) VALUES
  (1, 'P-001', 'Juan', 'A', 'Dela Cruz', NULL, 'Duty Officer', 'PCG DU Support Unit', '0917-000-0001', 'juan.delacruz@example.com', 'active', 7),
  (2, 'P-002', 'Pedro', 'B', 'Santos', NULL, 'Duty Officer', 'PCG DU Support Unit', '0917-000-0002', 'pedro.santos@example.com', 'active', 7),
  (3, 'P-003', 'Bryan', 'H', 'Corpuz', NULL, 'Duty Officer', 'PCG DU Support Unit', '0917-000-0003', 'bryan.corpuz@example.com', 'active', 7),
  (4, 'P-004', 'Carlo', 'M', 'Reyes', NULL, 'Duty Officer', 'PCG DU Support Unit', '0917-000-0004', 'carlo.reyes@example.com', 'active', 7),
  (5, 'P-005', 'Daniel', 'T', 'Lim', NULL, 'Duty Officer', 'PCG DU Support Unit', '0917-000-0005', 'daniel.lim@example.com', 'active', 7),
  (6, 'P-006', 'Juan', 'D', 'Dela Cruz', NULL, 'Operations Assistant', 'PCG DU Support Unit', '0917-000-0006', 'juan2.delacruz@example.com', 'active', 6),
  (7, 'P-007', 'Art', NULL, 'Balila', NULL, 'Sentinel Specialist', 'PCG DU Support Unit', '0917-000-0007', 'art.balila@example.com', 'active', 8),
  (8, 'P-008', 'Myla', NULL, 'Cruz', NULL, 'Operations Analyst', 'PCG DU Support Unit', '0917-000-0008', 'myla.cruz@example.com', 'active', 4),
  (9, 'P-009', 'Jose', NULL, 'Bayani', NULL, 'Command Duty Officer', 'PCG DU Support Unit', '0917-000-0009', 'jose.bayani@example.com', 'active', 1),
  (10, 'P-010', 'Christian', NULL, 'Lopez', NULL, 'Sentinel Specialist', 'PCG DU Support Unit', '0917-000-0010', 'christian.lopez@example.com', 'active', 8),
  (11, 'P-011', 'Ryan', NULL, 'Bang', NULL, 'Sentinel Specialist', 'PCG DU Support Unit', '0917-000-0011', 'ryan.bang@example.com', 'active', 8)
ON DUPLICATE KEY UPDATE
  first_name = VALUES(first_name),
  middle_name = VALUES(middle_name),
  last_name = VALUES(last_name),
  suffix = VALUES(suffix),
  designation = VALUES(designation),
  unit = VALUES(unit),
  contact_number = VALUES(contact_number),
  email = VALUES(email),
  status = VALUES(status),
  rank_id = VALUES(rank_id);

INSERT INTO duties (id, duty_code, duty_name, description, start_time, end_time, personnel_required, rotation_type, has_subduties, sort_order, is_active) VALUES
  (1, 'CDO', 'Command Duty Officer', 'Command duty oversight and leadership.', '08:00:00', '12:00:00', 1, 'none', 0, 1, 1),
  (2, 'OOD', 'Officer of the Day', 'Daily officer watch rotation.', '12:00:00', '16:00:00', 1, 'none', 0, 2, 1),
  (3, 'JOOD', 'Junior Officer of the Day', 'Junior watch duty assignment.', '16:00:00', '20:00:00', 1, 'none', 0, 3, 1),
  (4, 'POW', 'Port Operations Watch', 'Sequential duty rotation for port operations watch.', '20:00:00', '23:59:00', 1, 'sequential', 0, 4, 1),
  (5, 'SENT', 'Sentinel', 'Sentinel duty with sub-duties and reliefs.', '00:00:00', '23:59:00', 1, 'none', 1, 5, 1)
ON DUPLICATE KEY UPDATE
  duty_name = VALUES(duty_name),
  description = VALUES(description),
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  personnel_required = VALUES(personnel_required),
  rotation_type = VALUES(rotation_type),
  has_subduties = VALUES(has_subduties),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active);

INSERT INTO duty_subduties (id, duty_id, subduty_code, subduty_name, sort_order, is_active) VALUES
  (1, 5, 'ARM', 'Duty Armorer', 1, 1),
  (2, 5, 'LOB', 'Duty Lobby', 2, 1),
  (3, 5, 'P1', 'Duty Post 1', 3, 1),
  (4, 5, 'P2', 'Duty Post 2', 4, 1)
ON DUPLICATE KEY UPDATE
  duty_id = VALUES(duty_id),
  subduty_code = VALUES(subduty_code),
  subduty_name = VALUES(subduty_name),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active);

INSERT INTO duty_reliefs (id, subduty_id, relief_name, relief_order, sort_order, is_active) VALUES
  (1, 1, 'Armorer Relief 1', 1, 1, 1),
  (2, 1, 'Armorer Relief 2', 2, 2, 1),
  (3, 2, 'Lobby Relief 1', 1, 1, 1),
  (4, 2, 'Lobby Relief 2', 2, 2, 1),
  (5, 2, 'Lobby Relief 3', 3, 3, 1),
  (6, 3, 'Post 1 Relief 1', 1, 1, 1),
  (7, 3, 'Post 1 Relief 2', 2, 2, 1),
  (8, 3, 'Post 1 Relief 3', 3, 3, 1),
  (9, 4, 'Post 2 Relief 1', 1, 1, 1),
  (10, 4, 'Post 2 Relief 2', 2, 2, 1),
  (11, 4, 'Post 2 Relief 3', 3, 3, 1)
ON DUPLICATE KEY UPDATE
  subduty_id = VALUES(subduty_id),
  relief_name = VALUES(relief_name),
  relief_order = VALUES(relief_order),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active);

INSERT INTO rank_duty_eligibility (id, rank_id, duty_id, subduty_id, priority, eligibility, is_active) VALUES
  (1, 7, 4, NULL, 1, 'Primary', 1),
  (2, 6, 4, NULL, 2, 'Alternate', 1),
  (3, 6, 3, NULL, 1, 'Primary', 1),
  (4, 5, 3, NULL, 2, 'Primary', 1),
  (5, 4, 2, NULL, 1, 'Primary', 1),
  (6, 3, 2, NULL, 2, 'Primary', 1),
  (7, 2, 1, NULL, 1, 'Primary', 1),
  (8, 1, 1, NULL, 2, 'Primary', 1),
  (9, 8, 5, 1, 1, 'Primary', 1),
  (10, 9, 5, 1, 2, 'Primary', 1),
  (11, 7, 5, 1, 3, 'Alternate', 1),
  (12, 8, 5, 2, 1, 'Primary', 1),
  (13, 9, 5, 2, 2, 'Primary', 1),
  (14, 7, 5, 2, 3, 'Alternate', 1),
  (15, 8, 5, 3, 1, 'Primary', 1),
  (16, 9, 5, 3, 2, 'Primary', 1),
  (17, 7, 5, 3, 3, 'Alternate', 1),
  (18, 8, 5, 4, 1, 'Primary', 1),
  (19, 9, 5, 4, 2, 'Primary', 1),
  (20, 7, 5, 4, 3, 'Alternate', 1)
ON DUPLICATE KEY UPDATE
  rank_id = VALUES(rank_id),
  duty_id = VALUES(duty_id),
  subduty_id = VALUES(subduty_id),
  priority = VALUES(priority),
  eligibility = VALUES(eligibility),
  is_active = VALUES(is_active);

INSERT INTO duty_rotation_groups (id, duty_id, group_name, current_cycle, current_position, is_active) VALUES
  (1, 4, 'POW Group 1', 1, 0, 1)
ON DUPLICATE KEY UPDATE
  duty_id = VALUES(duty_id),
  group_name = VALUES(group_name),
  current_cycle = VALUES(current_cycle),
  current_position = VALUES(current_position),
  is_active = VALUES(is_active);

INSERT INTO duty_rotation_members (id, rotation_group_id, personnel_id, sequence, is_active) VALUES
  (1, 1, 1, 1, 1),
  (2, 1, 2, 2, 1),
  (3, 1, 3, 3, 1),
  (4, 1, 4, 4, 1),
  (5, 1, 5, 5, 1)
ON DUPLICATE KEY UPDATE
  rotation_group_id = VALUES(rotation_group_id),
  personnel_id = VALUES(personnel_id),
  sequence = VALUES(sequence),
  is_active = VALUES(is_active);

INSERT INTO personnel_availability (id, personnel_id, status, start_date, end_date, reason, remarks, created_by) VALUES
  (1, 6, 'Leave', '2026-09-25', '2026-09-30', 'Approved annual leave', 'On approved leave until end of the month.', 1)
ON DUPLICATE KEY UPDATE
  personnel_id = VALUES(personnel_id),
  status = VALUES(status),
  start_date = VALUES(start_date),
  end_date = VALUES(end_date),
  reason = VALUES(reason),
  remarks = VALUES(remarks),
  created_by = VALUES(created_by);
