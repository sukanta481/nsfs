-- OpsDesk 003: seed staff types and permissions
-- Spec: opsdesk/docs/01-schema-proposal.md (B2, B7). Safe to re-run (INSERT IGNORE).

-- Staff types. type_name must match tbl_staff.staff_role exactly.
INSERT IGNORE INTO tbl_ops_staff_types (type_name, activity_frequency, sort_order) VALUES
    ('Driver',           'daily',  1),
    ('Helper',           'daily',  2),
    ('Delivery Boy',     'daily',  3),
    ('Godown Staff',     'daily',  4),
    ('Office Assistant', 'weekly', 5),
    ('Office Staff',     'weekly', 6),
    ('Accountant',       'weekly', 7),
    ('Manager',          'weekly', 8);

-- Any role already used in tbl_staff but not listed above (defaults to daily)
INSERT IGNORE INTO tbl_ops_staff_types (type_name, activity_frequency, sort_order)
SELECT DISTINCT staff_role, 'daily', 99 FROM tbl_staff WHERE staff_role <> '';

-- Permissions (module OpsDesk)
INSERT IGNORE INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) VALUES
    ('ops_attendance_view',   'View Attendance',            'OpsDesk', 'See daily attendance of staff'),
    ('ops_attendance_mark',   'Mark Attendance',            'OpsDesk', 'Mark or change attendance before payroll is finalized'),
    ('ops_payroll_view',      'View Payroll & Slips',       'OpsDesk', 'See payroll runs and salary slips'),
    ('ops_payroll_run',       'Run Payroll',                'OpsDesk', 'Create and recalculate draft payroll'),
    ('ops_payroll_finalize',  'Finalize Payroll',           'OpsDesk', 'Lock a payroll month and post advance recoveries'),
    ('ops_salary_manage',     'Manage Salaries',            'OpsDesk', 'Add salary revisions'),
    ('ops_advance_manage',    'Manage Salary Advances',     'OpsDesk', 'Give advances and post reversals'),
    ('ops_hand_ledger_view',  'View Hand Balance',          'OpsDesk', 'See staff road-expense passbooks'),
    ('ops_hand_ledger_manage','Manage Hand Balance',        'OpsDesk', 'Record cash given, expenses and reversals'),
    ('ops_vehicle_view',      'View Vehicle Operations',    'OpsDesk', 'See trips, km and vehicle costs'),
    ('ops_vehicle_manage',    'Manage Vehicle Operations',  'OpsDesk', 'Record trips, crew, odometer and expenses'),
    ('ops_rental_manage',     'Manage Rented Vehicles',     'OpsDesk', 'Vendors and rented-vehicle settlement ledger'),
    ('ops_activity_manage',   'Manage Activity Log',        'OpsDesk', 'Record staff activities'),
    ('ops_profile_view',      'View 360 Profiles',          'OpsDesk', 'See staff and vehicle 360 profiles');

-- Grant all OpsDesk permissions to Super Admin (role_id 1). Other roles are assigned from roles.php.
INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id)
SELECT 1, permission_id FROM tbl_permissions WHERE module_name = 'OpsDesk';
