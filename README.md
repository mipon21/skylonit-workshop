# Skylon-IT Mini ERP + Project Management

Production-ready Laravel 10 web-based Mini ERP and Project Management System for Skylon-IT. Dark premium UI with Blade, Tailwind CSS, and AlpineJS. No React/Vue/Bootstrap.

## Stack

- **Laravel 10**
- **MySQL**
- **Blade**
- **Tailwind CSS**
- **AlpineJS**
- **Laravel Breeze** (auth: login, logout)
- **google/apiclient** (optional Google Sheets sync)

## Financial pipeline (mandatory order)

Each project follows this exact order. All percentages live in `config/revenue.php`; no magic numbers in controllers or Blade.

1. **Contract amount (T)** — client contract
2. **Total expenses (E)** — sum of project expenses  
   **Net base** = T − E
3. **Overhead** = 20% of Net base
4. **Sales** = 25% of (Net base − Overhead)
5. **Developer** = 40% of Net base
6. **Profit** = remainder (zero if Net base &lt; 0)

Order: **Contract → Expenses → Overhead → Sales → Developer → Profit**

Revenue is always calculated by the ERP. Google Sheets must never override revenue.

## Requirements

- PHP 8.1+
- Composer
- Node.js & npm (Vite/Tailwind)
- MySQL

## Setup

1. **Install dependencies**
   ```bash
   cd "mini erp"
   composer install
   npm install
   ```

2. **Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Create the MySQL database.

3. **Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Frontend**
   ```bash
   npm run build
   ```
   Development: `npm run dev`

5. **Admin user**  
   Register at `/register` or create via tinker:
   ```bash
   php artisan tinker
   >>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@skylon.it', 'password' => bcrypt('password')]);
   ```

6. **Run**
   ```bash
   php artisan serve
   ```
   Open http://localhost:8000 — login, then Dashboard, Clients, Projects.

## Google Sheets sync (optional)

ERP is the source of truth. Revenue fields are never synced from Sheet to ERP.

- **ERP → Sheet**: On create/update, a queued job pushes data to the sheet (erp_id, updated_at, and entity fields).
- **Sheet → ERP**: `php artisan sync:google` runs every minute (scheduler). Reads sheet rows, matches by erp_id, compares updated_at; updates ERP only if the sheet is newer. Revenue (contract_amount, expense_total, net_base, etc.) is never updated from the sheet.

**Enable:**

1. Create a Google Cloud project, enable Sheets API, create a Service Account, download JSON key.
2. Save the JSON key as `storage/app/google-credentials.json` (or set `GOOGLE_SHEETS_CREDENTIALS` in `.env`).
3. Create a Google Sheet, share it with the service account email (Editor). Copy the spreadsheet ID from the URL.
4. In `.env`:
   ```
   GOOGLE_SHEETS_SYNC_ENABLED=true
   GOOGLE_SHEETS_SPREADSHEET_ID=your_spreadsheet_id
   ```
5. Create tabs: **Projects**, **Payments**, **Expenses**, **Documents**, **Tasks**, **Bugs**, **Notes**. Use the first row as header; columns are filled by the app (see `config/google_sheets.php` and `App\Services\GoogleSheetsService`).
6. For cron: `* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1`

## Push notifications (FCM) — optional

Client portal users (logged-in clients, not guests) can receive **browser/mobile push notifications** in addition to in-app popups and polling.

- **Setup:** See [docs/FIREBASE_SETUP.md](docs/FIREBASE_SETUP.md) for Firebase project, Server Key, Web app config, and VAPID key.
- **Verification:** See [docs/FIREBASE_VERIFICATION.md](docs/FIREBASE_VERIFICATION.md) for a full checklist of backend/frontend wiring.
- **Requires:** HTTPS, `public/firebase-messaging-sw.js` at site root, and `.env` FCM/Firebase variables. If not configured, the app runs normally without push.

## SMTP and email notifications

All notification content comes from **admin-editable email templates**. No subject/body is hardcoded.

**Config (.env):**

- `MAIL_MAILER=smtp`
- `MAIL_HOST` — e.g. `smtp.mailtrap.io`, `smtp.gmail.com`
- `MAIL_PORT` — e.g. `587` (TLS) or `465` (SSL)
- `MAIL_USERNAME`, `MAIL_PASSWORD`
- `MAIL_ENCRYPTION` — `tls`, `ssl`, or `null`
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`

For queued mail, set `QUEUE_CONNECTION=database` (or `redis`), run `php artisan queue:work`, and ensure the `jobs` table exists (`php artisan queue:table` then migrate).

**Admin → Email Templates** (sidebar): list templates, edit subject/body (HTML), enable/disable per template. Placeholders (e.g. `{{client_name}}`, `{{payment_link}}`) are replaced when sending.

**Per-action toggle:** On Create Client, Create Project, Create Payment, Upload Document, Create Expense, Create Note, Create Link, Bug status update, and Task status update, a checkbox **"Send Email Notification?"** (default unchecked) controls whether a template-based email is queued. Email is sent only when the toggle is ON and the corresponding template is enabled. **Payment success** (gateway or cash) sends automatically (no toggle), with invoice PDF attached when available.

**Logo:** The app logo (Profile → logo upload, or `APP_LOGO`) is shown at the top of every template-based email.

**Verify config without sending to your inbox:** Set `MAIL_MAILER=log` in `.env`, then run `php artisan mail:test`. The test message is written to `storage/logs/laravel.log` only; no email is sent. Use `php artisan mail:test --no-send` to only check SMTP connection (host/port) without sending.

## Pages

| URL | Description |
|-----|-------------|
| `/dashboard` | KPIs: Total Revenue, Profit, Due, Active Projects, Open Bugs, Active Tasks, Documents, Notes |
| `/clients` | CRUD clients |
| `/projects` | Project cards (name, client, status, contract, due); add/edit/delete |
| `/projects/{id}` | Revenue pipeline cards; tabs: Payments, Expenses, Documents, Tasks (Kanban), Bugs, Notes |
| `/settings/email-templates` | (Admin) Edit email templates for client notifications |

## Seed data

`php artisan db:seed` creates sample clients, projects, payments, expenses, tasks, bugs, documents, and notes.

## Config

- **Revenue**: `config/revenue.php` — `overhead` (0.20), `sales` (0.25), `developer` (0.40). Profit = remainder.
- **Google Sheets**: `config/google_sheets.php` — spreadsheet ID, credentials path, tab names.

## License

Proprietary — Skylon-IT.
