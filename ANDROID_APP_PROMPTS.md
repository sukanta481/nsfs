# NSFS Admin → Android App — Google AI Studio Prompt Pack

This document is a **sequence of prompts** to paste into Google AI Studio (one at a time)
to build:

- **Part A — a PHP REST API** that lives on your existing Hostinger server and uses the
  **same MySQL database and the same auth tables** as the current admin panel.
- **Part B — a native Flutter Android app** that consumes that API and mirrors the
  current admin layout/flows for **core operations**.
- **Part C — deployment & security** steps.

> **Why an API is required:** A phone app cannot connect to MySQL directly (no DB port is
> exposed, and embedding DB credentials in an APK is unsafe). The app talks HTTPS to the
> PHP API; the PHP API talks to MySQL — exactly like the website does today. Same DB, same
> data, same login accounts.

## How to use this pack
1. Do **Part A first** (the API). The app is useless without it.
2. Feed prompts **one section at a time**. After each, test the output, then paste the next.
3. **Prompt 0 (Context)** below must be pasted **once at the start of every new AI Studio chat**
   so the model has the data model. Then paste the numbered prompt you're working on.
4. Replace `https://northsuperfastservice.com` with your real domain if different.

---

# PROMPT 0 — CONTEXT (paste first, every session)

```
You are helping me extend an existing PHP + MySQL logistics/courier system ("NSFS") and
build a companion native Android app in Flutter. Do NOT redesign the database. Reuse the
existing tables exactly. Here is the ground truth:

TECH/ENV
- Backend: plain PHP (procedural, mysqli) on Hostinger shared hosting. MySQL/MariaDB.
- Existing DB connection file: admin/conn.php exposes a mysqli handle named $conn.
- Existing config loads DB creds from a .env file (do not hardcode credentials).
- Sessions use session_name('pro'). The web uses cookie sessions; the APP will use
  TOKEN (Bearer) auth instead, but must validate against the SAME user tables.

AUTH MODEL (existing)
- New users: table tbl_users (columns include user_id, username, password [hashed with
  PHP password_hash], full_name, role_id, office_id [nullable], and an optional flag for
  "access all offices"). Legacy super admin: table tbl_administrator.
- Roles: tbl_roles. Permissions: tbl_permissions(permission_id, permission_key).
  Role→permission map: tbl_role_permissions(role_id, permission_id).
- A user with role_name = 'Super Admin' (or any legacy tbl_administrator login) has ALL
  permissions. Permission keys used by core ops include: dashboard_view, docket_view,
  docket_view_all, docket_create, special_docket_create, docket_edit, docket_delete,
  docket_status_update, trip_view, manifest_view, manifest_receive, tracking_view,
  tracking_management, vehicle_view, staff_view, client_view, settings_view, user_view.
- ROW-LEVEL SCOPING (must be enforced server-side on every docket query):
  * Office scope: if the user is NOT super admin, has no "access all offices" right, no
    'office_view_all' permission, AND has a non-empty office_id, then restrict results to
    docket_details.office_id = <user office_id>. Otherwise no office restriction.
  * Creator scope: if the user is NOT super admin and does NOT have 'docket_view_all',
    restrict to docket_details.created_by = <user_id>.
- Status-change permission: optional per-user restriction via tbl_user_status_permissions
  joined to tbl_status_hierarchy(status_id, status_name, status_order). If a user has no
  rows there, they may set any status (given docket_status_update). Super admin: any status.

KEY TABLES (columns you will use)
- docket_details: docket_id (PK), doc_no (UNIQUE), trip_group_id, manifest_id,
  service_type, doc_type ENUM('DRS','NON-DRS'), status, created_at, updated_at,
  pickup_datetime, delivery_datetime, company_id, company_name, company_phone,
  company_address, pickup_location, client_id, client_name, client_phone, client_email,
  client_address, delivery_location, car_id, car_number, driver_id, driver_name,
  driver_phone, helper_name, helper_phone, item, box (INT), weight, dimensions, rate,
  amount, invoice_no, eway_bill, office_id, branch_office, reason_of_delay,
  proof_of_delivery (relative file path), current_location, estimated_delivery,
  remarks, special_instructions, created_by (user_id of creator).
- docket_status_history: history_id (PK), docket_id, old_status, new_status,
  changed_by, changed_at, notes, location, delay_reason, status_date (custom event time).
- tbl_offices: office_id, office_name, contact_person, contact_number, address.
- tbl_users: user_id, username, full_name, role_id, office_id.
- tbl_car: car_id, car_number, car_details.
- tbl_staff: staff_id, staff_name, staff_phone.
- tbl_delay_reasons: reason_id, reason_category, reason_text, is_active.
- Manifest (note: table names differ by environment — LOCAL: manifest, manifest_dockets;
  LIVE: tbl_manifest, tbl_manifest_details). The API must auto-detect which exists with
  "SHOW TABLES LIKE" and use whichever is present. A manifest groups multiple dockets being
  transferred from one office to another (fields: manifest_id, manifest_no, from/to office,
  created_at, status). manifest line table links manifest_id ↔ docket_id.

STATUS WORKFLOW (order matters; user generally moves forward only)
  Pending(1) → Confirmed(2) → Picked Up(3) → In Transit(4) →
  Received at Destination(4.5) → Out for Delivery(5) →
  Pending POD(6) / Delivered(6, final) / Failed(6, final) / Cancelled(6, final).
  "Delayed" can be set at any time (does not advance the chain).
  Conditional required fields when updating status:
   - Out for Delivery: car_number + driver_name (+ driver_phone required for branch offices).
   - Delivered: optional POD file upload; if no file, status becomes "Pending POD".
   - Delayed: delay_reason required.
   - All of the above also accept an optional custom event datetime (status_date).

Acknowledge you understand, then wait for the next prompt.
```

