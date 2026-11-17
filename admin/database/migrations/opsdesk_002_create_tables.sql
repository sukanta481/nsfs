-- OpsDesk 002: new tables
-- Spec: opsdesk/docs/01-schema-proposal.md (B2-B6). Safe to re-run (IF NOT EXISTS).
--
-- Ledger tables (marked APPEND-ONLY) are never UPDATEd or DELETEd by code.
-- Corrections are a new row of type 'reversal' with reversal_of_id = original id.
-- created_by holds $_SESSION['user_id'] (tbl_users.user_id, or tbl_administrator.id for
-- legacy admins), so it has no foreign key; created_by_name is stored like docket_details.
-- Money is DECIMAL(12,2); all arithmetic is done in PHP as integer paise.

-- ---------------------------------------------------------------------------
-- B2. Staff types (type_name must equal tbl_staff.staff_role text)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_ops_staff_types (
    type_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    activity_frequency ENUM('daily','weekly') NOT NULL DEFAULT 'daily',
    sort_order INT(11) NOT NULL DEFAULT 0,
    active_status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_type_name (type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- B3. Attendance (no row for a day = absent)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_ops_attendance (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_id INT(11) NOT NULL,
    att_date DATE NOT NULL,
    status ENUM('present','paid_leave','week_off','absent') NOT NULL,
    remarks VARCHAR(255) NULL,
    marked_by INT(11) NULL,
    marked_by_name VARCHAR(255) NULL,
    marked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by INT(11) NULL,
    updated_by_name VARCHAR(255) NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_staff_date (staff_id, att_date),
    KEY idx_att_date (att_date),
    CONSTRAINT fk_ops_att_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salary history. APPEND-ONLY. Payroll uses the revision effective on the 1st of the month.
CREATE TABLE IF NOT EXISTS tbl_ops_salary_revisions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_id INT(11) NOT NULL,
    monthly_salary DECIMAL(12,2) NOT NULL,
    effective_from DATE NOT NULL,
    note VARCHAR(255) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_staff_effective (staff_id, effective_from),
    CONSTRAINT chk_ops_salary_nonneg CHECK (monthly_salary >= 0),
    CONSTRAINT fk_ops_salrev_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One payroll run per month for the whole company
CREATE TABLE IF NOT EXISTS tbl_ops_payroll_runs (
    run_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    pay_month DATE NOT NULL COMMENT 'Always the 1st of the month',
    status ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    finalized_by INT(11) NULL,
    finalized_by_name VARCHAR(255) NULL,
    finalized_at DATETIME NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pay_month (pay_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salary slip snapshot. APPEND-ONLY once the run is finalized.
CREATE TABLE IF NOT EXISTS tbl_ops_salary_slips (
    slip_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    run_id INT(11) NOT NULL,
    staff_id INT(11) NOT NULL,
    office_id INT(11) NULL COMMENT 'Snapshot of tbl_staff.office_id for branch-wise reports',
    pay_month DATE NOT NULL,
    days_in_month TINYINT NOT NULL,
    present_days TINYINT NOT NULL DEFAULT 0,
    paid_leave_days TINYINT NOT NULL DEFAULT 0,
    week_off_days TINYINT NOT NULL DEFAULT 0,
    absent_days TINYINT NOT NULL DEFAULT 0,
    payable_days TINYINT NOT NULL DEFAULT 0,
    monthly_salary DECIMAL(12,2) NOT NULL,
    earned_amount DECIMAL(12,2) NOT NULL,
    advance_deduction DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    net_payable DECIMAL(12,2) NOT NULL,
    hand_balance_at_close DECIMAL(12,2) NULL COMMENT 'Information only; not added or deducted',
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_run_staff (run_id, staff_id),
    KEY idx_staff_month (staff_id, pay_month),
    CONSTRAINT chk_ops_slip_days CHECK (present_days + paid_leave_days + week_off_days + absent_days = days_in_month),
    CONSTRAINT chk_ops_slip_net CHECK (net_payable >= 0),
    CONSTRAINT fk_ops_slip_run FOREIGN KEY (run_id) REFERENCES tbl_ops_payroll_runs (run_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_slip_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salary advance (borrowed against current month's salary). APPEND-ONLY.
-- Outstanding = sum(advance) - sum(recovery), with reversals undoing their original row.
CREATE TABLE IF NOT EXISTS tbl_ops_staff_advances (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_id INT(11) NOT NULL,
    entry_date DATE NOT NULL,
    entry_type ENUM('advance','recovery','reversal') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    slip_id INT(11) NULL COMMENT 'Set for recoveries made by payroll',
    note VARCHAR(255) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_staff_date (staff_id, entry_date),
    UNIQUE KEY uq_adv_reversal_of (reversal_of_id),
    CONSTRAINT chk_ops_adv_amount CHECK (amount > 0),
    CONSTRAINT chk_ops_adv_reversal CHECK ((entry_type = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_adv_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_adv_slip FOREIGN KEY (slip_id) REFERENCES tbl_ops_salary_slips (slip_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_adv_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_staff_advances (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- B5. Vendors & vehicles (vendors first: trips reference them)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_ops_vendors (
    vendor_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    gstin VARCHAR(20) NULL,
    active_status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_active_status (active_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per vehicle outing (own or rented). Odometer is read per outing.
CREATE TABLE IF NOT EXISTS tbl_ops_vehicle_trips (
    trip_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    trip_date DATE NOT NULL,
    car_id INT(11) NOT NULL,
    office_id INT(11) NULL,
    vendor_id INT(11) NULL COMMENT 'Rented trips only; pre-filled from tbl_car.default_vendor_id',
    manifest_id INT(11) NULL COMMENT 'Optional link to tbl_manifest',
    odo_start INT(11) NULL,
    odo_end INT(11) NULL,
    km_run INT(11) AS (odo_end - odo_start) PERSISTENT,
    out_time TIME NULL,
    in_time TIME NULL,
    route_note VARCHAR(255) NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by INT(11) NULL,
    updated_by_name VARCHAR(255) NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_car_date (car_id, trip_date),
    KEY idx_trip_date (trip_date),
    KEY idx_vendor (vendor_id),
    CONSTRAINT chk_ops_trip_odo CHECK (odo_end IS NULL OR odo_start IS NULL OR odo_end >= odo_start),
    CONSTRAINT fk_ops_trip_car FOREIGN KEY (car_id) REFERENCES tbl_car (car_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_trip_office FOREIGN KEY (office_id) REFERENCES tbl_offices (office_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_trip_vendor FOREIGN KEY (vendor_id) REFERENCES tbl_ops_vendors (vendor_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_trip_manifest FOREIGN KEY (manifest_id) REFERENCES tbl_manifest (manifest_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_ops_trip_crew (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    trip_id INT(11) NOT NULL,
    staff_id INT(11) NOT NULL,
    crew_role ENUM('driver','helper') NOT NULL,
    UNIQUE KEY uq_trip_staff (trip_id, staff_id),
    KEY idx_staff (staff_id),
    CONSTRAINT fk_ops_crew_trip FOREIGN KEY (trip_id) REFERENCES tbl_ops_vehicle_trips (trip_id) ON DELETE CASCADE,
    CONSTRAINT fk_ops_crew_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Own-vehicle costs. APPEND-ONLY.
CREATE TABLE IF NOT EXISTS tbl_ops_vehicle_expenses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    car_id INT(11) NOT NULL,
    expense_date DATE NOT NULL,
    trip_id INT(11) NULL,
    category ENUM('fuel','maintenance','repair','tyre','toll','parking','insurance','permit','other','reversal') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    litres DECIMAL(8,2) NULL,
    odometer INT(11) NULL,
    paid_by_staff_id INT(11) NULL,
    note VARCHAR(255) NULL,
    bill_file VARCHAR(255) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_car_date (car_id, expense_date),
    UNIQUE KEY uq_vexp_reversal_of (reversal_of_id),
    CONSTRAINT chk_ops_vexp_amount CHECK (amount > 0),
    CONSTRAINT chk_ops_vexp_reversal CHECK ((category = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_vexp_car FOREIGN KEY (car_id) REFERENCES tbl_car (car_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_vexp_trip FOREIGN KEY (trip_id) REFERENCES tbl_ops_vehicle_trips (trip_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_vexp_staff FOREIGN KEY (paid_by_staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_vexp_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_vehicle_expenses (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rented-vehicle settlement, per vendor. APPEND-ONLY. Separate from the hand ledger.
-- Vendor balance (we owe) = actual_expense + receipt - advance - payment, reversals undo their original.
CREATE TABLE IF NOT EXISTS tbl_ops_rental_ledger (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT(11) NOT NULL,
    car_id INT(11) NULL,
    trip_id INT(11) NULL,
    entry_date DATE NOT NULL,
    entry_type ENUM('advance','actual_expense','payment','receipt','reversal') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    note VARCHAR(255) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_vendor_date (vendor_id, entry_date),
    KEY idx_trip (trip_id),
    UNIQUE KEY uq_rent_reversal_of (reversal_of_id),
    CONSTRAINT chk_ops_rent_amount CHECK (amount > 0),
    CONSTRAINT chk_ops_rent_reversal CHECK ((entry_type = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_rent_vendor FOREIGN KEY (vendor_id) REFERENCES tbl_ops_vendors (vendor_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_rent_car FOREIGN KEY (car_id) REFERENCES tbl_car (car_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_rent_trip FOREIGN KEY (trip_id) REFERENCES tbl_ops_vehicle_trips (trip_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_rent_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_rental_ledger (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- B4. Staff hand-balance ledger (road-expense allowance). APPEND-ONLY.
-- Balance = sum(direction * amount); positive = staff holds company cash.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_ops_hand_ledger (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_id INT(11) NOT NULL,
    entry_date DATE NOT NULL,
    entry_type ENUM('given','expense','reversal') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    direction TINYINT NOT NULL COMMENT '+1 given, -1 expense; a reversal carries the opposite of its original',
    category VARCHAR(50) NULL COMMENT 'toll, petrol, repair, parking, food, loading...',
    note VARCHAR(255) NULL,
    receipt_file VARCHAR(255) NULL,
    trip_id INT(11) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_staff_date (staff_id, entry_date),
    UNIQUE KEY uq_hand_reversal_of (reversal_of_id),
    CONSTRAINT chk_ops_hand_amount CHECK (amount > 0),
    CONSTRAINT chk_ops_hand_direction CHECK (direction IN (1, -1)),
    CONSTRAINT chk_ops_hand_type_dir CHECK (
        (entry_type = 'given' AND direction = 1) OR
        (entry_type = 'expense' AND direction = -1) OR
        (entry_type = 'reversal')
    ),
    CONSTRAINT chk_ops_hand_reversal CHECK ((entry_type = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_hand_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_hand_trip FOREIGN KEY (trip_id) REFERENCES tbl_ops_vehicle_trips (trip_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_hand_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_hand_ledger (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- B6. Activity log (separate from attendance)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tbl_ops_activity_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_id INT(11) NOT NULL,
    activity_date DATE NOT NULL,
    period ENUM('daily','weekly') NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NULL,
    ref_type VARCHAR(30) NULL COMMENT 'docket, manifest, trip',
    ref_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_staff_date (staff_id, activity_date),
    KEY idx_ref (ref_type, ref_id),
    CONSTRAINT fk_ops_act_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- tbl_car.default_vendor_id -> tbl_ops_vendors (added after the vendors table exists)
SET @fk_exists := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                   WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_car'
                     AND CONSTRAINT_NAME = 'fk_ops_car_default_vendor');
SET @sql := IF(@fk_exists = 0,
    'ALTER TABLE tbl_car ADD CONSTRAINT fk_ops_car_default_vendor FOREIGN KEY (default_vendor_id) REFERENCES tbl_ops_vendors (vendor_id) ON DELETE SET NULL',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
