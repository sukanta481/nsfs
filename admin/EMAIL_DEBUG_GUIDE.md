# 🐛 Email Notification Debugging Guide

## Issue Fixed: Docket Creation Emails Not Sending

### **Problem Identified**
When dockets were created via manifest, the `company_email` and `client_email` fields were **NOT** being saved to the database, so no emails could be sent.

---

## ✅ **What Was Fixed**

### **File Updated: `manifest_save.php`**

**Changes Made:**
1. ✅ Added support for capturing `client_email` and `company_email` from POST data
2. ✅ Modified the docket INSERT statement to include email fields
3. ✅ Added email notification sending for BOTH scenarios:
   - When creating NEW dockets (manual manifest entry)
   - When adding EXISTING dockets to manifest
4. ✅ Used `@sendEmail()` to suppress warnings if mail() fails

**Backup Created:** `manifest_save_old_backup.php` (in case you need to roll back)

---

## 📝 **How Emails Work Now**

### **1. Docket Creation (Manual Manifest Entry)**
When you create a NEW docket via manifest:
- Email fields (`client_email`, `company_email`) are saved to database
- **Company** receives "Docket Created" email immediately
- Email includes: Docket number, tracking link, shipment details

### **2. Adding Existing Docket to Manifest**
When you add an EXISTING docket to a manifest:
- If the existing docket has `company_email`, they receive "Docket Added to Manifest" notification
- Existing email addresses are preserved
- New email addresses can be added if fields were previously empty

### **3. Status Updates**
When you update docket status:
- **Out for Delivery** → Client receives email with vehicle & driver details
- **Delivered** → Both Client AND Company receive confirmation emails
- **Delayed** → Client receives email with delay reason

---

## 🧪 **Testing Instructions**

### **Test 1: Create Docket with Email (Manual Manifest)**

1. Go to **Manifest Management** → **Create New Manifest**
2. Choose destination office
3. Enable **Manual Entry Mode**
4. Fill in docket details:
   ```
   Doc No: TEST001
   Client Name: Test Receiver
   Client Email: client@example.com    ← IMPORTANT!
   Company Email: company@example.com   ← IMPORTANT!
   Client Address: 123 Test Street
   Item: Test Package
   Box: 1
   Weight: 5
   Rate: 100
   ```
5. Enter car and driver details
6. Click **Save Manifest**

**Expected Result:**
- ✅ Manifest created successfully
- ✅ Email sent to `company@example.com` with subject: "📝 Docket Created - #TEST001"
- ✅ Check error logs for: `"Docket created email sent to company: company@example.com"`

### **Test 2: Update Existing Docket Status**

1. Go to **Delivery Status** or **View Register**
2. Find the docket TEST001
3. Click on status badge or update button
4. Change status to **"Out for Delivery"**
5. Enter:
   - Vehicle Number: KA-01-1234
   - Driver Name: John Driver
6. Click **Update Status**

**Expected Result:**
- ✅ Status updated successfully
- ✅ Email sent to `client@example.com` with subject: "🚚 Your Shipment is Out for Delivery"
- ✅ Email includes vehicle number and driver name

### **Test 3: Mark as Delivered**

1. Update same docket to **"Delivered"** status
2. Upload a POD file
3. Click **Update Status**

**Expected Result:**
- ✅ Status updated successfully
- ✅ Email sent to `client@example.com` with subject: "✅ Your Shipment Has Been Delivered"
- ✅ Email sent to `company@example.com` with subject: "✅ Delivery Completed"

---

## 🔍 **How to Check if Emails Were Sent**

### **Method 1: Check Error Logs**
Location: `/xampp/php/logs/php_error_log`

Search for:
```
Docket created email sent to company:
Out for Delivery email sent to client:
Delivered email sent to client:
Delivered email sent to company:
```

### **Method 2: Check Database**
```sql
SELECT docket_id, doc_no, client_email, company_email, status
FROM docket_details
WHERE doc_no = 'TEST001';
```

**Verify:**
- ✅ `client_email` is NOT NULL
- ✅ `company_email` is NOT NULL
- ✅ Emails are valid format

### **Method 3: Test PHP mail() Function**
Create a test file: `test_email.php`
```php
<?php
$to = "your-email@example.com";
$subject = "Test Email";
$message = "This is a test email from XAMPP";
$headers = "From: onestepup@northsuperfastservice.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send.";
}
?>
```

---

## ⚠️ **Important Notes**

### **Email Requirements:**
1. ✅ **Client Email** must be filled when creating dockets (for client notifications)
2. ✅ **Company Email** must be filled when creating dockets (for company notifications)
3. ✅ PHP `mail()` function must be configured on your server
4. ✅ XAMPP sendmail must be set up (for Windows)

### **XAMPP Sendmail Configuration:**
Edit `C:\xampp\sendmail\sendmail.ini`:
```ini
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=your-email@gmail.com
auth_password=your-app-password
force_sender=onestepup@northsuperfastservice.com
```

Edit `C:\xampp\php\php.ini`:
```ini
[mail function]
SMTP=smtp.gmail.com
smtp_port=587
sendmail_from=onestepup@northsuperfastservice.com
sendmail_path="C:\xampp\sendmail\sendmail.exe -t"
```

---

## 🚫 **When Emails WON'T Be Sent**

1. ❌ Email fields are empty in database
2. ❌ Email address is invalid format (not xxx@yyy.zzz)
3. ❌ Bulk status updates (only single updates send emails)
4. ❌ PHP mail() function is not configured
5. ❌ Server/firewall blocks outgoing emails

---

## 📊 **Email Sending Logic**

```
CREATE DOCKET (via manifest_save.php)
    ↓
Check if company_email exists & is valid
    ↓
YES → Send "Docket Created" email
NO  → Skip (no email sent)

---

UPDATE STATUS (via update_docket_status.php or delivery_status.php)
    ↓
Check status type
    ↓
├─ Out for Delivery → Send to client_email (if exists)
├─ Delivered → Send to BOTH client_email & company_email (if exist)
└─ Delayed → Send to client_email (if exists)
```

---

## 🔧 **Troubleshooting**

### **Problem: No emails received**
**Solutions:**
1. Check error logs for email send attempts
2. Verify email addresses in database are not NULL
3. Test PHP mail() function independently
4. Check spam/junk folder
5. Verify XAMPP sendmail configuration

### **Problem: Emails in database are NULL**
**Solution:**
- The manifest form needs to be updated to include email input fields
- Emails can be added manually in the database for testing:
```sql
UPDATE docket_details
SET client_email = 'client@example.com',
    company_email = 'company@example.com'
WHERE doc_no = 'TEST001';
```

### **Problem: Mail function errors**
**Solutions:**
1. Check PHP error logs
2. Ensure sendmail is running
3. Verify SMTP credentials
4. Check firewall settings

---

## ✅ **Success Checklist**

- [ ] Dockets created with email addresses saved
- [ ] "Docket Created" email received by company
- [ ] "Out for Delivery" email received by client
- [ ] "Delivered" emails received by both client & company
- [ ] "Delayed" email received by client
- [ ] Error logs show successful email sends
- [ ] Database contains valid email addresses

---

**Last Updated:** January 2025
**Fixed By:** Claude Code
**Version:** 2.0 (Email Support Added)
