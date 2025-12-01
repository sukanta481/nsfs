$filePath = "admin\add_trip_modern.php"

# Read the file
$content = Get-Content $filePath -Raw -Encoding UTF8

# Define the old JavaScript code (the buggy version)
$oldCode = @"
            // Setup driver phone auto-fill
            const driverSelect = document.getElementById('driver_select');
            const driverPhoneInput = document.getElementById('driver_phone');
            
            if (driverSelect && driverPhoneInput) {
                driverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const phone = selectedOption.getAttribute('data-phone') || '';
                    driverPhoneInput.value = phone;
                });
            }
"@

# Define the new JavaScript code (the fixed version)
$newCode = @"
            // Setup driver phone auto-fill for datalist input
            const driverNameInput = document.getElementById('driverNameInput');
            const driverPhoneInput = document.getElementById('driver_phone');
            const driverIdHidden = document.getElementById('driverIdHidden');
            const driverList = document.getElementById('driverList');
            
            if (driverNameInput && driverPhoneInput && driverList) {
                driverNameInput.addEventListener('input', function() {
                    const value = this.value;
                    const options = driverList.querySelectorAll('option');
                    
                    // Find matching option from datalist
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
                
                // Clear driver ID if manually typed name not in list
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
                    
                    if (!found) {
                        driverIdHidden.value = '';
                    }
                });
            }
"@

# Perform the replacement
$newContent = $content.Replace($oldCode, $newCode)

# Check if replacement was successful
if ($content -eq $newContent) {
    Write-Host "ERROR: No replacement was made. The old code pattern was not found in the file." -ForegroundColor Red
    exit 1
}
else {
    # Save the file
    Set-Content $filePath -Value $newContent -Encoding UTF8 -NoNewline
    Write-Host "SUCCESS: Driver phone auto-fill has been fixed!" -ForegroundColor Green
    Write-Host "The file has been updated: $filePath"
}