---

# PART A — PHP REST API

> Deploy target: a folder `api/` **inside admin** (i.e. `public_html/admin/api/`), reachable at
> `https://northsuperfastservice.com/admin/api/...`. It reuses `../conn.php` (admin/conn.php)
> and the existing tables. Output every file with its full relative path.

## PROMPT A1 — API foundation, token auth, helpers

```
Create the foundation of a PHP REST API under an /api/ folder for the NSFS system described
in PROMPT 0. Requirements:

1. api/_bootstrap.php:
   - Start output as JSON (header Content-Type: application/json; charset=utf-8).
   - Send permissive but safe CORS headers (allow the app origin, allow Authorization
     header, methods GET/POST/PUT/DELETE/OPTIONS; short-circuit OPTIONS preflight with 204).
   - Include the existing DB handle by requiring ../conn.php (this file lives in admin/api/,
     so admin/conn.php is one level up) so $conn (mysqli) is available. Do not create a
     second connection.
   - Helper functions:
       json_ok($data, $extra=[])     -> echo {"success":true, ...} and exit
       json_err($msg, $code=400)     -> http_response_code, echo {"success":false,"error":$msg}, exit
       body_json()                   -> decode JSON request body to assoc array (also support form-data)
       require_method($m)            -> 405 if request method != $m
2. Token auth using a new table tbl_api_tokens(token_id PK, user_id, token CHAR(64) UNIQUE,
   created_at, last_used_at, expires_at). Provide the CREATE TABLE IF NOT EXISTS SQL inside
   a file api/_schema.sql AND have _bootstrap.php create it on first run if missing.
3. api/_auth.php with:
   - current_user(): reads "Authorization: Bearer <token>", looks up a non-expired token,
     loads the user (tbl_users; if user_id maps to legacy tbl_administrator treat as Super
     Admin), and returns an array:
       [ user_id, username, full_name, role_id, role_name, is_super_admin(bool),
         office_id(nullable int), office_name, can_access_all_offices(bool),
         permissions => [list of permission_key], allowed_statuses => [status_name,...] ].
     Cache permissions by querying tbl_role_permissions⋈tbl_permissions. If permission tables
     are absent, grant all. Return null if no/invalid token.
   - require_auth(): returns current_user() or json_err('Unauthorized',401).
   - require_perm($key): 403 if the user lacks $key (super admin always passes).
   - office_filter_sql($alias='dd') and creator_filter_sql($alias='dd'): return the same
     " AND ..." fragments described in PROMPT 0 (port the existing getOfficeFilter /
     getCreatorFilter logic). These MUST be applied to every docket listing/detail query.
Output: _bootstrap.php, _auth.php, _schema.sql. Use prepared statements everywhere user
input touches SQL. Keep it compatible with older PHP 7.x on shared hosting.
```

