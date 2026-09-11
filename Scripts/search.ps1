Write-Host "=== Case-Sensitive Recursive Search Tool ===" -ForegroundColor Cyan

# Default directory = current working directory
$defaultDir = $PWD.Path
Write-Host "Current directory detected: $defaultDir" -ForegroundColor Yellow

# Ask for directory (optional)
$dirInput = Read-Host "Enter directory to search (leave blank to use current)"
$dir = if ([string]::IsNullOrWhiteSpace($dirInput)) { $defaultDir } else { $dirInput }

if (-not (Test-Path $dir)) {
    Write-Host "Directory does not exist. Exiting." -ForegroundColor Red
    exit
}

# Ask for search term
$term = Read-Host "Enter the case-sensitive search term"

# Ask for literal or regex mode
$mode = Read-Host "Use literal match? (y/n)"
$useLiteral = $mode -match '^(y|yes)$'

Write-Host ""
Write-Host "Searching recursively in: $dir" -ForegroundColor Yellow
Write-Host "Case-sensitive term: $term" -ForegroundColor Yellow
Write-Host ""

# Build search pattern
if ($useLiteral) {
    $pattern = [regex]::Escape($term)
} else {
    $pattern = $term
}

# Perform search
Get-ChildItem -Path $dir -Recurse -File | ForEach-Object {
    $matches = Select-String -Path $_.FullName -Pattern $pattern -CaseSensitive
    if ($matches) {
        foreach ($m in $matches) {
            Write-Host ("[{0}] {1}: {2}" -f $m.LineNumber, $_.FullName, $m.Line) -ForegroundColor Green
        }
    }
}

Write-Host ""
Write-Host "Search complete." -ForegroundColor Cyan
