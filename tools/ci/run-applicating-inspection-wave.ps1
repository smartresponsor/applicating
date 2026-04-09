$steps = @(
    'composer report:owner-overlap',
    'composer report:route-inventory',
    'composer report:class-alias',
    'composer report:runtime-proof',
    'composer report:engineering-drift',
    'composer report:pipeline-wiring',
    'composer report:qodana-wiring',
    'composer report:publish-guard',
    'composer report:fixture-publish-guard'
)
$steps | Set-Content -Encoding UTF8 'report/inspection/applicating-inspection-wave-steps.txt'
Write-Host ($steps -join "`n")