## PROMPT A2 — Auth endpoints (login / me / logout)

```
Add authentication endpoints to the /api/ folder built in A1. Use a front controller style:
api/index.php routes by path (use the part after /api/). Implement:

POST /api/auth/login   body {username, password}
  - Look up username in tbl_users; verify password with password_verify against the stored
    hash. If not found there, check legacy tbl_administrator (match its username/password
    columns; if that table stores plaintext or md5, support that fallback) and treat success
    as Super Admin.
  - On success: generate a 64-char random token, insert into tbl_api_tokens with
    expires_at = now + 30 days, and return json_ok with { token, user: <the full user
    context array from current_user()> }.
  - On failure: json_err('Invalid username or password', 401).

GET /api/auth/me   (Bearer)  -> json_ok({ user: current_user() }); 401 if invalid.

POST /api/auth/logout  (Bearer) -> delete the token row, json_ok({}).

Update last_used_at on each authenticated request. Output api/index.php (router) and any
controller files you add (e.g. api/controllers/auth.php).
```

## PROMPT A3 — Dockets: list, filters, detail

```
Add docket endpoints to /api/ (reuse router from A2). Enforce office_filter_sql() and
creator_filter_sql() on ALL of these.

GET /api/dockets   (Bearer, requires docket_view)
  Query params (all optional): page (default 1), per_page (default 50, max 100),
  fromdate, todate (YYYY-MM-DD; filter on pickup_datetime BETWEEN from 00:00:00 and to
  23:59:59), status, search_type ('doc'|'box'), search_value, consignor (company_name),
  consignee (client_name), office (office_id), creator (created_by).
  - Build the WHERE with prepared statements (mirror the website's logic: numeric doc search
    uses doc_no = ?, else doc_no LIKE 'value%'; box uses box LIKE 'value%').
  - Run a COUNT(*) for the total, then SELECT the page with
    ORDER BY pickup_datetime DESC, docket_id DESC LIMIT ? OFFSET ?.
  - LEFT JOIN tbl_offices and tbl_users to include branch_office_name and creator_name.
  - Return json_ok({ data:[...rows...], total, page, per_page, total_pages }).

GET /api/dockets/filters  (Bearer, docket_view)
  - Return { companies:[distinct company_name], clients:[distinct client_name],
    offices:[{office_id,office_name}], creators:[{user_id,full_name}],
    statuses:[ the fixed workflow status list ] }, each scoped by office where relevant.

GET /api/dockets/{id}  (Bearer, docket_view)
  - Return the full docket row (scoped). 404 if not visible to this user.

Return dates as ISO strings. Output the docket controller file.
```

## PROMPT A4 — Dockets: status update (+ POD upload) and history

```
Add status endpoints to /api/. Port the existing update_docket_status.php business rules.

GET /api/dockets/{id}/history  (Bearer, docket_view)
  - Return docket_status_history rows for the docket ordered by COALESCE(status_date,
    changed_at) ASC: [{ new_status, notes, location, delay_reason, event_time }].
  - Also return a computed "timeline" array suitable for a tracking UI (Picked Up →
    In Transit → Out for Delivery → Delivered; or the 6-step branch-transfer timeline if the
    docket has a manifest), marking which steps are done and their times.

POST /api/dockets/{id}/status  (Bearer, docket_status_update; multipart/form-data)
  Fields: status (required), status_date (optional ISO datetime), location (optional),
  remarks (optional), car_number, driver_name, driver_phone, car_id, driver_id,
  delay_reason, pod_file (file upload).
  Rules (reject with json_err if violated):
   - The user must be allowed to set this status (allowed_statuses / canUpdateToStatus logic).
   - Status 'Out for Delivery' requires car_number AND driver_name (+ driver_phone if the
     user's office is a branch, i.e. office_id is set and not the main office id 12).
   - Status 'Delivered': if pod_file uploaded, save it under uploads/pod/<year>/<month>/
     <docket_id>/POD_<docket_id>_<timestamp>.<ext> (accept jpg,jpeg,png,pdf, max 5MB) and
     store the relative path in docket_details.proof_of_delivery. If NO file, set status to
     'Pending POD' instead of 'Delivered'.
   - Status 'Delayed' requires delay_reason; store it in reason_of_delay.
  Actions: update docket_details.status (and car/driver/proof fields as relevant), and
  INSERT a docket_status_history row (old_status, new_status, changed_by = full_name,
  changed_at = now, status_date = provided or now, notes = remarks, location, delay_reason).
  Wrap in a transaction. Return json_ok({ status: <new status> }).
Output the updated controller.
```

