# 📧 SMTP Email Setup Guide - Complete Configuration

## ⚠️ **IMPORTANT: Email Configuration Required!**

You are **100% correct** - the current email system won't work without proper SMTP configuration!

The basic `mail()` function requires server-side configuration that XAMPP doesn't have by default. We need to use **PHPMailer with SMTP** instead.

---

## 🎯 **Two Solutions Available**

### **Option 1: PHPMailer with SMTP (RECOMMENDED)**
✅ Works without server configuration
✅ Uses your Hostinger SMTP settings
✅ More reliable and secure
✅ Better error handling

### **Option 2: XAMPP Sendmail Configuration**
❌ Requires manual XAMPP configuration
❌ Less reliable
❌ More complex setup

**We'll use Option 1 (PHPMailer)!**

---

## 📥 **Step 1: Download PHPMailer**

### **Method A: Using Composer (Recommended)**

```bash
# Navigate to admin folder
cd c:\xampp\htdocs\nsfs\admin

# Install PHPMailer via Composer
composer require phpmailer/phpmailer
```

### **Method B: Manual Download**

1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
2. Extract the ZIP file
3. Copy the `PHPMailer-master/src` folder to: `c:\xampp\htdocs\nsfs\admin\PHPMailer\src\`

Your folder structure should be:
```
c:\xampp\htdocs\nsfs\admin\
├── PHPMailer/
│   └── src/
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
├── email_config_smtp.php (NEW FILE)
├── email_templates.php
└── ... (other files)
```

---

## 🔐 **Step 2: Configure SMTP Credentials**

Open **`email_config_smtp.php`** and update line 19:

```php
// ⚠️ REPLACE THIS LINE:
define('SMTP_PASSWORD', 'YOUR_PASSWORD_HERE');

// WITH YOUR ACTUAL HOSTINGER EMAIL PASSWORD:
define('SMTP_PASSWORD', 'your-actual-password-here');
```

**SMTP Settings (Already Configured):**
```php
Host: smtp.hostinger.com
Port: 465 (SSL)
Username: onestepup@northsuperfastservice.com
Password: [YOUR PASSWORD HERE]
```

---

## 🔄 **Step 3: Update Files to Use SMTP Config**

Replace the old `email_config.php` reference with `email_config_smtp.php` in all files:

### **Files to Update:**

1. **update_docket_status.php** (Line 4)
2. **delivery_status.php** (Line 5)
3. **manifest_save.php** (Line 11)
4. **save_trip_modern.php** (Line 4)
5. **email_templates.php** (Line 2)

**Change this:**
```php
require 'email_config.php';
```

**To this:**
```php
require 'email_config_smtp.php';
```

---

## 🚀 **Step 4: Quick Update Script**

I can create a script to automatically update all files. Would you like me to do this now?

Or you can manually run these replacements:

### **Manual Replacement:**

**File 1:** `update_docket_status.php`
```php
// Line 4: Change from
require 'conn.php';
require 'email_templates.php';

// To:
require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';
```

**File 2:** `delivery_status.php`
```php
// Line 5: Change from
require 'conn.php';
require 'email_templates.php';

// To:
require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';
```

**File 3:** `manifest_save.php`
```php
// Line 11: Change from
require 'conn.php';
require 'email_templates.php';

// To:
require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';
```

**File 4:** `save_trip_modern.php`
```php
// Line 4: Change from
require_once 'DocketDetailsManager.php';
require_once 'email_templates.php';

// To:
require_once 'DocketDetailsManager.php';
require_once 'email_config_smtp.php';
require_once 'email_templates.php';
```

**File 5:** `email_templates.php`
```php
// Line 2: Change from
require_once 'email_config.php';

// To:
require_once 'email_config_smtp.php';
```

---

## 🧪 **Step 5: Test Email Sending**

Create a test file: `c:\xampp\htdocs\nsfs\admin\test_email_smtp.php`

```php
<?php
require 'email_config_smtp.php';

