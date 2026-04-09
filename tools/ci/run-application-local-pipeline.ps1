param(
    [switch]$IncludeSmokes,
    [switch]$IncludeReports,
    [switch]$IncludeSecurity,
    [switch]$FailOnErrors
)

$steps = @(
    'composer qa:env',
    'composer qa:style',
    'composer qa:static'
)
if ($IncludeSmokes) {
    $steps += @('composer smoke:runtime', 'composer smoke:fixtures', 'composer smoke:container', 'composer smoke:doctrine', 'composer smoke:fixture-load', 'composer smoke:admin', 'composer smoke:functional-readiness', 'composer smoke:postgres-matrix')
}
if ($IncludeReports) {
    $steps += @('composer report:all')
}
if ($IncludeSecurity) {
    $steps += @('composer report:qodana-wiring')
}
$steps | Set-Content -Encoding UTF8 'report/inspection/application-local-pipeline-steps.txt'
Write-Host ($steps -join "`n")
