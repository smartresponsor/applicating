[CmdletBinding()]
param(
    [string]$ProjectRoot = '',
    [string]$ReportRoot = '',
    [switch]$IncludeSmokes,
    [switch]$IncludeReports,
    [switch]$IncludeSecurity,
    [switch]$FailOnErrors,
    [switch]$Quiet
)

$ErrorActionPreference = 'Stop'
if (Get-Variable -Name PSNativeCommandUseErrorActionPreference -ErrorAction SilentlyContinue) {
    $PSNativeCommandUseErrorActionPreference = $false
}

$runId = Get-Date -Format 'yyyyMMdd-HHmmss'

$scriptRoot = $PSScriptRoot
if ([string]::IsNullOrWhiteSpace($scriptRoot) -and $MyInvocation.MyCommand.Path) {
    $scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
}
if ([string]::IsNullOrWhiteSpace($scriptRoot)) {
    $scriptRoot = (Get-Location).Path
}
if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = [System.IO.Path]::GetFullPath((Join-Path $scriptRoot '..\..'))
}

function Write-Section {
    param([string]$Message)
    if (-not $Quiet) {
        Write-Host "==> $Message" -ForegroundColor Cyan
    }
}

function New-Step {
    param(
        [string]$Name,
        [string]$Command,
        [string]$Category = 'qa'
    )

    [pscustomobject]@{
        Name = $Name
        Command = $Command
        Category = $Category
    }
}

function Classify-StepResult {
    param(
        [int]$ExitCode,
        [string[]]$Output
    )

    if ($ExitCode -eq 0) {
        return 'passed'
    }

    $joined = ($Output -join [Environment]::NewLine)
    if ($joined -match 'Could not open input file') {
        return 'missing-artifact'
    }
    if ($joined -match 'requires php >=|your php version') {
        return 'environment'
    }
    if ($joined -match 'is not recognized as an internal or external command') {
        return 'environment'
    }
    if ($joined -match 'CommandNotFoundException|command not found|not installed') {
        return 'environment'
    }
    if ($joined -match 'Mandatory arguments:|Cannot find specified rule-set') {
        return 'misconfigured'
    }

    return 'failed'
}

function Invoke-StepCommand {
    param([string]$Command)

    $psi = [System.Diagnostics.ProcessStartInfo]::new()
    $psi.FileName = 'cmd.exe'
    $psi.Arguments = "/d /c $Command"
    $psi.WorkingDirectory = $rootPath
    $psi.UseShellExecute = $false
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.CreateNoWindow = $true

    $process = [System.Diagnostics.Process]::new()
    $process.StartInfo = $psi
    [void]$process.Start()

    $stdOut = $process.StandardOutput.ReadToEnd()
    $stdErr = $process.StandardError.ReadToEnd()
    $process.WaitForExit()

    $lines = [System.Collections.Generic.List[string]]::new()
    foreach ($chunk in @($stdOut, $stdErr)) {
        if ([string]::IsNullOrWhiteSpace($chunk)) {
            continue
        }

        foreach ($line in ($chunk -split "`r?`n")) {
            if ($line -ne '') {
                $lines.Add($line) | Out-Null
            }
        }
    }

    return [pscustomobject]@{
        ExitCode = $process.ExitCode
        Output = @($lines)
    }
}

$rootPath = (Resolve-Path $ProjectRoot).Path
if ([string]::IsNullOrWhiteSpace($ReportRoot)) {
    $ReportRoot = Join-Path $rootPath (Join-Path 'report/pipeline' $runId)
}
New-Item -ItemType Directory -Force -Path $ReportRoot | Out-Null
$logsRoot = Join-Path $ReportRoot 'logs'
New-Item -ItemType Directory -Force -Path $logsRoot | Out-Null
$runInfoPath = Join-Path $ReportRoot 'run-info.json'
[pscustomobject]@{
    runId = $runId
    startedAt = (Get-Date).ToString('o')
    projectRoot = $rootPath
    reportRoot = $ReportRoot
} | ConvertTo-Json -Depth 4 | Set-Content -Path $runInfoPath -Encoding UTF8

