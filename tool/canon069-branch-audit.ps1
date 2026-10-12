# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
param([string]$Base = 'master')
$ErrorActionPreference = 'Stop'
$root = (git rev-parse --show-toplevel).Trim()
if ($LASTEXITCODE -ne 0) { throw 'Not a Git repository' }
Set-Location $root
$refs = @(git for-each-ref --format='%(refname)' refs/heads refs/remotes)
if ($LASTEXITCODE -ne 0) { throw 'Unable to enumerate Git refs' }
foreach ($ref in $refs) {
    if ($ref -eq 'refs/heads/master' -or $ref -eq 'refs/remotes/origin/HEAD') { continue }
    $counts = (git rev-list --left-right --count "$Base...$ref").Trim() -split '\s+'
    if ($LASTEXITCODE -ne 0) { throw "Cannot compare $ref" }
    $unique = [int]$counts[1]
    if ($unique -eq 0) { continue }
    Write-Output "BRANCH $ref UNIQUE=$unique BASE_ONLY=$($counts[0])"
    $commits = @(git log --format='%h %s' "$Base..$ref" -n 5)
    foreach ($entry in $commits) { Write-Output "  COMMIT $entry" }
    $mergeBase = (git merge-base $Base $ref 2>$null)
    if ($LASTEXITCODE -ne 0) {
        Write-Output '  HISTORY unrelated; reviewing complete tree differences'
        $files = @(git diff --name-status $Base $ref)
    } else {
        $files = @(git diff --name-status "$Base...$ref")
    }
    foreach ($entry in ($files | Select-Object -First 25)) { Write-Output "  FILE $entry" }
    if ($files.Count -gt 25) { Write-Output "  MORE_FILES $($files.Count - 25)" }
}
