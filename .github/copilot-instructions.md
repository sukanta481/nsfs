# Copilot Instructions for NSFS Project

## Project Overview
- NSFS is a PHP-based web application for logistics and shipment management.
- The codebase is organized into a flat root structure and an `admin/` directory for backend/admin features.
- Database credentials are loaded from a `.env` file in the project root. Fallbacks are provided in `conn.php`.

## Key Components
- `admin/` — Main backend logic, user management, permissions, and CRUD operations.
- `admin/conn.php` — Central DB connection logic. Reads `.env` for credentials.
- `admin/check_auth.php` — Handles session, authentication, and permission checks. Uses `requirePermission()` and `hasPermission()` for RBAC.
- `admin/documentation/` — Contains technical and user documentation, including database schema and update history.
- `admin/database/` — Migration scripts and database sync utilities. Do not re-run migrations unless setting up a new DB.
- `assets/` — Static files (CSS, JS, images).

## Patterns & Conventions
- All admin pages require authentication via `check_auth.php` at the top.
- Permissions are enforced using `requirePermission('permission_key')`.
- Database access is via the global `$conn` (mysqli). Always check for connection errors.
- Use `require_file_or_die()` for robust file inclusion in admin scripts.
- Environment variables: `.env` in root, keys: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
- Debugging: Add `?debug=1` to URLs for verbose error output in some admin scripts.

## Developer Workflows
- **Database setup:**
  1. Place `.env` in root with DB credentials.
  2. Run migration scripts in `admin/database/migrations/` (only for new DBs).
  3. Use `database_sync.php` for structure verification.
- **Authentication:**
  - Sessions are required for all admin actions.
  - Legacy and new user systems are supported (see `check_auth.php`).
- **Permissions:**
  - Permissions are managed via `tbl_permissions`, `tbl_role_permissions`, and `tbl_roles` tables.
  - Assign permissions in DB or via admin UI.
- **Debugging:**
  - Use `?debug=1` for detailed error logs in admin scripts.
  - Check `debug_add_user.log` or `debug_conn.log` for error traces.

## Integration Points
- Uses PHPMailer for email (see SMTP settings in `.env`).
- Uses `vlucas/phpdotenv` for environment variable loading.

## Examples
- To add a new admin page:
  1. Place PHP file in `admin/`.
  2. Start with `require_file_or_die('check_auth.php');` and `requirePermission('your_permission_key');`.
  3. Use `$conn` for DB access.

- To add a new permission:
  1. Insert into `tbl_permissions`.
  2. Assign to roles in `tbl_role_permissions`.

---
For more, see `admin/documentation/README.md` and `admin/database/README.md`.
