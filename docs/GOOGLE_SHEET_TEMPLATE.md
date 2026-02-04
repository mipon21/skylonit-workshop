# Google Sheet template for Skylon-IT ERP sync

Create a Google Sheet and add these tabs with the following headers (first row). The app will fill rows; **do not use the first row for data**. Revenue columns (e.g. contract_amount, expense_total, net_base on Projects) are written by the ERP only and must never be used to overwrite ERP revenue.

## Tab: Projects

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
