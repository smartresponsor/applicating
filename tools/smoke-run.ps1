Param([string]$BaseUrl = "http://localhost:8080")
Write-Host "Running Category RC9 smoke..." -ForegroundColor Cyan
$env:BASE_URL = $BaseUrl
k6 run ops/smoke/category-smoke-k6.js