## PROMPT A5 — Manifests: list, create, receive

```
Add manifest endpoints to /api/. IMPORTANT: detect table names at runtime with
"SHOW TABLES LIKE" — use manifest/manifest_dockets if present, else tbl_manifest/
tbl_manifest_details. Centralize this in a helper manifest_tables().

GET /api/manifests  (Bearer, manifest_view)  -> paginated list (page, per_page) with
  from/to office names, docket count per manifest, created_at, status. Office-scoped.

POST /api/manifests  (Bearer, manifest_view)  body { to_office_id, docket_ids:[...],
  notes } -> create a manifest header (generate manifest_no), attach the given dockets
  (only dockets the user can access), set those dockets' status to 'In Transit to Branch'
  and manifest_id. Transaction. Return the created manifest.

GET /api/manifests/{id}  (Bearer, manifest_view) -> header + its dockets (doc_no, status).

POST /api/manifests/{id}/receive  (Bearer, manifest_receive) body { docket_ids?:[...] }
  -> mark the manifest (or selected dockets) as received at the destination office; set
  docket status to 'Arrived at Branch'/'Received at Destination'; write status history rows.
Output the manifest controller.
```

## PROMPT A6 — Tracking, lookups, dashboard stats

```
Add the remaining read endpoints to /api/.

GET /api/track?doc_no=...  (no auth required — public tracking, but rate-limit gently)
  - Reuse the public timeline logic: find docket_details by doc_no, build the step timeline
    from docket_status_history (4-step direct or 6-step branch-transfer if it has a
    manifest), include current status, current_location, POD availability. Return 404 shape
    {success:false} if not found.

GET /api/lookups/offices | /api/lookups/cars | /api/lookups/staff |
GET /api/lookups/delay-reasons | /api/lookups/statuses   (Bearer)
  - Simple lists from tbl_offices, tbl_car, tbl_staff, tbl_delay_reasons (active only,
    grouped by category), and the fixed status workflow list. Cars/staff/dropdowns are only
    relevant for the main office (office_id 12) per existing rules — still return them.

GET /api/dashboard/stats  (Bearer, dashboard_view)
  Port the metrics computed in admin/index.php. EVERY count must apply office + creator
  scoping (office_filter_sql / creator_filter_sql). Return:
  {
    kpis: {
      total, delivered, in_transit, delayed,
      today_total, today_delivered,         // DATE(created_at)=today
      week_total,                            // created_at >= Monday of this week
      delivery_rate                          // round(delivered/total*100,1), 0 if total=0
    },
    status_distribution: {                   // for the donut
      picked_up,                             // status='Picked Up'
      in_transit, out_for_delivery, delivered, delayed,
      pending_pod                            // status='Pending POD' OR (status='Delivered'
                                             //   AND (proof_of_delivery IS NULL OR =''))
    },
    manifest_count,                          // COUNT(*) FROM tbl_manifest (office-scoped on m)
    monthly_trend: {                         // last 12 months, oldest→newest
      labels:   ["Jul",...,"Jun"],           // short month names
      dockets:  [..12 ints..],               // created_at in that month
      delivered:[..12 ints..]                // delivered AND created_at in that month
    },
    service_types: [ { type, count } ],      // GROUP BY COALESCE(service_type,'Standard'), top 5
    top_clients:   [ { name, count } ]       // GROUP BY company_name ORDER BY count DESC, top 8
  }
  Do NOT include any recent-dockets list here — the dashboard intentionally omits the bottom
  table; the app uses the dedicated Dockets list screen for that.
Output the controller(s).
```

## PROMPT A7 — Create Trip + multiple dockets (REUSE existing logic)

