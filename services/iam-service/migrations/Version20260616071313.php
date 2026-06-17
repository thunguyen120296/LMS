<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616071313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create iam.users and iam.companies tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE iam.companies (id UUID NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_iam_companies_status ON iam.companies (status)');
        $this->addSql('CREATE TABLE iam.users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, avatar_url TEXT DEFAULT NULL, locale VARCHAR(10) DEFAULT \'en\' NOT NULL, sso_provider VARCHAR(50) DEFAULT NULL, sso_subject VARCHAR(255) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, email_verified BOOLEAN DEFAULT false NOT NULL, last_login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEE98E3E7927C74 ON iam.users (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEE98E3F85E0677 ON iam.users (username)');
        $this->addSql('CREATE INDEX idx_iam_users_email ON iam.users (email)');
        $this->addSql('CREATE INDEX idx_iam_users_sso ON iam.users (sso_provider, sso_subject)');
        $this->addSql('CREATE INDEX idx_iam_users_deleted_at ON iam.users (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE iam.users');
        $this->addSql('DROP TABLE iam.companies');
    }
}
