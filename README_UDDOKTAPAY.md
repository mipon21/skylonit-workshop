# UddoktaPay Integration

This app extends the ERP with **UddoktaPay** payment links and **manual (cash) settlement**. Payments are created as **DUE**; an invoice is generated only when the payment becomes **PAID** (via gateway or admin marking as cash).

## Configuration

### 1. Environment variables

Add to your `.env`:

```env
# Base URL = root API endpoint (include /api). Paths /checkout-v2 and /verify-payment are appended.
UDDOKTAPAY_BASE_URL=https://skylon-it.paymently.io/api
UDDOKTAPAY_API_KEY=your_api_key_here
```

- **Paymently / UddoktaPay:** Set `UDDOKTAPAY_BASE_URL` to your **root API endpoint** (e.g. `https://skylon-it.paymently.io/api`). The app will call `{base_url}/checkout-v2` and `{base_url}/verify-payment`. Do not include `/checkout-v2` in the base URL.
- **Sandbox:** Use `https://sandbox.uddoktapay.com/api` if your sandbox uses the same path layout.
- Use an API key from your dashboard with create/charge and verify permissions.

### 2. Config

The app reads from `config/services.php`:

- `services.uddoktapay.base_url` – from `UDDOKTAPAY_BASE_URL`
- `services.uddoktapay.api_key` – from `UDDOKTAPAY_API_KEY`

No extra config file is required.

## Flow

1. **Admin creates payment**  
   Payment is stored as **DUE** with `gateway = uddoktapay`. The app calls UddoktaPay **Create Charge** (`POST {base_url}/api/checkout-v2`). The returned `payment_url` is saved and shown as “Copy Payment Link” in the project Payments tab.

2. **Admin shares link**  
   Admin copies the payment link and sends it to the client (email, chat, etc.). The client can also open it from **Client Portal → Payments → Pay Now**.

3. **Payment becomes PAID in two ways**
   - **Gateway:** Client pays on UddoktaPay. UddoktaPay sends a webhook to `POST /api/uddoktapay/webhook`. The app verifies the payment with **Verify Payment** (`POST {base_url}/api/verify-payment`). If status is `COMPLETED`, the payment is marked PAID and the invoice is generated.
   - **Manual (cash):** Admin clicks **Mark as Paid (Cash)** on a DUE payment. The payment is set to PAID, `gateway = manual`, `paid_method = cash`, and the invoice is generated immediately.

4. **Invoice**  
   Generated only when a payment turns **PAID**. Watermark is **PAID**. It appears in Admin (project Payments tab) and in Client Portal (Invoices).

## Webhook

- **URL:** `POST /api/uddoktapay/webhook`  
  Must be publicly reachable (e.g. `https://yourdomain.com/api/uddoktapay/webhook`).
- **Header:** `RT-UDDOKTAPAY-API-KEY` must match `UDDOKTAPAY_API_KEY`.
- **Payload:** UddoktaPay sends `invoice_id` and optionally `metadata.payment_id`. The app finds the payment and calls the verify API before marking PAID and generating the invoice.

Configure this URL in your UddoktaPay dashboard as the **webhook URL** for the charge.

## Client redirect URLs

- **Success:** `GET /client/payment/success?invoice_id=xxx`  
  Logged-in client is verified; app calls verify API and, if COMPLETED, marks payment PAID and generates invoice, then redirects to Invoices.
- **Cancel:** `GET /client/payment/cancel`  
  Redirects the client back to the Payments list.

These are built as full URLs using `url()` (from `config('app.url')`). Ensure `APP_URL` in `.env` is correct so UddoktaPay can redirect to the right domain.

## Security

- Clients see and pay only their own payments (scoped by `client_id`).
- Webhook requests are accepted only when `RT-UDDOKTAPAY-API-KEY` matches the configured key.
- Success/cancel redirects are not trusted alone; the app always verifies payment with the **Verify Payment** API before marking PAID.

## Demo data

To seed demo DUE, PAID (gateway), and PAID (cash) payments:

```bash
php artisan db:seed --class=UddoktaPayDemoPaymentsSeeder
```

Or run the full seed:

```bash
php artisan db:seed
```

## Database

Relevant columns on `payments`:

- `gateway` – `uddoktapay` | `manual`
- `payment_status` – `DUE` | `PAID`
- `gateway_invoice_id` – UddoktaPay invoice id (if any)
- `payment_link` – URL for the client to pay
- `paid_at` – when the payment was marked PAID
- `paid_method` – `gateway` | `cash`

Run migrations if you have not yet:

```bash
php artisan migrate
```