```
Add a trip-creation endpoint to /api/ that creates MANY dockets at once, exactly like the
website's save_trip_modern.php. CRITICAL: do NOT reinvent the insert/auto-sync — REUSE the
existing class admin/DocketDetailsManager.php (constructor takes $conn; methods
saveDocket($data) and docketExists($doc_no)). Require it with require_once '../DocketDetailsManager.php'.

POST /api/trips   (Bearer, requires docket_create)
  Body (JSON):
    { office_id, car_id (optional), car_number, driver_id (optional), driver_name,
      driver_phone, helper_id (optional), helper_name (optional), helper_phone (optional),
      pickup_datetime, dockets: [ { doc_no, service_type, company_id, company_address,
      client_name, client_phone, client_email, client_address, weight, box, dimensions,
      eway_bill, invoice_no, invoice_amount }, ... ] }

  Server logic (port from save_trip_modern.php):
  1. Validate: office_id, car (id or manual number), driver (id or manual name),
     pickup_datetime, and at least one docket with a non-empty doc_no — else json_err 422.
  2. Manual-entry auto-add (combo box): if car_number given but no car_id, find it in tbl_car
     or INSERT a new row (car_details 'External Vehicle', active_status 1) and use its id.
     Same for driver_name -> tbl_staff (staff_role 'Driver', 'External') and helper_name ->
     tbl_staff (staff_role 'Helper').
  3. Generate one trip_group_id = 'TRIP-' . date('Ymd') . '-' . zero-padded random 4 digits.
  4. Normalize each docket: trim+UPPERCASE doc_no, skip empties, default service_type
     'Standard', invoice_no 'N/A' if blank.
  5. Begin a transaction. FIRST loop: collect any doc_no where $mgr->docketExists() is true;
     if the list is non-empty, rollback and json_err with code 409 and a message naming the
     duplicates (return them in an array field "duplicates" too).
  6. SECOND loop: for each docket build the $data array and call $mgr->saveDocket($data) with:
       doc_no, trip_group_id, service_type, doc_type 'DRS', status 'Picked Up',
       office_id, branch_office (office_name looked up from tbl_offices),
       company_id (or null), company_address, pickup_location = company_address,
       client_name, client_phone, client_email, client_address,
       delivery_location = client_address, car_id, driver_id, driver_name (manual or null),
       driver_phone, helper_id (or null), helper_name, helper_phone,
       weight (float), box (int), dimensions, eway_bill, invoice_no, invoice_amount (float),
       created_by = current_user()['user_id'], pickup_datetime.
     If any saveDocket fails, throw -> rollback the whole trip.
  7. Commit. Return json_ok({ trip_group_id, created: <count>, doc_nos: [...] }).
  (Email sending is optional — you may skip the company-email step in the API for now.)

GET /api/dockets/check-duplicate?doc_no=...  (Bearer, docket_create)
  - Port check_duplicate_docket.php: look up the doc_no in docket_details; return
    { exists:bool, status, created_at, trip_group_id, company_name, client_name }.

Also EXTEND the lookups from A6 so the create form has its data:
  GET /api/lookups/companies  -> [{ company_id, company_title, company_address }] from tbl_company.
  GET /api/lookups/staff?role=Driver|Helper -> filtered tbl_staff (active_status=1) with
      staff_id, staff_name, staff_phone (and driving_license for drivers).
Output the trips controller and the extended lookups.
```

---

# PART B — FLUTTER ANDROID APP

> Build a native Flutter app. Mirror the current admin layout: a left **drawer** with the
> same module names, a paginated dockets list with the same filters, the same status-update
> modal as a bottom sheet, etc. Show/hide drawer items based on the user's `permissions`
> returned by the API.

## PROMPT B1 — App scaffold, theme, API client, auth state

```
Create a new Flutter app "nsfs_admin" (Android). Use:
- State management: Riverpod (flutter_riverpod).
- HTTP: dio. Secure token storage: flutter_secure_storage. JSON: built-in. Dates: intl.
- A single ApiClient(dio) with baseUrl = const String.fromEnvironment('API_BASE',
  defaultValue: 'https://northsuperfastservice.com/admin/api'). It must attach
  "Authorization: Bearer <token>" automatically when a token exists, parse the
  {success,error} envelope, and throw a typed ApiException on success=false or non-2xx.
- Models (immutable, with fromJson): UserContext (id, username, fullName, roleName,
  isSuperAdmin, officeId, officeName, permissions:Set<String>, allowedStatuses:List<String>),
  Docket, DocketHistoryItem, Manifest, Office, Car, Staff, DelayReason.
- AuthController (Riverpod StateNotifier): holds AuthState { loading, UserContext? user }.
  Methods: bootstrap() (load saved token, call GET /auth/me, set user or clear),
  login(username,password) (POST /auth/login, save token, set user),
  logout() (POST /auth/logout, clear token).
- A helper bool can(String permission) on UserContext: returns true if isSuperAdmin or
  permissions.contains(permission).
- Theme: match the web admin — primary indigo gradient (#667eea→#764ba2), sidebar dark
  slate (#1e3a52), rounded cards, Inter font. Provide light theme, Material 3.
- Routing: an AuthGate that shows a splash while bootstrap() runs, LoginScreen if no user,
  else HomeShell (Scaffold with a Drawer + body). Use go_router.
Output the full project structure, pubspec.yaml with deps, main.dart, and these core files.
```

