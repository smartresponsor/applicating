<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create persistent application user table for local and enterprise-ready admin authentication';
    }

    public function up(Schema $schema): void
    {
        $user = $schema->createTable('application_user');
        $user->addColumn('id', 'integer', ['autoincrement' => true]);
        $user->addColumn('user_identifier', 'string', ['length' => 120]);
        $user->addColumn('display_name', 'string', ['length' => 160]);
        $user->addColumn('email', 'string', ['length' => 180, 'notnull' => false]);
        $user->addColumn('roles', 'json');
        $user->addColumn('password', 'string', ['length' => 255, 'notnull' => false]);
        $user->addColumn('auth_source', 'string', ['length' => 32]);
        $user->addColumn('external_subject', 'string', ['length' => 190, 'notnull' => false]);
        $user->addColumn('active', 'boolean');
        $user->addColumn('created_at', 'datetime_immutable');
        $user->addColumn('updated_at', 'datetime_immutable');
        $user->addColumn('last_login_at', 'datetime_immutable', ['notnull' => false]);
        $user->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $user->addUniqueIndex(['user_identifier'], 'uniq_application_user_identifier');
        $user->addUniqueIndex(['email'], 'uniq_application_user_email');
        $user->addUniqueIndex(['external_subject'], 'uniq_application_user_external_subject');
        $user->addIndex(['active'], 'idx_application_user_active');
        $user->addIndex(['auth_source'], 'idx_application_user_auth_source');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('application_user');
    }
}
