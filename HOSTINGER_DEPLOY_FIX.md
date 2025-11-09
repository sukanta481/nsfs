# Hostinger Auto-Deploy Troubleshooting Guide

## Problem
Auto-deploy from GitHub to Hostinger is not working. Changes pushed to GitHub are not appearing on the live server.

## Quick Fixes

### 1. Manual Deploy via Hostinger Panel
1. Login to Hostinger hPanel
2. Go to **Git** or **Deployments** section
3. Click **"Pull from repository"** or **"Deploy Now"**
4. Wait for deployment to complete
5. Check your website

### 2. Check Webhook Status on GitHub
1. Go to: https://github.com/sukanta481/nsfs/settings/hooks
2. Find the Hostinger webhook (usually has your domain name)
3. Click on it
4. Check "Recent Deliveries" tab
5. Look for:
   - ✓ Green checkmarks = webhook working
   - ✗ Red X = webhook failing
6. If failing, click the failed delivery to see error details

### 3. Re-enable Auto-Deploy on Hostinger
Sometimes the connection breaks. To fix:
1. Login to Hostinger hPanel
2. Go to **Git** section
3. **Disconnect** the repository
4. **Reconnect** it again:
   - Repository URL: `https://github.com/sukanta481/nsfs`
   - Branch: `main`
   - Deploy path: `/public_html/north_super_fast_service/application`
5. Enable **"Auto-deploy on push"**

### 4. Manual Deploy via SSH (If you have SSH access)

**Option A: Using deploy.sh script**
```bash
cd /home2/workuidy/public_html/north_super_fast_service/application
chmod +x deploy.sh
./deploy.sh
```

**Option B: Direct git pull**
```bash
cd /home2/workuidy/public_html/north_super_fast_service/application
git pull origin main
```

### 5. Check Git Configuration on Server
```bash
cd /home2/workuidy/public_html/north_super_fast_service/application
git status
git remote -v
git branch -a
```

## Common Issues & Solutions

### Issue 1: Merge Conflicts
**Symptom:** Deploy fails with "merge conflict" error

**Solution:**
```bash
cd /home2/workuidy/public_html/north_super_fast_service/application
git stash  # Save any local changes
git pull origin main
git stash pop  # Restore local changes if needed
```

### Issue 2: Permission Denied
**Symptom:** Can't write files or git pull fails

**Solution:**
```bash
cd /home2/workuidy/public_html/north_super_fast_service/application
git config --global --add safe.directory '*'
```

### Issue 3: Detached HEAD State
**Symptom:** Git says "detached HEAD"

**Solution:**
```bash
git checkout main
git pull origin main
```

### Issue 4: Authentication Failed
**Symptom:** Git asks for username/password

**Solution:**
- Reconnect repository in Hostinger hPanel
- Make sure repository is public OR
- Use deploy key (Hostinger should handle this automatically)

## Verify Deployment
After deploying, check:
1. Visit your website: https://northsuperfastservice.com/admin/add_user.php
2. Check file timestamps via SSH:
   ```bash
   ls -la /home2/workuidy/public_html/north_super_fast_service/application/admin/add_user.php
   ```
3. Verify latest commit on server:
   ```bash
   cd /home2/workuidy/public_html/north_super_fast_service/application
   git log -1
   ```
   Should show: `208d885` (your latest commit)

## Files Included
- `deploy.sh` - Manual deployment script
- Upload this to your server for easy manual deployments

## Need Help?
If auto-deploy still doesn't work:
1. Contact Hostinger support with these details:
   - Repository: github.com/sukanta481/nsfs
   - Branch: main
   - Last successful deploy date
   - Error messages from webhook deliveries
2. Consider using manual deploys until fixed
3. Check Hostinger's Git deployment documentation