## PROMPT B2 — Login screen

```
Build LoginScreen for the nsfs_admin Flutter app. A centered card on the indigo gradient
background with the NSFS brand, username + password fields, show/hide password, a loading
button, and inline error display. On submit call AuthController.login(); on success
go_router redirects to /home (AuthGate handles it). Validate non-empty fields. Match the
web admin's visual style (rounded inputs, gradient primary button).
```

## PROMPT B3 — Home shell + permission-driven drawer + Dashboard

```
Build HomeShell: a Scaffold with an AppBar (title "NSFS CMS", user avatar menu with Logout)
and a Drawer that lists ONLY the modules the user has permission for (use user.can(...)):
- Dashboard (dashboard_view)
- Dockets: All Dockets (docket_view), Create Trip (docket_create), Update Status
  (docket_status_update), Barcode Scanner (docket_view)
- Manifests: All / Create (manifest_view), Receive (manifest_receive)
- Tracking (tracking_view)
Drawer header shows full name, role, and office name. Tapping an item navigates via go_router.

Build DashboardScreen: call GET /api/dashboard/stats once and render the analytics dashboard
that mirrors the web admin (admin/index.php) — but DO NOT include the bottom "Dockets List"
table or its search bar (the app uses the dedicated Dockets screen for that). Sections, top to
bottom:
1. Office indicator banner: if user.officeName != null AND !user.isSuperAdmin AND
   !user.canAccessAllOffices, show "Viewing data for: <officeName>".
2. KPI cards (grid/wrap), each TAPPABLE → navigate to the Dockets list pre-filtered:
   - Total Dockets (kpis.total) → list, no status filter   [show if can('dashboard_view_total_dockets') or super]
   - Delivered (kpis.delivered, subtitle "<delivery_rate>% rate") → list status=Delivered  [dashboard_view_delivered]
   - In Transit (kpis.in_transit) → list status=In Transit   [dashboard_view_in_transit]
   - Today's Dockets (kpis.today_total, subtitle "<today_delivered> delivered")  [always show]
   - This Week (kpis.week_total)  [always show]
   - Delayed (kpis.delayed) → list status=Delayed   [dashboard_view_delayed]
   Gate each permissioned card with user.can(...); always show Today + This Week.
3. Status Distribution donut (fl_chart PieChart) from status_distribution with a center label
   showing kpis.total; legend: Delivered, In Transit, Picked Up, Out for Delivery, Delayed,
   Pending POD (use the web colors: green, blue, amber, teal, red, orange).
4. Monthly Trend line chart (fl_chart LineChart) using monthly_trend.labels with two lines:
   dockets (red) and delivered (green).
5. Status cards (tappable, with a thin % progress bar = count/total) — Picked Up → list
   status=Picked Up; Out for Delivery → status=Out for Delivery; Pending POD → status=Pending
   POD; Manifests (manifest_count) → Manifests screen. Gate with dashboard_view_picked_up /
   _out_for_delivery / _pending_pod / _manifest.
6. Top Clients horizontal bar chart from top_clients. 
7. Service Types donut from service_types.
8. Delivery Performance: a circular gauge showing kpis.delivery_rate (%). Show an
   "In Progress" value (in_transit + out_for_delivery) and "Delayed" count. NOTE: the web has a
   fake hardcoded "On Time 85%" bar — DO NOT replicate it; only show real numbers.
Use pull-to-refresh to re-fetch. Handle loading (skeleton/spinner) and error (retry) states.
Match the dark analytics styling, but keep it readable on mobile (cards stack on small widths).
```

## PROMPT B4 — Dockets list (paginated, filterable)

