@echo off
REM Fix delay reasons query in list_register_new.php

echo Fixing delay reasons query...

REM Backup original file
copy "admin\list_register_new.php" "admin\list_register_new.php.bak"

REM Use PowerShell to replace the query
powershell -Command "(Get-Content 'admin\list_register_new.php') -replace 'SELECT reason_id, reason_category, reason_text FROM tbl_delay_reasons ORDER BY', 'SELECT reason_id, reason_category, reason_text FROM tbl_delay_reasons WHERE is_active = 1 ORDER BY' | Set-Content 'admin\list_register_new.php'"

echo Fix applied successfully!
echo Original file backed up to admin\list_register_new.php.bak
pause
