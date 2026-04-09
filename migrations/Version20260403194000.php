<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403194000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Harden application lifecycle schema with unique constraints and supporting indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_application_release_application_id ON application_release (application_id)');
        $this->addSql('CREATE INDEX idx_application_release_publication_state ON application_release (publication_state)');
        $this->addSql('CREATE UNIQUE INDEX uniq_application_release_version_per_application ON application_release (application_id, version)');

        $this->addSql('CREATE INDEX idx_application_manifest_application_id ON application_manifest (application_id)');
        $this->addSql('CREATE INDEX idx_application_manifest_governance_state ON application_manifest (governance_state)');
        $this->addSql('CREATE UNIQUE INDEX uniq_application_manifest_identifier_per_application ON application_manifest (application_id, identifier)');

        $this->addSql('CREATE INDEX idx_tenant_application_application_id ON tenant_application (application_id)');
        $this->addSql('CREATE INDEX idx_tenant_application_tenant_key ON tenant_application (tenant_key)');
        $this->addSql('CREATE INDEX idx_tenant_application_installation_state ON tenant_application (installation_state)');
        $this->addSql('CREATE UNIQUE INDEX uniq_tenant_application_assignment ON tenant_application (application_id, tenant_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_application_release_application_id');
        $this->addSql('DROP INDEX idx_application_release_publication_state');
        $this->addSql('DROP INDEX uniq_application_release_version_per_application');

        $this->addSql('DROP INDEX idx_application_manifest_application_id');
        $this->addSql('DROP INDEX idx_application_manifest_governance_state');
        $this->addSql('DROP INDEX uniq_application_manifest_identifier_per_application');

        $this->addSql('DROP INDEX idx_tenant_application_application_id');
        $this->addSql('DROP INDEX idx_tenant_application_tenant_key');
        $this->addSql('DROP INDEX idx_tenant_application_installation_state');
        $this->addSql('DROP INDEX uniq_tenant_application_assignment');
    }
}
