# Smartresponsor Product Suite — Roadmap
*(updated 2025-10-08)*

---

## v18.x — Stabilization & CI/CD Hardening ✅
- [x] Unified installer & Docker bootstrap (v18)
- [x] Demo bootstrap (`make demo-up`, `bootstrap.sh`)
- [ ] CI/CD multi-env matrix (staging → prod)
- [ ] Smoke-test job after deploy
- [ ] Slack/Telegram release notifications
- [ ] SonarQube / PHPStan Quality Gate

---

## v19.x — Observability & Security
- [ ] OpenTelemetry traces/logs (Jaeger + Loki)
- [ ] ClickHouse/S3 audit sink (long-term retention)
- [ ] Role-based API access (JWT scopes, API keys)
- [ ] Vault/KMS secret rotation
- [ ] Enhanced CSP template + nonce/hash management

---

## v20.x — Multi-Tenant Layer
- [ ] Tenant isolation (schema/row filter)
- [ ] Plan-based rate limits
- [ ] Tenant metadata service
- [ ] Billing webhooks (Stripe or stub)
- [ ] Tenant-aware caching

---

## v21.x — Horizontal Scale & Async
- [ ] Async queue for heavy jobs (RabbitMQ/Redis Streams)
- [ ] Outbox relay with dead-letter retry
- [ ] ProductRead denormalized cache
- [ ] Transaction-safe batch writer
- [ ] Auto-partitioned tables by month

---

## v22.x — Web & API Enhancements
- [ ] GraphQL & JSON:API gateways
- [ ] Admin UI (React/Vue) with metrics dashboard
- [ ] OAuth2 / SSO integration (Google/GitHub)
- [ ] API versioning & deprecation warnings
- [ ] Public API documentation portal (Swagger / ReDoc)

---

## v23.x — SaaS & Commercialization
- [ ] License manager (per-tenant / per-install)
- [ ] Helm chart release to ArtifactHub
- [ ] Docker Hub auto-build + image signing
- [ ] Docusaurus documentation site
- [ ] SaaS tier rollout: Free / Pro / Enterprise

---

### Long-term (v24+)
- [ ] AI-assisted monitoring & self-healing
- [ ] Predictive scaling
- [ ] Cost optimization analytics
- [ ] SDKs for PHP/Node/Python clients

---

**Maintainer:** Smartresponsor Core  
**Contact:** dev@smartresponsor.com  
