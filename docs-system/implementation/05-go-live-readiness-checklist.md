# 05 - Go-Live Readiness Checklist

## Objective
Provide an actionable release gate for soft launch and public launch.

## API Readiness Endpoint
- Endpoint: `GET /api/v1/admin/system/readiness`
- Auth: Sanctum + `admin.role`
- Output:
  - `ready` (boolean)
  - `score` (0-100)
  - `summary.pass|warn|fail`
  - detailed `checks[]`

## Current Checks
- `DB_CONNECTION`: DB query health.
- `QUEUE_CONNECTION`: warns if queue driver is `sync`.
- `PROVIDER_CREDENTIALS`: provider credential coverage.
- `PAYMENT_GATEWAY_WEBHOOK_SECRETS`: gateway webhook secret coverage.
- `HOUSEKEEPING_RECENT_PERSISTENCE`: purge run detected in last 2 hours.

## Go-Live Gate
All items below should be closed before public launch:

1. Integration
- Provider sandbox/live payload mapping verified.
- Payment callback verification tested with real gateway signature.

2. Infrastructure
- Queue workers supervised (auto restart enabled).
- Scheduler running every minute.
- Redis persistence and PostgreSQL backup/restore tested.

3. Security
- Production secrets rotated and stored in secret manager.
- Webhook endpoint protected by signature + timestamp (+ optional IP allowlist).
- Login/admin routes covered by throttling and audit logging.

4. Operations
- Dashboard alerts configured with threshold and escalation owner.
- Housekeeping endpoints monitored (`summary`, `history`, `trend`).
- Incident runbook available for payment/provider outage.

5. UAT
- End-to-end purchase success tested.
- Retry/failover path tested.
- Manual reprocess flow tested by operations/admin.

## Suggested Release Stages
1. Internal staging soak (2-3 days).
2. Limited beta traffic.
3. Public rollout in phases with alert monitoring.