// Test email
$to = "your-email@example.com"; // ⚠️ Replace with your email
$subject = "Test Email from NSFS";
$message = "<h1>Test Email</h1><p>If you receive this, SMTP is working!</p>";

if (sendEmail($to, $subject, getEmailTemplate($message), "Test User")) {
    echo "✅ Email sent successfully! Check your inbox.";
} else {
    echo "❌ Email failed. Check error logs.";
}
?>
```

**Run the test:**
1. Open browser: `http://localhost/nsfs/admin/test_email_smtp.php`
2. Check your email inbox
3. Check error logs: `c:\xampp\php\logs\php_error_log`

---

## 🔍 **Troubleshooting**

### **Error: Class 'PHPMailer\PHPMailer\PHPMailer' not found**
**Solution:** PHPMailer is not installed correctly
- Download PHPMailer manually and place in `admin/PHPMailer/src/`
- Or install via Composer

### **Error: SMTP connect() failed**
**Solution:** Wrong SMTP credentials
- Check username: `onestepup@northsuperfastservice.com`
- Check password in `email_config_smtp.php`
- Verify Hostinger email account is active

### **Error: Could not authenticate**
**Solution:** Password is incorrect
- Login to Hostinger webmail to verify password
- Update `SMTP_PASSWORD` in `email_config_smtp.php`

### **Emails go to spam**
**Solution:**
- Add SPF record in Hostinger DNS settings
- Enable DKIM in Hostinger email settings
- Ask recipients to whitelist `onestepup@northsuperfastservice.com`

### **No error but email not received**
**Solution:**
- Check spam/junk folder
- Check error logs: `c:\xampp\php\logs\php_error_log`
- Verify recipient email is valid

---

## ✅ **Verification Checklist**

- [ ] PHPMailer downloaded and placed in `admin/PHPMailer/src/`
- [ ] SMTP password updated in `email_config_smtp.php`
- [ ] All files updated to use `email_config_smtp.php`
- [ ] Test email sent successfully
- [ ] Production emails working (check with actual docket)

---

## 🎨 **Alternative: Use Gmail SMTP (For Testing)**

If Hostinger SMTP doesn't work, you can use Gmail temporarily:

```php
// In email_config_smtp.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // TLS port
define('SMTP_USERNAME', 'your-gmail@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password, not regular password
```

**Gmail App Password Setup:**
1. Go to: https://myaccount.google.com/security
2. Enable 2-Step Verification
3. Generate App Password for "Mail"
4. Use that 16-character password

---

## 📊 **SMTP Configuration Comparison**

| Provider | Host | Port | Security | Speed |
|----------|------|------|----------|-------|
| **Hostinger** | smtp.hostinger.com | 465 | SSL | ⭐⭐⭐⭐⭐ |
| **Gmail** | smtp.gmail.com | 587 | TLS | ⭐⭐⭐⭐ |
| **XAMPP Sendmail** | localhost | 25 | None | ⭐⭐ |

**Recommendation:** Use Hostinger SMTP (best for production)

---

## 🔒 **Security Best Practices**

1. ✅ **Never commit passwords to Git**
   - Add `email_config_smtp.php` to `.gitignore`

2. ✅ **Use environment variables (optional)**
   ```php
   define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
   ```

3. ✅ **Enable SSL/TLS**
   - Always use port 465 (SSL) or 587 (TLS)

4. ✅ **Validate email addresses**
   - Already implemented in `sendEmail()` function

---

## 📞 **Need Help?**

If you encounter issues:

1. **Check error logs:** `c:\xampp\php\logs\php_error_log`
2. **Test SMTP credentials:** Use Gmail/Outlook to verify
3. **Verify Hostinger email:** Login to webmail
4. **Check firewall:** Ensure port 465 is not blocked

---

**Once PHPMailer is installed and configured, your email system will work perfectly!** 🚀

**Next Steps:**
1. Download PHPMailer
2. Update SMTP password
3. Update all files to use SMTP config
4. Test with `test_email_smtp.php`
5. Enjoy automatic email notifications! ✉️
