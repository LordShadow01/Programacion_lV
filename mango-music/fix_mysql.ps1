$dataDir = "C:\xampp\mysql\data"
$backupDir = "C:\xampp\mysql\backup"
$oldDir = "C:\xampp\mysql\data_old"

if (Test-Path $oldDir) {
    Remove-Item -Recurse -Force $oldDir
}

Rename-Item -Path $dataDir -NewName "data_old"
Copy-Item -Path $backupDir -Destination $dataDir -Recurse

$exclude = @("mysql", "performance_schema", "phpmyadmin")

Get-ChildItem -Path $oldDir | ForEach-Object {
    if ($_.PSIsContainer -and $exclude -notcontains $_.Name) {
        Copy-Item -Path $_.FullName -Destination "$dataDir\$($_.Name)" -Recurse -Force
    }
}

if (Test-Path "$oldDir\ibdata1") {
    Copy-Item -Path "$oldDir\ibdata1" -Destination "$dataDir\ibdata1" -Force
}

Write-Host "XAMPP MySQL Data fixed successfully!"