```
Build DocketsListScreen for nsfs_admin. It mirrors the web "All Dockets" page.
- Calls GET /api/dockets with page/per_page=50 and the active filters; shows total count
  ("Showing X–Y of N").
- Infinite scroll OR numbered pager (your choice) using the API's total_pages.
- Each list item (card): doc_no (bold), date (pickup_datetime, 'd MMM yyyy'), consignor →
  consignee, delivery address, a colored status chip (color per status like the web badges),
  and a tap target opening DocketDetailScreen.
- A filter sheet (top-right filter icon) with: date range, status dropdown, search type
  (Doc/Box) + value, consignor dropdown, consignee dropdown, office dropdown, creator
  dropdown — options loaded from GET /api/dockets/filters. "Apply" and "Reset".
- Search box at top for quick doc_no search.
- Respect permissions for row actions (view always; edit/delete buttons only if the user has
  docket_edit/docket_delete — these can deep-link to detail for now).
Pull-to-refresh re-queries page 1.
```

## PROMPT B5 — Docket detail + status update (bottom sheet) + POD camera

```
Build DocketDetailScreen for nsfs_admin (input: docket_id).
- Loads GET /api/dockets/{id} and GET /api/dockets/{id}/history.
- Top: status chip + doc_no + key fields (consignor, consignee, addresses, phones, box,
  weight, car/driver, office). A vertical timeline from the history "timeline" array
  (done steps highlighted, with times), like the website tracking timeline.
- If POD exists (proof_of_delivery), show a "View POD" button (open URL =
  https://northsuperfastservice.com/<path>).
- Floating "Update Status" button (only if user.can('docket_status_update')) opens a
  bottom sheet form replicating the web modal:
    * Status dropdown limited to user.allowedStatuses and forward-only (disable earlier
      statuses by the workflow order in PROMPT 0; allow 'Delayed' anytime).
    * Optional event datetime (default now).
    * Conditional fields: Out for Delivery → car_number + driver_name (autocomplete from
      GET /api/lookups/cars and /staff) + driver_phone if branch office; Delivered → POD
      file picker using image_picker (camera or gallery) or a PDF; Delayed → delay_reason
      dropdown from /api/lookups/delay-reasons (grouped by category).
    * Location + remarks (optional).
  Submit as multipart to POST /api/dockets/{id}/status. On success, close sheet, refresh,
  toast "Status updated". Show server validation errors inline.
```

## PROMPT B6 — Create Trip (one trip → many dockets)

> IMPORTANT MENTAL MODEL: this is NOT a single-docket form. A **trip** is one vehicle run
> (one car + driver + optional helper + branch office + pickup time) that carries **multiple
> dockets**. The trip-level info is entered once and applied to every docket. This mirrors the
> web "Create New Trip" page (add_trip_modern.php → save_trip_modern.php). Build the API
> endpoint A7 first.

```
Build CreateTripScreen for nsfs_admin (visible if user.can('docket_create')). It mirrors the
web "Create New Trip" page: a TRIP DETAILS section at top, then a repeatable list of DOCKET
cards, then "Save Trip & All Dockets".

TRIP DETAILS (entered once, shared by all dockets):
- Branch Office: dropdown from GET /api/lookups/offices. Default to the user's own office; if
  the user can access all offices, let them change it. Show the office address/phone read-only.
- Car Number: a free-text field with autocomplete suggestions from GET /api/lookups/cars
  (user may type a NEW external vehicle not in the list). Keep a hidden car_id when a listed
  car is chosen.
- Driver Name: free-text + autocomplete from GET /api/lookups/staff?role=Driver; selecting a
  listed driver auto-fills Driver Phone and a hidden driver_id. Manual entry allowed.
- Driver Phone: text (auto-filled from selection or typed).
- Helper Name (optional): free-text + autocomplete from GET /api/lookups/staff?role=Helper;
  selecting fills Helper Phone + hidden helper_id.
- Helper Phone (optional).
- Pickup Date & Time: datetime picker (required).

DOCKETS (a dynamic list; start with one card, "Add Another Docket" appends more, each
removable except the first):
Each docket card has:
- Docket Number (doc_no): required, force UPPERCASE as the user types. Run a LIVE duplicate
  check (debounced ~600ms) against GET /api/dockets/check-duplicate?doc_no=...; if it exists,
  show a red inline warning with the existing status/created date and BLOCK saving.
- Service Type: dropdown Standard / Express / Overnight (default Standard).
- Sender: Company dropdown from GET /api/lookups/companies (value = company_id, label =
  company_title). Selecting a company auto-fills "Company Address (Pickup Location)" (editable,
  UPPERCASE). For dockets #2+, default the company + address from docket #1 (sender is usually
  the same) — but allow changing.
- Receiver: Client Name (req, UPPERCASE), Phone (req), Email (optional), Client Address
  (Delivery Location) (req, UPPERCASE).
- Package: Weight (kg, decimal), Box/Items (int), Dimensions (text), E-way Bill (optional),
  Invoice Number (optional), Invoice Amount (optional, decimal).

SUBMIT:
- Validate: at least one docket; no docket showing a duplicate warning; trip required fields
  filled. Build a JSON body:
    { office_id, car_id?, car_number, driver_id?, driver_name, driver_phone,
      helper_id?, helper_name?, helper_phone?, pickup_datetime,
      dockets: [ { doc_no, service_type, company_id, company_address, client_name,
                   client_phone, client_email, client_address, weight, box, dimensions,
                   eway_bill, invoice_no, invoice_amount } , ... ] }
- POST it to /api/trips. On success show "Trip created with N docket(s)" and navigate to the
  Dockets list (filtered by the new trip if easy). On a duplicate/validation error from the
  server, surface the returned message and highlight the offending doc_no(s).
Match the web styling (cards, green "Add Another Docket", indigo "Save"). Use Riverpod for the
dynamic docket list state.
```

