CREATE DATABASE IF NOT EXISTS duty_scheduling
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE duty_scheduling;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS plan_of_day_revisions;
DROP TABLE IF EXISTS plan_of_day_items;
DROP TABLE IF EXISTS plan_of_day;
DROP TABLE IF EXISTS rotation_assignments;
DROP TABLE IF EXISTS schedule_overrides;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS personnel_availability;
DROP TABLE IF EXISTS duty_rotation_members;
DROP TABLE IF EXISTS duty_rotation_groups;
DROP TABLE IF EXISTS rank_duty_eligibility;
DROP TABLE IF EXISTS duty_reliefs;
DROP TABLE IF EXISTS duty_subduties;
DROP TABLE IF EXISTS duties;
DROP TABLE IF EXISTS personnel;
DROP TABLE IF EXISTS ranks;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS audit_logs;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id INT NOT NULL AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    permissions LONGTEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_roles_role_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    role_id INT NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    failed_login_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_username (username),
    KEY idx_users_role_id (role_id),
    CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ranks (
    id INT NOT NULL AUTO_INCREMENT,
    rank_name VARCHAR(50) NOT NULL,
    rank_abbr VARCHAR(10) NOT NULL,
    rank_order INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_ranks_rank_abbr (rank_abbr),
    KEY idx_ranks_rank_order (rank_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personnel (
    id INT NOT NULL AUTO_INCREMENT,
    service_number VARCHAR(20) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50) NULL,
    last_name VARCHAR(50) NOT NULL,
    suffix VARCHAR(20) NULL,
    designation VARCHAR(100) NULL,
    unit VARCHAR(100) NULL,
    contact_number VARCHAR(30) NULL,
    email VARCHAR(100) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    rank_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_personnel_service_number (service_number),
    KEY idx_personnel_rank_id (rank_id),
    KEY idx_personnel_status (status),
    CONSTRAINT fk_personnel_rank_id FOREIGN KEY (rank_id) REFERENCES ranks (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE duties (
    id INT NOT NULL AUTO_INCREMENT,
    duty_code VARCHAR(20) NOT NULL,
    duty_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    personnel_required INT NOT NULL DEFAULT 1,
    rotation_type ENUM('none','sequential') NOT NULL DEFAULT 'none',
    has_subduties TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_duties_duty_code (duty_code),
    KEY idx_duties_rotation_type (rotation_type),
    KEY idx_duties_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE duty_subduties (
    id INT NOT NULL AUTO_INCREMENT,
    duty_id INT NOT NULL,
    subduty_code VARCHAR(20) NOT NULL,
    subduty_name VARCHAR(100) NOT NULL,
    sort_order INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_duty_subduties_duty_code (duty_id, subduty_code),
    KEY idx_duty_subduties_duty_id (duty_id),
    CONSTRAINT fk_duty_subduties_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE duty_reliefs (
    id INT NOT NULL AUTO_INCREMENT,
    subduty_id INT NOT NULL,
    relief_name VARCHAR(50) NOT NULL,
    relief_order INT NOT NULL,
    sort_order INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_duty_reliefs_subduty_order (subduty_id, relief_order),
    KEY idx_duty_reliefs_subduty_id (subduty_id),
    CONSTRAINT fk_duty_reliefs_subduty_id FOREIGN KEY (subduty_id) REFERENCES duty_subduties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rank_duty_eligibility (
    id INT NOT NULL AUTO_INCREMENT,
    rank_id INT NOT NULL,
    duty_id INT NOT NULL,
    subduty_id INT NULL,
    priority INT NOT NULL,
    eligibility ENUM('Primary','Alternate') NOT NULL DEFAULT 'Primary',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_rank_duty_eligibility (rank_id, duty_id, subduty_id, priority),
    KEY idx_rank_duty_rank_id (rank_id),
    KEY idx_rank_duty_duty_id (duty_id),
    KEY idx_rank_duty_subduty_id (subduty_id),
    CONSTRAINT fk_rank_duty_eligibility_rank_id FOREIGN KEY (rank_id) REFERENCES ranks (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_rank_duty_eligibility_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_rank_duty_eligibility_subduty_id FOREIGN KEY (subduty_id) REFERENCES duty_subduties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE duty_rotation_groups (
    id INT NOT NULL AUTO_INCREMENT,
    duty_id INT NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    current_cycle INT NOT NULL DEFAULT 0,
    current_position INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_duty_rotation_groups (duty_id, group_name),
    KEY idx_duty_rotation_groups_duty_id (duty_id),
    CONSTRAINT fk_duty_rotation_groups_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE duty_rotation_members (
    id INT NOT NULL AUTO_INCREMENT,
    rotation_group_id INT NOT NULL,
    personnel_id INT NOT NULL,
    sequence INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_duty_rotation_members (rotation_group_id, personnel_id),
    KEY idx_duty_rotation_members_group_id (rotation_group_id),
    KEY idx_duty_rotation_members_personnel_id (personnel_id),
    CONSTRAINT fk_duty_rotation_members_group_id FOREIGN KEY (rotation_group_id) REFERENCES duty_rotation_groups (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_duty_rotation_members_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personnel_availability (
    id INT NOT NULL AUTO_INCREMENT,
    personnel_id INT NOT NULL,
    status ENUM('Available','Leave','Schooling','Sick','Official Assignment','Training','Temporarily Unavailable','Other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    reason VARCHAR(255) NULL,
    remarks TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_personnel_availability (personnel_id, start_date, status),
    KEY idx_personnel_availability_personnel_id (personnel_id),
    KEY idx_personnel_availability_created_by (created_by),
    CONSTRAINT fk_personnel_availability_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_personnel_availability_created_by FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedules (
    id INT NOT NULL AUTO_INCREMENT,
    schedule_date DATE NOT NULL,
    duty_id INT NOT NULL,
    subduty_id INT NULL,
    relief_id INT NULL,
    personnel_id INT NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    rotation_group_id INT NULL,
    rotation_cycle INT NULL,
    source ENUM('auto','manual') NOT NULL,
    status ENUM('planned','confirmed','overridden') NOT NULL DEFAULT 'planned',
    created_by INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_schedules_assignment (schedule_date, duty_id, subduty_id, relief_id, personnel_id),
    KEY idx_schedules_duty_id (duty_id),
    KEY idx_schedules_subduty_id (subduty_id),
    KEY idx_schedules_relief_id (relief_id),
    KEY idx_schedules_personnel_id (personnel_id),
    KEY idx_schedules_rotation_group_id (rotation_group_id),
    KEY idx_schedules_created_by (created_by),
    CONSTRAINT fk_schedules_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedules_subduty_id FOREIGN KEY (subduty_id) REFERENCES duty_subduties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedules_relief_id FOREIGN KEY (relief_id) REFERENCES duty_reliefs (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedules_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedules_rotation_group_id FOREIGN KEY (rotation_group_id) REFERENCES duty_rotation_groups (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedules_created_by FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedule_overrides (
    id INT NOT NULL AUTO_INCREMENT,
    schedule_id INT NOT NULL,
    personnel_id INT NOT NULL,
    assigned_duty_id INT NOT NULL,
    normal_duty_id INT NULL,
    subduty_id INT NULL,
    relief_id INT NULL,
    override_type ENUM('rank_substitution','sentinel_repeat','rotation_bypass','availability_override') NOT NULL,
    reason VARCHAR(255) NULL,
    confirmed_by INT NULL,
    confirmed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_schedule_overrides_schedule_id (schedule_id),
    KEY idx_schedule_overrides_personnel_id (personnel_id),
    KEY idx_schedule_overrides_assigned_duty_id (assigned_duty_id),
    KEY idx_schedule_overrides_confirmed_by (confirmed_by),
    CONSTRAINT fk_schedule_overrides_schedule_id FOREIGN KEY (schedule_id) REFERENCES schedules (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_assigned_duty_id FOREIGN KEY (assigned_duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_normal_duty_id FOREIGN KEY (normal_duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_subduty_id FOREIGN KEY (subduty_id) REFERENCES duty_subduties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_relief_id FOREIGN KEY (relief_id) REFERENCES duty_reliefs (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_schedule_overrides_confirmed_by FOREIGN KEY (confirmed_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rotation_assignments (
    id INT NOT NULL AUTO_INCREMENT,
    rotation_group_id INT NOT NULL,
    personnel_id INT NOT NULL,
    schedule_id INT NULL,
    cycle_number INT NOT NULL,
    status ENUM('completed','pending','skipped','unavailable') NOT NULL DEFAULT 'pending',
    completed_date DATE NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_rotation_assignments_group_id (rotation_group_id),
    KEY idx_rotation_assignments_personnel_id (personnel_id),
    KEY idx_rotation_assignments_schedule_id (schedule_id),
    CONSTRAINT fk_rotation_assignments_group_id FOREIGN KEY (rotation_group_id) REFERENCES duty_rotation_groups (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_rotation_assignments_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_rotation_assignments_schedule_id FOREIGN KEY (schedule_id) REFERENCES schedules (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plan_of_day (
    id INT NOT NULL AUTO_INCREMENT,
    pod_date DATE NOT NULL,
    reference_number VARCHAR(50) NULL,
    status ENUM('draft','final','cancelled') NOT NULL DEFAULT 'draft',
    version INT NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_plan_of_day_created_by (created_by),
    KEY idx_plan_of_day_approved_by (approved_by),
    CONSTRAINT fk_plan_of_day_created_by FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_approved_by FOREIGN KEY (approved_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plan_of_day_items (
    id INT NOT NULL AUTO_INCREMENT,
    pod_id INT NOT NULL,
    schedule_id INT NULL,
    duty_id INT NOT NULL,
    subduty_id INT NULL,
    relief_id INT NULL,
    personnel_id INT NOT NULL,
    display_order INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_plan_of_day_items (pod_id, duty_id, subduty_id, relief_id, personnel_id),
    KEY idx_plan_of_day_items_pod_id (pod_id),
    KEY idx_plan_of_day_items_schedule_id (schedule_id),
    KEY idx_plan_of_day_items_duty_id (duty_id),
    KEY idx_plan_of_day_items_subduty_id (subduty_id),
    KEY idx_plan_of_day_items_relief_id (relief_id),
    KEY idx_plan_of_day_items_personnel_id (personnel_id),
    CONSTRAINT fk_plan_of_day_items_pod_id FOREIGN KEY (pod_id) REFERENCES plan_of_day (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_items_schedule_id FOREIGN KEY (schedule_id) REFERENCES schedules (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_items_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_items_subduty_id FOREIGN KEY (subduty_id) REFERENCES duty_subduties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_items_relief_id FOREIGN KEY (relief_id) REFERENCES duty_reliefs (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_items_personnel_id FOREIGN KEY (personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plan_of_day_revisions (
    id INT NOT NULL AUTO_INCREMENT,
    pod_id INT NOT NULL,
    version INT NOT NULL,
    old_item_id INT NULL,
    new_item_id INT NULL,
    old_personnel_id INT NULL,
    new_personnel_id INT NULL,
    duty_id INT NULL,
    reason VARCHAR(255) NULL,
    changed_by INT NULL,
    changed_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_plan_of_day_revisions_pod_id (pod_id),
    KEY idx_plan_of_day_revisions_changed_by (changed_by),
    KEY idx_plan_of_day_revisions_duty_id (duty_id),
    CONSTRAINT fk_plan_of_day_revisions_pod_id FOREIGN KEY (pod_id) REFERENCES plan_of_day (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_revisions_duty_id FOREIGN KEY (duty_id) REFERENCES duties (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_revisions_changed_by FOREIGN KEY (changed_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_revisions_old_personnel_id FOREIGN KEY (old_personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plan_of_day_revisions_new_personnel_id FOREIGN KEY (new_personnel_id) REFERENCES personnel (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NULL,
    username VARCHAR(50) NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NULL,
    record_type VARCHAR(50) NULL,
    record_id INT NULL,
    description VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_logs_user_id (user_id),
    KEY idx_audit_logs_action (action),
    CONSTRAINT fk_audit_logs_user_id FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id INT NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_settings_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_settings_setting_key ON settings (setting_key);
CREATE INDEX idx_users_username ON users (username);
CREATE INDEX idx_personnel_service_number ON personnel (service_number);
CREATE INDEX idx_ranks_rank_abbr ON ranks (rank_abbr);
CREATE INDEX idx_duties_duty_code ON duties (duty_code);
CREATE INDEX idx_duty_subduties_duty_code ON duty_subduties (duty_id, subduty_code);
CREATE INDEX idx_duty_reliefs_subduty_order ON duty_reliefs (subduty_id, relief_order);
CREATE INDEX idx_personnel_availability_window ON personnel_availability (personnel_id, start_date, status);
CREATE INDEX idx_schedules_lookup ON schedules (schedule_date, duty_id, subduty_id, relief_id, personnel_id);
CREATE INDEX idx_plan_of_day_items_lookup ON plan_of_day_items (pod_id, duty_id, subduty_id, relief_id, personnel_id);
