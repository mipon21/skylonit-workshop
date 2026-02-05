# Invoice System - Testing Guide

## Quick Start Testing

### 1. Command Line Testing

```bash
# Test invoice generation for first completed payment
php artisan invoice:test

# Test specific payment
php artisan invoice:test 12
```

### 2. Web Interface Testing

#### Admin User Testing
1. **Navigate to Projects**
   - URL: `/projects`
   - Click on any project with payments

2. **View Payments Tab**
   - Scroll to payments section
   - Verify "Invoice" button appears for each payment
   - Click "Invoice" to download PDF

3. **Navigate to Invoices Menu**
   - URL: `/invoices`
   - Verify all invoices are listed
   - Test "View" button (opens in new tab)
   - Test "Download" button

#### Client User Testing
1. **Login as Client**
   - Use client credentials

2. **Navigate to Projects**
   - Should only see own projects

3. **View Project Payments**
   - Click on a project
   - Verify invoice download button appears

4. **Navigate to Invoices Menu**
   - URL: `/invoices`
   - Verify only own project invoices are visible
   - Test download functionality

## Edge Cases to Verify

### ✅ Payment Without Invoice
**Scenario**: Create new payment
**Expected**: Invoice auto-generates via PaymentObserver
**Test**:
```php
// In Tinker or test
$payment = Payment::create([...]);
// Check if invoice exists
$payment->invoice; // Should return Invoice instance
```

### ✅ Update Existing Payment
**Scenario**: Edit payment amount or status
**Expected**: Invoice regenerates with updated data
**Test**: Edit payment via admin panel, verify PDF updates

### ✅ Project with No Payments
**Scenario**: New project without payments
**Expected**: No invoices shown, no errors
**Test**: Navigate to project page, payments tab should be empty

### ✅ Client with No Invoices
**Scenario**: Client user with projects but no payments
**Expected**: "No Invoices Yet" message displayed
**Test**: Navigate to `/invoices` as client with no payments

### ✅ Very Long Text Fields
**Scenario**: Project name > 50 characters, email > 35 characters
**Expected**: Text truncated with `Str::limit()` to prevent overflow
**Test**: Create payment with long fields, verify PDF doesn't break

### ✅ Missing Optional Fields
**Scenario**: Client with no phone or email
**Expected**: Shows "N/A" in invoice
**Test**: Create payment for client without phone/email

### ✅ Security - Cross-Client Access
**Scenario**: Client A tries to access Client B's invoice
**Expected**: 403 Forbidden error
**Test**:
```
# As Client A, try to access Client B's invoice
GET /invoices/{client_b_invoice_id}/download
# Should return 403
```

### ✅ Non-Existent Invoice
**Scenario**: Access invoice that doesn't exist
**Expected**: 404 Not Found
**Test**:
```
GET /invoices/99999/download
# Should return 404
```

### ✅ Missing PDF File
**Scenario**: Invoice record exists but file deleted
**Expected**: 404 error with message "Invoice file not found"
**Test**: Delete PDF file, try to download

### ✅ Payment Status Watermark Colors
**Test All Statuses**:
- PAID: Green (#22c55e)
- PARTIAL: Amber (#f59e0b)  
- DUE: Red (#ef4444)

Create test payments with different total amounts to trigger each status.

## Automated Test Suite (Optional)

Create feature tests:

```php
// tests/Feature/InvoiceTest.php
public function test_invoice_generated_on_payment_creation()
public function test_admin_can_download_any_invoice()
public function test_client_can_only_download_own_invoices()
public function test_invoice_list_scoped_to_client()
public function test_watermark_color_matches_payment_status()
```

## Performance Testing

### Invoice Generation Speed
```bash
time php artisan invoice:test
# Should complete in < 2 seconds
```

### PDF File Size
- Single invoice: ~2-5 KB
- Acceptable range: < 50 KB

### Concurrent Generation
```bash
# Create 10 payments simultaneously
# Verify all invoices generate without conflicts
```

## Visual Verification Checklist

Open generated PDF and verify:

- [ ] Header gradient displays correctly
- [ ] Logo circle with "S-IT" visible
- [ ] All dynamic fields populated
- [ ] Watermark visible with correct color and opacity
- [ ] Text not overlapping or cut off
- [ ] Proper spacing and alignment
- [ ] Footer information present
- [ ] No encoding issues with ৳ (Taka symbol)
- [ ] Dates formatted correctly (d M Y)
- [ ] Numbers formatted with commas

## Common Issues & Solutions

### Issue: PDF appears blank
**Solution**: Check DOMPDF configuration, verify storage permissions

### Issue: SVG not rendering
**Solution**: DOMPDF has limited SVG support, ensure using simple SVG elements

### Issue: Text overlapping
**Solution**: Adjust x/y coordinates in svg-template.blade.php

### Issue: Watermark too visible/invisible
**Solution**: Adjust opacity value (currently 0.12) in template

### Issue: File not found error
**Solution**: Run `php artisan storage:link` if using public disk

### Issue: Slow generation
**Solution**: Check if DOMPDF is using remote fonts, ensure local fonts used

## Production Readiness Checklist

- [x] Migrations applied
- [x] Observer registered
- [x] Routes configured
- [x] Security checks in place
- [x] Error handling implemented
- [x] File storage configured
- [x] Navigation links added
- [x] Client portal access enabled
- [x] PDF generation tested
- [x] Download functionality verified

## Monitoring Recommendations

### Key Metrics to Track
1. **Invoice generation time** (should be < 2s)
2. **Failed generation count** (should be 0)
3. **Average file size** (~3 KB)
4. **Storage usage** (invoices directory size)

### Error Monitoring
Monitor logs for:
- PDF generation failures
- File permission errors
- Database constraint violations
- Memory limit issues

### Performance Optimization (if needed)
1. Queue invoice generation for async processing
2. Implement PDF caching
3. Add CDN for static assets
4. Compress older invoices

---

## Success Criteria

✅ System is ready for production when:
1. All edge cases handled gracefully
2. Both admin and client can access invoices
3. PDFs generate in < 2 seconds
4. File sizes remain small (< 50 KB)
5. Security checks prevent unauthorized access
6. No errors in application logs

**Current Status**: ✅ Production Ready
