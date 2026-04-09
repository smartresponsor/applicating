# Applicating admin auth and roles

## Runtime profile
The active runtime now uses one Symfony firewall backed by a persistent Doctrine user provider.

Admin users live in the `application_user` table. This moves the active runtime away from in-memory demo users and gives the component a stable base for later OIDC/SSO integration.

Demo accounts are created by `applicating:fixtures:load-demo`:
- `admin` → `ROLE_APPLICATION_ADMIN`, `ROLE_APPLICATION_MANAGER`, `ROLE_APPLICATION_VIEWER`
- `manager` → `ROLE_APPLICATION_MANAGER`, `ROLE_APPLICATION_VIEWER`
- `viewer` → `ROLE_APPLICATION_VIEWER`

The `test` environment still overrides the provider with isolated in-memory users so functional login tests stay deterministic and do not depend on fixture state.

## Access model
- `/login` → public
- `/health` and `/ready` → public
- `/admin/**` → `ROLE_APPLICATION_VIEWER`
- mutating admin actions → voter checks and/or `ROLE_APPLICATION_MANAGER`
- `/api/admin/**` and `/api/admin/v1/**` → `ROLE_APPLICATION_MANAGER`

`ROLE_APPLICATION_ADMIN` is the elevated management role.

## Identity model
Persistent admin identities now carry:
- `userIdentifier` for the login name
- `displayName`
- `email`
- `roles`
- `authSource` (`local` today, ready for external IdP later)
- `externalSubject` for future OIDC/SAML subject mapping
- `active` status for account disablement

## Local bootstrap
Create demo users:
```bash
php bin/console applicating:fixtures:load-demo
```

Create or update a local user explicitly:
```bash
php bin/console applicating:user:create ops-admin change-me-now \
  --display-name="Operations Admin" \
  --email="ops-admin@example.test" \
  --role=ROLE_APPLICATION_ADMIN \
  --role=ROLE_APPLICATION_MANAGER \
  --role=ROLE_APPLICATION_VIEWER
```

## Current limitations
- there is still no external OIDC/SAML connector in the active runtime
- role assignment is still application-local rather than backed by a separate enterprise RBAC service
- last-login auditing is modeled in the entity, but no interactive login success listener writes it yet