## PROMPT B7 — Manifests (list / create / receive)

```
Build the Manifests feature for nsfs_admin:
- ManifestListScreen: GET /api/manifests paginated; cards show manifest_no, from→to office,
  docket count, status, date.
- CreateManifestScreen (manifest_view): pick destination office (GET /api/lookups/offices),
  then select dockets to include (reuse a paginated/searchable docket picker that lists
  eligible dockets), submit POST /api/manifests.
- ReceiveManifestScreen (manifest_receive): load a manifest by number/id, show its dockets
  with checkboxes, "Receive selected" → POST /api/manifests/{id}/receive.
Match the web styling. Handle loading/empty/error states.
```

## PROMPT B8 — Tracking + Barcode scanner

```
Add two features to nsfs_admin:
1. TrackingScreen: a search field for a Doc No; calls GET /api/track?doc_no=... and renders
   the same step timeline + current status + POD link as the public website tracking page.
2. BarcodeScannerScreen: use mobile_scanner to scan a barcode/QR containing a doc_no; on a
   successful scan, look it up (GET /api/dockets?search_type=doc&search_value=<code>) and
   navigate straight to that docket's DocketDetailScreen. Provide a manual-entry fallback.
Add a torch toggle and handle camera permission denial gracefully.
```

---

# PART C — DEPLOYMENT & SECURITY

```
DEPLOY THE API
1. Upload the /api/ folder to public_html/admin/api/ on Hostinger (inside the admin folder).
2. Confirm https://northsuperfastservice.com/admin/api/ping responds with JSON {pong:true}.
3. Ensure the site is HTTPS-only (it must be — the app sends a Bearer token).
4. The API reuses ../conn.php (admin/conn.php), so it uses the same .env DB credentials.

SECURITY CHECKLIST (enforced in the prompts above, verify it stayed in)
- All SQL uses prepared statements / escaping. No string-concatenated user input.
- Every docket query applies office + creator row-scoping server-side (never trust the app).
- Tokens are random 64-char, stored hashed-or-opaque in tbl_api_tokens, expire in 30 days,
  and are revoked on logout.
- File uploads (POD) are extension- and size-checked and stored under uploads/pod/...
  (which is already gitignored and lives only on the server).
- CORS is restricted to your app/site origin in production.

BUILD THE APK (Flutter)
1. flutter pub get
2. flutter run   (debug on a device)
3. flutter build apk --release
   (optionally pass --dart-define=API_BASE=https://northsuperfastservice.com/admin/api)
4. Install the APK or publish to Play Store.

NOTES
- The app shows/hides features by permission, but the server is the real gate.
- Manifest table-name differences (local vs live) are handled by runtime detection in A5.
- Start with Part A endpoints A1–A4 + app B1–B5 to get a working "view dockets + update
  status + scan" MVP, then add manifests/tracking.
```
