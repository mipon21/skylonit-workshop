# Deployment Notes - Invoice System

## ✅ Pre-Deployment Checklist

### 1. Database
```bash
# Ensure migration is applied
php artisan migrate:status

# Should show:
# 2026_02_04_172331_create_invoices_table [18] Ran
```

### 2. Storage Permissions
```bash
# Ensure invoices directory is writable
chmod -R 775 storage/app/invoices

# Or on Windows (if needed):
# Right-click storage/app/invoices → Properties → Security → Grant write access
```

### 3. Test Invoice Generation
```bash
# Test with existing payment
php artisan invoice:test

# Should output:
# ✓ Invoice regenerated successfully!
# ✓ PDF file created
# ✓ File size: ~2.81 KB
```

### 4. Verify Web Access
- Admin: Visit `/invoices` - should see list
- Client: Visit `/invoices` - should see only their invoices
- Try downloading a PDF - should work

---

## 🗂️ File Cleanup (Optional)

### Files You Can Delete
These files are no longer needed:

```bash
# Original large SVG (not used by system)
rm "Copy of Copy of Skylon-IT Agreement For FlashBDTopUp.svg"

# Old HTML templates (replaced by SVG)
rm resources/views/invoices/exact-template.blade.php
rm resources/views/invoices/original.html
rm temp_invoice.html
```

### Files to Keep
- ✅ `resources/views/invoices/svg-template.blade.php` - **Active template**
- ✅ `app/Services/InvoiceService.php` - **Core service**
- ✅ `app/Http/Controllers/InvoiceController.php` - **Controller**
- ✅ All documentation files (*.md)

---

## 🚀 Deployment Steps

### Step 1: Commit Changes
```bash
cd "i:\htdocs\mini erp"
git add .
git commit -m "Implement SVG-based invoice system with dynamic watermarks and client portal"
git push
```

### Step 2: Deploy to Production
```bash
# On production server:
git pull
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify storage permissions
chmod -R 775 storage/app/invoices
```

### Step 3: Test on Production
```bash
# Test invoice generation
php artisan invoice:test

# Access via browser
# Visit: https://your-domain.com/invoices
```

---

## 📊 Monitoring

### Key Metrics to Watch

1. **Invoice Generation Failures**
   - Check logs: `storage/logs/laravel.log`
   - Look for: "Failed to generate invoice"

2. **Storage Usage**
   - Monitor: `storage/app/invoices/` directory size
   - Expected: ~3 KB per invoice
   - Clean up old invoices periodically if needed

3. **Performance**
   - Invoice generation should take < 2 seconds
   - If slower, check server resources

### Logging
Add to `.env` if not already present:
```env
LOG_CHANNEL=stack
LOG_LEVEL=info
```

---

## 🔧 Configuration Options

### Customize Invoice Template
Edit: `resources/views/invoices/svg-template.blade.php`

**Common customizations:**
- Colors (header gradient, watermark colors)
- Font sizes
- Layout spacing
- Company logo/branding
- Footer text

### Change Storage Location
Edit: `app/Services/InvoiceService.php`

```php
// Line 84: Change 'invoices/' to desired path
$path = 'invoices/' . $filename;
```

### Adjust Watermark
Edit: `resources/views/invoices/svg-template.blade.php`

```xml
<!-- Line with watermark -->
<g transform="translate(397, 561) rotate(-30)" opacity="0.12">
```

**Adjustments:**
- `opacity`: 0.12 (higher = more visible)
- `rotate(-30)`: Rotation angle
- `font-size="120"`: Text size

---

## 🐛 Common Issues & Fixes

### Issue: "Class 'Barryvdh\DomPDF\Facade\Pdf' not found"
**Fix:**
```bash
composer require barryvdh/laravel-dompdf
php artisan config:clear
```

### Issue: "storage/app/invoices is not writable"
**Fix:**
```bash
chmod -R 775 storage/app/invoices
chown -R www-data:www-data storage/app/invoices  # Linux
```

### Issue: Invoice shows blank page
**Fix:**
- Check if SVG is rendering: View page source
- Check DOMPDF logs
- Verify all Blade variables have data

### Issue: Watermark not showing
**Fix:**
- Increase opacity in template
- Check if `$invoice->payment_status` is set
- Verify SVG layer ordering

### Issue: Text cut off in PDF
**Fix:**
- Adjust X/Y coordinates in template
- Use `Str::limit()` for long fields
- Reduce font size if needed

---

## 💾 Backup Recommendations

### What to Backup

1. **Invoice PDFs**
   - Location: `storage/app/invoices/`
   - Frequency: Daily
   - Method: rsync, S3, or cloud backup

