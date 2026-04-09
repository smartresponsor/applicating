# GitHub Release — Applicating / Application

To publish:
```bash
gh release create v18.0.0 --title "Applicating / Application" --notes-file docs/CHANGELOG.md --draft
```

Build the curated runtime archive with:
```bash
bash tools/release/make-release.sh
```

Suggested artifacts to attach:
- `applicating-runtime.zip`
- `applicating-application-roadmap.zip`
- `applicating-application-demo-bootstrap.zip`
- `applicating-application-inspection-reports.zip`

Before publishing or promoting a release:
- run `composer release:verify` locally or let `release.yml` do it on tag push
- verify `/health` and `/ready`
- confirm admin login still works
- review `docs/ROLLBACK.md`
- inspect the `applicating-inspection-reports` artifact
