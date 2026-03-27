Param([Parameter(Mandatory=$true)][string]$Target)
$Kit = Resolve-Path (Join-Path $PSScriptRoot "..")
$Payload = Join-Path $Kit "payload"
New-Item -ItemType Directory -Path $Target -Force | Out-Null
Set-Location $Target
if (-not (Test-Path ".git")) { git init | Out-Null }
git checkout -B master | Out-Null
git config user.name "Oleksandr Tishchenko"
git config user.email "17111337+taa0662621456@users.noreply.github.com"
Get-ChildItem -Path $Payload -Directory | Sort-Object Name | ForEach-Object {
  $step = $_.FullName
  robocopy $step . /E /NFL /NDL /NJH /NJS /NP | Out-Null
  $msg = "Import " + $_.Name; $date = $null
  $j = Join-Path $step "report\COMMIT.json"
  if (Test-Path $j) { try { $d = Get-Content $j -Raw | ConvertFrom-Json; if ($d.message) { $msg=$d.message }; if ($d.when) {$date=$d.when} } catch {} }
  if ($date) { $env:GIT_AUTHOR_DATE=$date; $env:GIT_COMMITTER_DATE=$date }
  git add -A; git commit -m $msg --allow-empty | Out-Null
}
Write-Host "Done."
