#!/usr/bin/env sh
set -u

PROJECT_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
REPORT_ROOT="${1:-$PROJECT_ROOT/report/pipeline/$(date +%Y%m%d-%H%M%S)}"
LOG_ROOT="$REPORT_ROOT/logs"
mkdir -p "$LOG_ROOT"

SUMMARY_JSON="$REPORT_ROOT/summary.json"
SUMMARY_TXT="$REPORT_ROOT/summary.txt"
SUMMARY_MD="$REPORT_ROOT/summary.md"
STATUS_TMP="$REPORT_ROOT/.status.tsv"
: > "$STATUS_TMP"

run_step() {
  name="$1"
  category="$2"
  command="$3"
  log_file="$LOG_ROOT/$name.log"
  start_ts=$(date +%s)
  sh -lc "cd '$PROJECT_ROOT' && $command" >"$log_file" 2>&1
  exit_code=$?
  end_ts=$(date +%s)
  duration=$((end_ts - start_ts))
  status="passed"
  if [ "$exit_code" -ne 0 ]; then
    status="failed"
  fi
  printf '%s\t%s\t%s\t%s\t%s\t%s\n' "$name" "$category" "$status" "$exit_code" "$duration" "logs/$name.log" >> "$STATUS_TMP"
  printf '%s\n' "$status $name ($category) exit=$exit_code"
}

run_step composer-validate meta "composer validate"
run_step composer-lint style "composer lint"
run_step composer-cs-check style "composer cs:check"
run_step composer-stan static "composer stan"
run_step composer-md smell "composer md"
run_step composer-md-tests smell "composer md:tests"
run_step composer-test test "composer test"
run_step phpunit-tools test "php tools/php/php84.php vendor/bin/phpunit -c phpunit.xml.dist tests/Tools"
run_step prefix-check canon "php tools/php/php84.php tools/linter/category_prefix_check.php"
run_step canonical-roots-check canon "php tools/php/php84.php tools/linter/category_canonical_roots_check.php"
run_step mirror-check canon "php tools/php/php84.php tools/linter/category_mirror_check.php"

python3 - "$REPORT_ROOT" "$STATUS_TMP" <<'PY'
import json, sys, pathlib, datetime
report_root = pathlib.Path(sys.argv[1])
status_file = pathlib.Path(sys.argv[2])
rows = []
for line in status_file.read_text(encoding='utf-8').splitlines():
    name, category, status, exit_code, duration, log = line.split('\t')
    rows.append({
        'name': name,
        'category': category,
        'status': status,
        'exitCode': int(exit_code),
        'durationSec': int(duration),
        'log': log,
    })
report = {
    'generatedAt': datetime.datetime.now().isoformat(),
    'reportRoot': str(report_root),
    'totals': {
        'steps': len(rows),
        'passed': sum(1 for r in rows if r['exitCode'] == 0),
        'failed': sum(1 for r in rows if r['exitCode'] != 0),
    },
    'steps': rows,
}
(report_root / 'summary.json').write_text(json.dumps(report, indent=2), encoding='utf-8')
lines = [
    'Category local pipeline report',
    f"Generated: {datetime.datetime.now():%Y-%m-%d %H:%M:%S}",
    f"Report root: {report_root}",
    '',
    f"Totals: steps={report['totals']['steps']}; passed={report['totals']['passed']}; failed={report['totals']['failed']}",
    '',
]
for row in rows:
    lines.append(f"[{row['status'].upper()}] {row['name']} | exit={row['exitCode']} | {row['durationSec']} sec | {row['log']}")
(report_root / 'summary.txt').write_text('\n'.join(lines) + '\n', encoding='utf-8')
md = [
    '# Category local pipeline report',
    '',
    f"- Generated: `{datetime.datetime.now():%Y-%m-%d %H:%M:%S}`",
    f"- Report root: `{report_root}`",
    f"- Totals: `steps={report['totals']['steps']}; passed={report['totals']['passed']}; failed={report['totals']['failed']}`",
    '',
    '| Status | Step | Category | Exit | Duration sec | Log |',
    '|---|---|---|---:|---:|---|',
]
for row in rows:
    icon = 'OK' if row['exitCode'] == 0 else 'FAIL'
    md.append(f"| {icon} | `{row['name']}` | `{row['category']}` | {row['exitCode']} | {row['durationSec']} | `{row['log']}` |")
(report_root / 'summary.md').write_text('\n'.join(md) + '\n', encoding='utf-8')
PY

rm -f "$STATUS_TMP"
printf 'Report written to: %s\n' "$REPORT_ROOT"
exit 0
