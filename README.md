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

## Pages

| URL | Description |
|-----|-------------|
| `/dashboard` | KPIs: Total Revenue, Profit, Due, Active Projects, Open Bugs, Active Tasks, Documents, Notes |
| `/clients` | CRUD clients |
| `/projects` | Project cards (name, client, status, contract, due); add/edit/delete |
| `/projects/{id}` | Revenue pipeline cards; tabs: Payments, Expenses, Documents, Tasks (Kanban), Bugs, Notes |

## Seed data

`php artisan db:seed` creates sample clients, projects, payments, expenses, tasks, bugs, documents, and notes.

## Config

- **Revenue**: `config/revenue.php` — `overhead` (0.20), `sales` (0.25), `developer` (0.40). Profit = remainder.
- **Google Sheets**: `config/google_sheets.php` — spreadsheet ID, credentials path, tab names.

## License

Proprietary — Skylon-IT.
