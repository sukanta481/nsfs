# Email Notifications System - Implementation Guide

## 📧 Overview

An automated email notification system has been implemented for the North Super Fast Service docket management system. Emails are sent from `onestepup@northsuperfastservice.com` to clients (consignees) and companies (consignors) at specific stages of the delivery process.

---

## 🎯 Email Notification Triggers

### **1. CLIENT (Consignee/Receiver) Emails**

#### **Out for Delivery** 🚚
- **When**: Status changed to "Out for Delivery"
- **To**: Client's email (client_email)
- **Contains**:
  - Docket number and delivery address
  - Vehicle number
  - Driver name
  - Tracking link to view status

#### **Delivered** ✅
- **When**: Status changed to "Delivered"
- **To**: Client's email (client_email)
- **Contains**:
  - Delivery confirmation with date/time
  - Docket summary
  - Link to view proof of delivery
  - Feedback request

#### **Delayed** ⏰
- **When**: Status changed to "Delayed"
- **To**: Client's email (client_email)
- **Contains**:
  - Delay notification
  - Reason for delay
  - Tracking link
  - Customer support information

---

### **2. COMPANY (Consignor/Sender) Emails**

#### **Docket Created** 📝
- **When**: New docket is created via manifest
- **To**: Company's email (company_email)
- **Contains**:
  - Docket number and creation date
  - Shipment details (sender, receiver, addresses)
  - Tracking link

#### **Delivered** ✅
- **When**: Status changed to "Delivered"
- **To**: Company's email (company_email)
- **Contains**:
  - Delivery confirmation
  - Delivery summary
  - Link to view proof of delivery

---

## 📁 Files Created/Modified

### **New Files**
1. **`admin/email_config.php`**
   - Email configuration and send function
   - Uses PHP's built-in `mail()` function
   - Email from: `onestepup@northsuperfastservice.com`
   - Tracking URL generator

2. **`admin/email_templates.php`**
   - All HTML email templates
   - Functions for each notification type:
     - `getOutForDeliveryEmailTemplate()`
     - `getDeliveredEmailTemplate()`
     - `getDelayedEmailTemplate()`
     - `getDocketCreatedEmailTemplate()`
     - `getCompanyDeliveredEmailTemplate()`

### **Modified Files**
1. **`admin/update_docket_status.php`**
   - Added email notifications after status update
   - Lines 246-297: Email sending logic

2. **`admin/delivery_status.php`**
   - Added email notifications in embedded status update handler
   - Lines 223-271: Email sending logic

3. **`admin/manifest_save.php`**
   - Added email notification when new dockets are created
   - Lines 288-303: Docket creation email logic

---

## ✅ Testing Instructions

### **Prerequisites**
Before testing, ensure:
1. Your server has email functionality enabled
2. PHP's `mail()` function is working
3. Client and company email addresses are stored in `docket_details` table

### **Test 1: Docket Created Email (to Company)**
1. Go to Manifest Management
2. Create a new manifest with manual entry mode
3. Enter docket details including company email in `company_email` field
4. Save the manifest
5. **Expected**: Company receives "Docket Created" email

### **Test 2: Out for Delivery Email (to Client)**
1. Go to any docket view page or delivery status page
2. Update status to "Out for Delivery"
3. Enter vehicle number and driver name (required)
4. Ensure client email exists in `client_email` field
5. Submit the status update
6. **Expected**: Client receives "Out for Delivery" email with vehicle details

### **Test 3: Delivered Email (to Client & Company)**
1. Update a docket status to "Delivered"
2. Upload POD file (required)
3. Ensure both `client_email` and `company_email` are set
4. Submit the status update
5. **Expected**:
   - Client receives "Delivered" email
   - Company receives "Delivery Completed" email

### **Test 4: Delayed Email (to Client)**
1. Update a docket status to "Delayed"
2. Select a delay reason from dropdown (required)
3. Ensure `client_email` is set
4. Submit the status update
5. **Expected**: Client receives "Delayed" email with reason

---

## 🔧 Configuration

### **Email Settings** (in `admin/email_config.php`)

```php
define('EMAIL_FROM', 'onestepup@northsuperfastservice.com');
define('EMAIL_FROM_NAME', 'North Super Fast Service');
define('EMAIL_REPLY_TO', 'onestepup@northsuperfastservice.com');
define('TRACKING_BASE_URL', 'http://..../track.php?doc_no=');
```

### **Required Database Columns**
Ensure these columns exist in `docket_details` table:
- `client_email` - Client's email address
- `company_email` - Company's email address
- `client_name` - Client name (for personalization)
- `company_name` - Company name (for personalization)
- `doc_no` - Docket number
- `client_address` - Delivery address
- All other standard docket fields

---

## 📝 Email Template Features

All emails include:
- ✅ Modern, responsive HTML design
- ✅ Professional North Super Fast Service branding
- ✅ Gradient headers with company colors
- ✅ Clickable tracking links
- ✅ Mobile-friendly layout
- ✅ Clear call-to-action buttons
- ✅ Proper email headers and reply-to configuration

---

## 🐛 Troubleshooting

### **Emails not sending?**
1. Check PHP error logs: `/xampp/php/logs/php_error_log`
2. Verify email addresses are valid in database
3. Test PHP mail() function independently
4. Check XAMPP sendmail configuration (if using XAMPP)
5. Review error_log entries for email send attempts

### **Email goes to spam?**
1. Add `onestepup@northsuperfastservice.com` to whitelist
2. Configure SPF and DKIM records for your domain
3. Ensure "From" address matches your domain

### **Tracking links not working?**
1. Verify `TRACKING_BASE_URL` in `email_config.php`
2. Ensure `track.php` exists in your root directory
3. Check that tracking page can access docket details

---

## 📊 Email Logs

All email sends are logged using PHP's `error_log()`:
- Success: `"Out for Delivery email sent to client: email@example.com"`
- Failure: `"Failed to send email to: email@example.com"`

Check logs at: `/xampp/php/logs/php_error_log` (Windows XAMPP)

---

## 🔒 Security Notes

1. **Email validation**: All emails are validated before sending
2. **SQL injection prevention**: All queries use `mysqli_real_escape_string()`
3. **XSS prevention**: All output uses `htmlspecialchars()`
4. **No sensitive data**: Emails don't contain payment information
5. **Sender authentication**: Emails sent only from verified address

---

## 🚀 Future Enhancements (Optional)

Consider adding:
- Email delivery status tracking
- Multiple recipient support (CC/BCC)
- Email templates customization via admin panel
- SMS notifications integration
- Email queue system for high volume
- WhatsApp notification integration

---

## 📞 Support

For issues or questions:
- Check the error logs first
- Review this documentation
- Test with a simple docket update
- Verify email addresses in database

---

**Implementation Date**: January 2025
**Developer**: Claude Code
**Version**: 1.0
