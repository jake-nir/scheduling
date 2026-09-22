# PHASE 2 — DATABASE

Status: PLANNED (do not build until requested)

---

## Scope

Create the complete normalized schema for all 21 tables plus seed data.
Imported into MariaDB via `database\schema.sql` and `database\seed.sql`.
This phase has NO UI — it delivers the data layer only.

## Database

- Name: `duty_scheduling`
- Engine: InnoDB, utf8mb4, foreign keys ON
- Access via PDO (config\database.php from Phase 1)

## Tables & Key Fields

**roles**
id PK AI, role_name VARCHAR(50) UNIQUE, description VARCHAR(255),
permissions LONGTEXT (JSON array of permission keys), is_active TINYINT(1), timestamps

**users**
id PK AI, username VARCHAR(50) UNIQUE, password_hash VARCHAR(255),
full_name VARCHAR(100), email VARCHAR(100), role_id FK→roles,
status ENUM('active','inactive'), last_login DATETIME NULL, timestamps

**ranks**
id PK AI, rank_name VARCHAR(50), rank_abbr VARCHAR(10), rank_order INT,
is_active TINYINT(1), timestamps; UNIQUE(rank_abbr)

**personnel**
id PK AI, service_number VARCHAR(20) UNIQUE, first_name, middle_name NULL,
last_name, suffix NULL, designation VARCHAR(100), unit VARCHAR(100),
contact_number VARCHAR(30) NULL, email VARCHAR(100) NULL,
status ENUM('active','inactive'), rank_id FK→ranks, timestamps

**duties**
id PK AI, duty_code VARCHAR(20) UNIQUE, duty_name VARCHAR(100), description TEXT NULL,
start_time TIME NULL, end_time TIME NULL, personnel_required INT DEFAULT 1,
rotation_type ENUM('none','sequential') DEFAULT 'none', has_subduties TINYINT(1) DEFAULT 0,
sort_order INT, is_active TINYINT(1), timestamps

**duty_subduties**
id PK AI, duty_id FK→duties, subduty_code VARCHAR(20), subduty_name VARCHAR(100),
sort_order INT, is_active TINYINT(1), timestamps; UNIQUE(duty_id, subduty_code)

**duty_reliefs**
id PK AI, subduty_id FK→duty_subduties, relief_name VARCHAR(50), relief_order INT,
sort_order INT, is_active TINYINT(1), timestamps; UNIQUE(subduty_id, relief_order)

**rank_duty_eligibility**
id PK AI, rank_id FK→ranks, duty_id FK→duties, subduty_id FK→duty_subduties NULL,
priority INT, eligibility ENUM('Primary','Alternate') DEFAULT 'Primary',
is_active TINYINT(1), timestamps; UNIQUE(rank_id,duty_id,subduty_id,priority)

**duty_rotation_groups**
id PK AI, duty_id FK→duties, group_name VARCHAR(100), current_cycle INT DEFAULT 0,
current_position INT DEFAULT 0, is_active TINYINT(1), timestamps; UNIQUE(duty_id, group_name)

**duty_rotation_members**
id PK AI, rotation_group_id FK→duty_rotation_groups, personnel_id FK→personnel,
sequence INT, is_active TINYINT(1), timestamps; UNIQUE(rotation_group_id, personnel_id)

**personnel_availability**
id PK AI, personnel_id FK→personnel,
status ENUM('Available','Leave','Schooling','Sick','Official Assignment','Training','Temporarily Unavailable','Other'),
start_date DATE, end_date DATE NULL, reason VARCHAR(255) NULL, remarks TEXT NULL,
created_by FK→users, created_at; UNIQUE(personnel_id, start_date, status)

**schedules**
id PK AI, schedule_date DATE, duty_id FK→duties, subduty_id FK→duty_subduties NULL,
relief_id FK→duty_reliefs NULL, personnel_id FK→personnel,
rotation_group_id FK→duty_rotation_groups NULL, rotation_cycle INT NULL,
source ENUM('auto','manual'), status ENUM('planned','confirmed','overridden') DEFAULT 'planned',
created_by FK→users, timestamps; UNIQUE(schedule_date,duty_id,subduty_id,relief_id,personnel_id)

**schedule_overrides**
id PK AI, schedule_id FK→schedules, personnel_id FK→personnel,
assigned_duty_id FK→duties, normal_duty_id FK→duties NULL,
subduty_id FK NULL, relief_id FK NULL,
override_type ENUM('rank_substitution','sentinel_repeat','rotation_bypass','availability_override'),
reason VARCHAR(255), confirmed_by FK→users, confirmed_at DATETIME, timestamps

