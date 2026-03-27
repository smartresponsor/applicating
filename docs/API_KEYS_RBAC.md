# API Keys & RBAC
Headers: X-Tenant-Id, X-API-Key. Roles: VIEWER, MANAGER, ADMIN, SERVICE.
Создание ключа:
$repo->create('tenantA', 'integration-1', 'PLAINTEXT_KEY', ['catalog:read'], ['SERVICE'], 600, null);