$summary = [System.Collections.Generic.List[object]]::new()
$steps = [System.Collections.Generic.List[object]]::new()
$steps.Add((New-Step 'composer-validate' 'composer validate' 'meta'))
$steps.Add((New-Step 'composer-lint' 'composer lint' 'style'))
$steps.Add((New-Step 'composer-cs-check' 'composer cs:check' 'style'))
$steps.Add((New-Step 'composer-stan' 'composer stan' 'static'))
$steps.Add((New-Step 'composer-md' 'composer md' 'smell'))
$steps.Add((New-Step 'composer-md-tests' 'composer md:tests' 'smell'))
$steps.Add((New-Step 'composer-test' 'composer test' 'test'))
$steps.Add((New-Step 'phpunit-tools' 'php tools/php/php84.php vendor/bin/phpunit -c phpunit.xml.dist tests/Tools' 'test'))
$steps.Add((New-Step 'prefix-check' 'php tools/php/php84.php tools/linter/category_prefix_check.php' 'canon'))
$steps.Add((New-Step 'canonical-roots-check' 'php tools/php/php84.php tools/linter/category_canonical_roots_check.php' 'canon'))
$steps.Add((New-Step 'mirror-check' 'php tools/php/php84.php tools/linter/category_mirror_check.php' 'canon'))
$steps.Add((New-Step 'app-namespace-check' 'php tools/php/php84.php tools/linter/app_namespace_check.php' 'canon'))
$steps.Add((New-Step 'config-prefix-check' 'php tools/php/php84.php tools/linter/catalog_config_prefix_check.php' 'canon'))

if ($IncludeSmokes) {
    $steps.Add((New-Step 'smoke-runtime' 'composer smoke:runtime' 'smoke'))
    $steps.Add((New-Step 'smoke-fixtures' 'composer smoke:fixtures' 'smoke'))
    $steps.Add((New-Step 'smoke-container' 'composer smoke:container' 'smoke'))
    $steps.Add((New-Step 'smoke-doctrine' 'composer smoke:doctrine' 'smoke'))
    $steps.Add((New-Step 'smoke-fixture-load' 'composer smoke:fixture-load' 'smoke'))
    $steps.Add((New-Step 'smoke-graphql' 'composer smoke:graphql' 'smoke'))
    $steps.Add((New-Step 'smoke-postgres-matrix' 'composer smoke:postgres-matrix' 'smoke'))
}

if ($IncludeReports) {
    $steps.Add((New-Step 'report-owner-overlap' 'composer report:owner-overlap' 'report'))
    $steps.Add((New-Step 'report-route-inventory' 'composer report:route-inventory' 'report'))
    $steps.Add((New-Step 'report-class-alias' 'composer report:class-alias' 'report'))
    $steps.Add((New-Step 'report-runtime-proof' 'composer report:runtime-proof' 'report'))
}

if ($IncludeSecurity) {
    $steps.Add((New-Step 'security-composer-audit' 'composer audit --no-interaction --format=plain' 'security'))
    $steps.Add((New-Step 'security-importmap-audit' 'php tools/php/php84.php bin/console importmap:audit --no-interaction' 'security'))
    $steps.Add((New-Step 'security-gitleaks' 'gitleaks detect --no-banner --redact --source .' 'security'))
    $steps.Add((New-Step 'security-semgrep-ce' 'semgrep scan --config auto --error' 'security'))
    $steps.Add((New-Step 'security-symfony-tests' 'if exist tests\\Security (php tools/php/php84.php vendor/bin/phpunit -c phpunit.xml.dist tests/Security) else (echo Security tests directory not found: tests\\Security & exit /b 0)' 'security'))
}

Push-Location $rootPath
try {
    foreach ($step in $steps) {
        $logFile = Join-Path $logsRoot ($step.Name + '.log')
        $start = Get-Date
        Write-Section "$($step.Name): $($step.Command)"

        $invocation = Invoke-StepCommand -Command $step.Command
        $output = $invocation.Output
        $exitCode = $invocation.ExitCode

        $end = Get-Date
        $durationMs = [int][Math]::Round(($end - $start).TotalMilliseconds)
        $normalizedOutput = @($output | ForEach-Object {
            if ($_ -is [System.Management.Automation.ErrorRecord]) { $_.ToString() } else { [string]$_ }
        })
        Set-Content -Path $logFile -Value ($normalizedOutput -join [Environment]::NewLine) -Encoding UTF8

        $status = Classify-StepResult -ExitCode $exitCode -Output $normalizedOutput
        $summary.Add([pscustomobject]@{
            name = $step.Name
            category = $step.Category
            command = $step.Command
            status = $status
            exitCode = $exitCode
            durationMs = $durationMs
            log = $logFile.Replace($rootPath + [IO.Path]::DirectorySeparatorChar, '')
        }) | Out-Null

        if (-not $Quiet) {
            $color = switch ($status) {
                'passed' { 'Green' }
                'missing-artifact' { 'DarkYellow' }
                'environment' { 'Magenta' }
                'misconfigured' { 'DarkMagenta' }
                default { 'Yellow' }
            }
            Write-Host ("    {0} ({1} ms)" -f $status.ToUpperInvariant(), $durationMs) -ForegroundColor $color
        }
    }
} finally {
    Pop-Location
}