2. **Database**
   - Table: `invoices`
   - Includes: invoice_number, file_path, payment_status
   - Frequency: Daily (same as regular DB backups)

### Sample Backup Script
```bash
#!/bin/bash
# backup-invoices.sh

DATE=$(date +%Y%m%d)
BACKUP_DIR="/backups/invoices/$DATE"

mkdir -p "$BACKUP_DIR"
cp -r storage/app/invoices/* "$BACKUP_DIR/"

# Optional: Sync to S3
# aws s3 sync "$BACKUP_DIR" s3://your-bucket/invoices/$DATE
```

---

## 📈 Scaling Considerations

### If Invoice Generation Becomes Slow

**Option 1: Queue Jobs**
```php
// Create job: GenerateInvoiceJob
php artisan make:job GenerateInvoiceJob

// In PaymentObserver:
GenerateInvoiceJob::dispatch($payment);
```

**Option 2: Cache PDFs**
- Store generated PDFs in CDN
- Regenerate only when payment changes

**Option 3: Optimize DOMPDF**
- Use local fonts (no remote downloads)
- Reduce image complexity
- Enable DOMPDF caching

### Storage Management

**If invoices directory grows large:**
```php
// Create cleanup command
php artisan make:command CleanupOldInvoices

// Delete invoices older than 1 year
$oldInvoices = Invoice::where('created_at', '<', now()->subYear())->get();
foreach ($oldInvoices as $invoice) {
    Storage::delete($invoice->file_path);
    // Optionally delete invoice record
}
```

---

## 🔐 Security Audit

### Checklist
- [x] Client authorization checks in place
- [x] File path validation (no directory traversal)
- [x] MIME type verification for downloads
- [x] Proper error handling (no data leaks)
- [x] Database relationships use foreign keys
- [x] No SQL injection risks (using Eloquent)

### Additional Hardening (Optional)
```php
// Add rate limiting to invoice downloads
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/invoices/{invoice}/download', ...);
});
```

---

## 📞 Support Information

### Documentation Files
- `README_INVOICE_SYSTEM.md` - Quick start guide
- `SVG_INVOICE_IMPLEMENTATION.md` - Technical details
- `ORIGINAL_SVG_ANALYSIS.md` - SVG file analysis
- `INVOICE_TESTING_GUIDE.md` - Testing procedures
- `DEPLOYMENT_NOTES.md` - This file

### Laravel Logs
- Location: `storage/logs/laravel.log`
- Relevant errors: Search for "invoice", "DOMPDF", or "InvoiceService"

### Debug Mode
```env
# .env (development only!)
APP_DEBUG=true
```

---

## ✅ Post-Deployment Verification

After deployment, verify:

1. **Invoice Generation**
   ```bash
   php artisan invoice:test
   # Should succeed without errors
   ```

2. **Web Access**
   - [ ] Admin can view `/invoices`
   - [ ] Admin can download invoices
   - [ ] Client can view `/invoices` (own only)
   - [ ] Client can download invoices (own only)

3. **Security**
   - [ ] Client A cannot access Client B's invoices
   - [ ] Unauthenticated users redirected to login
   - [ ] File paths don't expose system information

4. **Performance**
   - [ ] Invoice generation < 2 seconds
   - [ ] PDF file size < 50 KB
   - [ ] Page load time acceptable

5. **Data Integrity**
   - [ ] All invoice fields populated correctly
   - [ ] Watermark displays with correct color
   - [ ] Numbers formatted properly
   - [ ] Dates show correct format

---

## 🎉 Success Indicators

System is working correctly when:

✅ New payments auto-generate invoices
✅ PDFs download successfully
✅ Clients see only their invoices
✅ Admins see all invoices
✅ Watermarks display correctly
✅ No errors in logs
✅ File sizes remain small
✅ Generation speed is fast

---

## 🆘 Emergency Rollback

If critical issues arise:

```bash
# Rollback migration (removes invoices table)
php artisan migrate:rollback

# Revert to previous template
git checkout HEAD~1 -- resources/views/invoices/exact-template.blade.php
git checkout HEAD~1 -- app/Services/InvoiceService.php

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Note**: This will delete all invoice records. Backup first!

---

## 📅 Maintenance Schedule

### Weekly
- Check storage usage
- Review error logs
- Verify invoice generation success rate

### Monthly
- Clean up old test invoices
- Review and optimize if needed
- Update documentation if changes made

### Quarterly
- Audit security settings
- Review performance metrics
- Plan optimizations if needed

---

**Deployment Status**: ✅ Ready for Production

**Last Updated**: February 5, 2026

**Version**: 1.0.0
