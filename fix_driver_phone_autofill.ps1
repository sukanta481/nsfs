// Create a backup
Copy-Item "admin\add_trip_modern.php" "admin\add_trip_modern.php.bak"

// Read the file
$content = Get-Content "admin\add_trip_modern.php" -Raw

// Replace the broken driver select code with proper datalist input handling
$old = @"
            // Setup driver phone auto-fill
            const driverSelect = document.getElementById\('driver_select'\);
            const driverPhoneInput = document.getElementById\('driver_phone'\);
            
            if \(driverSelect && driverPhoneInput\) {
                driverSelect.addEventListener\('change', function\(\) {
                    const selectedOption = this.options\[this.selectedIndex\];
                    const phone = selectedOption.getAttribute\('data-phone'\) \|\| '';
                    driverPhoneInput.value = phone;
                }\);
            }
"@

$new = @"
            // Setup driver phone auto-fill for datalist input
            const driverNameInput = document.getElementById('driverNameInput');
            const driverPhoneInput = document.getElementById('driver_phone');
            const driverIdHidden = document.getElementById('driverIdHidden');
            const driverList = document.getElementById('driverList');
            
            if (driverNameInput && driverPhoneInput && driverList) {
                driverNameInput.addEventListener('input', function() {
                    const value = this.value;
                    const options = driverList.querySelectorAll('option');
                    
                    // Find matching option
                    for (let option of options) {
                        if (option.value === value) {
                            const phone = option.getAttribute('data-phone') || '';
                            const driverId = option.getAttribute('data-id') || '';
                            driverPhoneInput.value = phone;
                            driverIdHidden.value = driverId;
                            break;
                        }
                    }
                });
                
                // Also handle when user types manually (clear phone if no match)
                driverNameInput.addEventListener('blur', function() {
                    const value = this.value;
                    const options = driverList.querySelectorAll('option');
                    let found = false;
                    
                    for (let option of options) {
                        if (option.value === value) {
                            found = true;
                            break;
                        }
                    }
                    
                    // If manually typed and not in list, clear hidden ID but keep phone if already filled
                    if (!found) {
                        driverIdHidden.value = '';
                    }
                });
            }
"@

// Apply the replacement
$content = $content -replace [regex]::Escape($old), $new

// Write back to file
Set-Content "admin\add_trip_modern.php" $content -NoNewline

Write-Host "Driver phone auto-fill fixed successfully!"
