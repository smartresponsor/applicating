<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create application lifecycle tables for Applicating component';
    }

    public function up(Schema $schema): void
    {
        $application = $schema->createTable('application_listing');
        $application->addColumn('id', 'integer', ['autoincrement' => true]);
        $application->addColumn('name', 'string', ['length' => 160]);
        $application->addColumn('slug', 'string', ['length' => 120]);
        $application->addColumn('package_name', 'string', ['length' => 160]);
        $application->addColumn('developer_name', 'string', ['length' => 160]);
        $application->addColumn('listing_summary', 'string', ['length' => 512]);
        $application->addColumn('publication_state', 'string', ['length' => 20]);
        $application->addColumn('access_level', 'string', ['length' => 24]);
        $application->addColumn('billing_code', 'string', ['length' => 120, 'notnull' => false]);
        $application->addColumn('sandbox_profile', 'string', ['length' => 80]);
        $application->addColumn('enabled_by_default', 'boolean');
        $application->addColumn('created_at', 'datetime_immutable');
        $application->addColumn('updated_at', 'datetime_immutable');
        $application->setPrimaryKey(['id']);
        $application->addUniqueIndex(['slug']);
        $application->addUniqueIndex(['package_name']);

        $release = $schema->createTable('application_release');
        $release->addColumn('id', 'integer', ['autoincrement' => true]);
        $release->addColumn('application_id', 'integer');
        $release->addColumn('version', 'string', ['length' => 32]);
        $release->addColumn('channel', 'string', ['length' => 32]);
        $release->addColumn('checksum', 'string', ['length' => 128]);
        $release->addColumn('download_url', 'string', ['length' => 255]);
        $release->addColumn('release_notes', 'text');
        $release->addColumn('publication_state', 'string', ['length' => 20]);
        $release->addColumn('created_at', 'datetime_immutable');
        $release->addColumn('published_at', 'datetime_immutable', ['notnull' => false]);
        $release->setPrimaryKey(['id']);
        $release->addForeignKeyConstraint('application_listing', ['application_id'], ['id'], ['onDelete' => 'CASCADE']);

        $manifest = $schema->createTable('application_manifest');
        $manifest->addColumn('id', 'integer', ['autoincrement' => true]);
        $manifest->addColumn('application_id', 'integer');
        $manifest->addColumn('manifest_version', 'string', ['length' => 24]);
        $manifest->addColumn('identifier', 'string', ['length' => 160]);
        $manifest->addColumn('capabilities', 'json');
        $manifest->addColumn('permissions', 'json');
        $manifest->addColumn('runtime_hooks', 'json');
        $manifest->addColumn('sandbox_profile', 'string', ['length' => 80]);
        $manifest->addColumn('governance_state', 'string', ['length' => 40]);
        $manifest->addColumn('raw_manifest', 'json');
        $manifest->addColumn('created_at', 'datetime_immutable');
        $manifest->setPrimaryKey(['id']);
        $manifest->addForeignKeyConstraint('application_listing', ['application_id'], ['id'], ['onDelete' => 'CASCADE']);

        $tenantApplication = $schema->createTable('tenant_application');
        $tenantApplication->addColumn('id', 'integer', ['autoincrement' => true]);
        $tenantApplication->addColumn('application_id', 'integer');
        $tenantApplication->addColumn('tenant_key', 'string', ['length' => 120]);
        $tenantApplication->addColumn('installed_version', 'string', ['length' => 32]);
        $tenantApplication->addColumn('installation_state', 'string', ['length' => 20]);
        $tenantApplication->addColumn('enabled', 'boolean');
        $tenantApplication->addColumn('billing_active', 'boolean');
        $tenantApplication->addColumn('access_policy', 'json');
        $tenantApplication->addColumn('diagnostics', 'json');
        $tenantApplication->addColumn('assigned_at', 'datetime_immutable');
        $tenantApplication->addColumn('installed_at', 'datetime_immutable', ['notnull' => false]);
        $tenantApplication->addColumn('last_checked_at', 'datetime_immutable', ['notnull' => false]);
        $tenantApplication->setPrimaryKey(['id']);
        $tenantApplication->addForeignKeyConstraint('application_listing', ['application_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('tenant_application');
        $schema->dropTable('application_manifest');
        $schema->dropTable('application_release');
        $schema->dropTable('application_listing');
    }
}
