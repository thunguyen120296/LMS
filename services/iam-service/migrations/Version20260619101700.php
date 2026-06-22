<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619101700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create iam.email_verification_tokens table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE iam.email_verification_tokens (id UUID NOT NULL, user_id UUID NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_iam_email_verification_token_hash ON iam.email_verification_tokens (token_hash)');
        $this->addSql('CREATE INDEX idx_iam_email_verification_expires_at ON iam.email_verification_tokens (expires_at)');
        $this->addSql('ALTER TABLE iam.email_verification_tokens ADD CONSTRAINT FK_EMAIL_VERIFICATION_USER FOREIGN KEY (user_id) REFERENCES iam.users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE iam.email_verification_tokens DROP CONSTRAINT FK_EMAIL_VERIFICATION_USER');
        $this->addSql('DROP TABLE iam.email_verification_tokens');
    }
}