**rotation_assignments**
id PK AI, rotation_group_id FK→duty_rotation_groups, personnel_id FK→personnel,
schedule_id FK→schedules NULL, cycle_number INT,
status ENUM('completed','pending','skipped','unavailable') DEFAULT 'pending',
completed_date DATE NULL, notes VARCHAR(255) NULL, timestamps

**plan_of_day**
id PK AI, pod_date DATE, reference_number VARCHAR(50) NULL,
status ENUM('draft','final','cancelled') DEFAULT 'draft', version INT DEFAULT 1,
created_by FK→users, approved_by FK→users NULL, approved_at DATETIME NULL, timestamps

**plan_of_day_items**
id PK AI, pod_id FK→plan_of_day, schedule_id FK→schedules NULL,
duty_id FK→duties, subduty_id FK NULL, relief_id FK NULL, personnel_id FK→personnel,
display_order INT; UNIQUE(pod_id, duty_id, subduty_id, relief_id, personnel_id)

**plan_of_day_revisions**
id PK AI, pod_id FK→plan_of_day, version INT, old_item_id INT NULL,
new_item_id INT NULL, old_personnel_id FK NULL, new_personnel_id FK NULL,
duty_id FK→duties NULL, reason VARCHAR(255), changed_by FK→users, changed_at DATETIME

**audit_logs**
id PK AI, user_id FK→users NULL, username VARCHAR(50), action VARCHAR(50),
module VARCHAR(50), record_type VARCHAR(50), record_id INT NULL,
description VARCHAR(255), ip_address VARCHAR(45), created_at

**settings**
id PK AI, setting_key VARCHAR(50) UNIQUE, setting_value TEXT, description VARCHAR(255)

## Indexes

- Index every FK column.
- Unique: users.username, personnel.service_number, ranks.rank_abbr,
  duties.duty_code, duty_subduties(duty_id,subduty_code),
  duty_reliefs(subduty_id,relief_order),
  rank_duty_eligibility(rank_id,duty_id,subduty_id,priority),
  duty_rotation_groups(duty_id,group_name),
  duty_rotation_members(rotation_group_id,personnel_id),
  personnel_availability(personnel_id,start_date,status),
  schedules(schedule_date,duty_id,subduty_id,relief_id,personnel_id),
  plan_of_day_items(pod_id,duty_id,subduty_id,relief_id,personnel_id), settings.setting_key

## Seed Data (seed.sql)

- Settings: `org_name` (PCG DU SUPPORT UNIT), `org_logo` (empty), `print_orientation`
  (portrait), `rest_gap_hours` (12), `pod_prepared_by`, `pod_reviewed_by`, `pod_approved_by`
- Roles: **Admin** (all permissions), **Scheduler** (see master permission matrix)
- Admin user: username `admin`, password `admin123` (hashed with password_hash)
- Ranks in order: CDR, LCDR, LT, ENS, PO1, PO2, PO3, SN1, SN2, Other Rank
- Duties: CDO(CDO, rotation none), OOD, JOOD, POW(sequential rotation), Sentinel(has_subduties=1)
- Sentinel sub-duties: Duty Armorer (2 reliefs), Duty Lobby (3), Duty Post 1 (3), Duty Post 2 (3)
- Eligibility:
  - POW → PO3(P1 Primary), PO2(P2 Alternate)
  - JOOD → PO2(P1 Primary), PO1(P2 Primary)
  - OOD → ENS(P1 Primary), LT(P2 Primary)
  - CDO → LCDR(P1 Primary), CDR(P2 Primary)
  - Sentinel sub-duties → SN1(P1), SN2(P2), PO3(P3 Alternate)
- POW rotation group "POW Group 1" with 5 PO3 members:
  1. PO3 Juan A Dela Cruz, 2. PO3 Pedro B Santos, 3. PO3 Bryan H Corpuz,
  4. PO3 Carlo M Reyes, 5. PO3 Daniel T Lim
- Sample personnel: SN1 Art Balila, PO2 Juan Dela Cruz, PO3 Bryan Corpuz, ENS Myla Cruz, CDR Jose Bayani, Sn1 Christian Lopez, Sn1 Ryan Bang + full sample set

## Acceptance Criteria

1. `schema.sql` imports without errors into a fresh `duty_scheduling` DB.
2. All 21 tables exist; FKs resolve; ENUM values match the spec.
3. `seed.sql` runs after schema; admin can log in with seeded credentials.
4. No NULL-constraint violations on documented required fields.
5. `duties`/`subduties`/`reliefs` config is fully data-driven (no constants in code).

## Verification Steps

- `mysql -u root < database\schema.sql` then `seed.sql`; check `SHOW TABLES` = 21.
- Run FK/duplicate sanity queries.
- `php -l` any bootstrap used (none expected in this phase beyond config db check).
- Quick CLI test: connect via PDO, count rows per table.