$failed = @($summary | Where-Object { $_.status -ne 'passed' })
$passed = @($summary | Where-Object { $_.status -eq 'passed' })
$grouped = $summary | Group-Object status | Sort-Object Name

$reportObject = [pscustomobject]@{
    generatedAt = (Get-Date).ToString('o')
    projectRoot = $rootPath
    reportRoot = $ReportRoot
    totals = [pscustomobject]@{
        steps = $summary.Count
        passed = $passed.Count
        nonPassed = $failed.Count
    }
    statuses = @($grouped | ForEach-Object { [pscustomobject]@{ status = $_.Name; count = $_.Count } })
    steps = $summary
}

$jsonPath = Join-Path $ReportRoot 'summary.json'
$txtPath = Join-Path $ReportRoot 'summary.txt'
$mdPath = Join-Path $ReportRoot 'summary.md'
$reportObject | ConvertTo-Json -Depth 6 | Set-Content -Path $jsonPath -Encoding UTF8

$txtLines = [System.Collections.Generic.List[string]]::new()
$txtLines.Add('Category local pipeline report') | Out-Null
$txtLines.Add("Generated: $((Get-Date).ToString('yyyy-MM-dd HH:mm:ss'))") | Out-Null
$txtLines.Add("Project root: $rootPath") | Out-Null
$txtLines.Add("Report root: $ReportRoot") | Out-Null
$txtLines.Add('') | Out-Null
$txtLines.Add(("Totals: steps={0}; passed={1}; nonPassed={2}" -f $summary.Count, $passed.Count, $failed.Count)) | Out-Null
foreach ($item in $grouped) {
    $txtLines.Add(("Status bucket: {0}={1}" -f $item.Name, $item.Count)) | Out-Null
}
$txtLines.Add('') | Out-Null
foreach ($item in $summary) {
    $txtLines.Add(("[{0}] {1} | exit={2} | {3} ms | {4}" -f $item.status.ToUpperInvariant(), $item.name, $item.exitCode, $item.durationMs, $item.command)) | Out-Null
    $txtLines.Add(("  log: {0}" -f $item.log)) | Out-Null
}
Set-Content -Path $txtPath -Value $txtLines -Encoding UTF8

$mdLines = [System.Collections.Generic.List[string]]::new()
$mdLines.Add('# Category local pipeline report') | Out-Null
$mdLines.Add('') | Out-Null
$mdLines.Add(("- Generated: {0}" -f (Get-Date).ToString('yyyy-MM-dd HH:mm:ss'))) | Out-Null
$mdLines.Add(("- Project root: {0}" -f $rootPath)) | Out-Null
$mdLines.Add(("- Report root: {0}" -f $ReportRoot)) | Out-Null
$mdLines.Add(("- Totals: steps={0}; passed={1}; nonPassed={2}" -f $summary.Count, $passed.Count, $failed.Count)) | Out-Null
foreach ($item in $grouped) {
    $mdLines.Add(("- Status bucket: {0}={1}" -f $item.Name, $item.Count)) | Out-Null
}
$mdLines.Add('') | Out-Null
$mdLines.Add('| Status | Step | Category | Exit | Duration ms | Log |') | Out-Null
$mdLines.Add('|---|---|---|---:|---:|---|') | Out-Null
foreach ($item in $summary) {
    $mdLines.Add(("| {0} | `{1}` | `{2}` | {3} | {4} | `{5}` |" -f $item.status.ToUpperInvariant(), $item.name, $item.category, $item.exitCode, $item.durationMs, $item.log)) | Out-Null
}
Set-Content -Path $mdPath -Value $mdLines -Encoding UTF8

if (-not $Quiet) {
    Write-Host ''
    Write-Host ("Report written to: {0}" -f $ReportRoot) -ForegroundColor Cyan
    Write-Host ("Summary: passed={0}; nonPassed={1}" -f $passed.Count, $failed.Count) -ForegroundColor Cyan
}

if ($FailOnErrors -and $failed.Count -gt 0) {
    exit 1
}

exit 0
