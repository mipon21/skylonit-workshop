# Google Sheet template for Skylon-IT ERP sync

Create a Google Sheet and add these tabs with the following headers (first row). The app will fill rows; **do not use the first row for data**. Revenue columns (e.g. contract_amount, expense_total, net_base on Projects) are written by the ERP only and must never be used to overwrite ERP revenue.

---

## Production: Projects tab (single tab, bidirectional)

Use **one tab named "Projects"** with this header row. Columns A–Z are editable (allowed fields); AA–AL are ERP system columns (read-only from sheet; ERP overwrites these).

| Col | Header | Editable | Notes |
|-----|--------|----------|--------|
| A | SL | | Row / serial |
| B | Project Name | ✓ | |
| C | Project ID | | Display (e.g. SLN-000001) |
| D | Order ID | ✓ | |
| E | Contract Date | | |
| F | Delivery Date | ✓ | |
| G | Project Type | ✓ | |
| H | Payment Method | ✓ | |
| I | Contract Amount | | ERP source |
| J | Advance | ✓ | One payment (type=advance) |
| K | Due | | ERP calculated |
| L | Middle Payments | ✓ | Comma-separated amounts (unlimited) |
| M | Final Pay | ✓ | One payment |
| N | Tips | ✓ | Tip payment |
| O | Expense | ✓ | Creates/updates ERP expense |
| P | Balance | | ERP calculated |
| Q | Company Share | read-only | Legacy |
| R | Sales Share | read-only | Legacy |
| S | Payment Status | | ERP derived |
| T | Project Status | ✓ | |
| U–Z | Client Name, Phone, Address, Email, Facebook Link, KYC | ✓ | |
| AA | erp_project_id | | Primary key; match row to ERP |
| AB | updated_at | | Conflict resolution |
| AC–AL | expense_total, net_base, overhead, sales, developer, profit, *_paid | | **ERP only**; do not edit |

- **Sync**: Scheduled every 5 minutes; or **Settings → Google Sync → Sync Now**.
- **Conflict**: If Sheet `updated_at` newer → update ERP allowed fields and import payments/expense. If ERP newer → overwrite sheet row. Sheet deletes are ignored.
- **Payments**: Advance (one), Middle (unlimited, comma-separated), Final (one), Tips. Imported as gateway=manual, status=PAID. Dedupe by payment hash.

---

## Legacy tab layout (multi-tab)

## Tab: Projects (legacy)

| erp_id | updated_at | project_name | project_code | client_id | client_name | contract_amount | contract_date | delivery_date | status | expense_total | net_base |

## Tab: Payments

| erp_id | updated_at | project_id | amount | note | payment_date | status |

(status: upcoming / due / completed; only completed counts toward Total paid)

## Tab: Expenses

| erp_id | updated_at | project_id | amount | note |

## Tab: Documents

| erp_id | updated_at | project_id | title | file_path |

## Tab: Tasks

| erp_id | updated_at | project_id | title | description | status | priority | due_date |

## Tab: Bugs

| erp_id | updated_at | project_id | title | description | severity | status |

## Tab: Notes

| erp_id | updated_at | project_id | title | body | visibility | created_by |

---

- **ERP → Sheet**: On create/update the app appends or updates the row with `erp_id` and `updated_at`.
- **Sheet → ERP**: `php artisan sync:google` reads rows, finds the record by `erp_id`, and if the sheet’s `updated_at` is newer, updates the ERP (except revenue fields on Projects).
