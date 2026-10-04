param(
    [int] $Port = 8000
)

$script = Join-Path (Split-Path -Parent $PSScriptRoot) 'tools/runtime/application_test_server.ps1'
& $script -Port $Port

exit $LASTEXITCODE
