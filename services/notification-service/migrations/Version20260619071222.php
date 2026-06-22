<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619071222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial notification schema: templates, notifications, user preferences';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification.notifications (id UUID NOT NULL, user_id UUID NOT NULL, template_code VARCHAR(100) NOT NULL, channel VARCHAR(20) NOT NULL, title VARCHAR(255) NOT NULL, body TEXT NOT NULL, payload JSON DEFAULT NULL, status VARCHAR(20) NOT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_notification_inbox ON notification.notifications (user_id, status, created_at)');
        $this->addSql('CREATE TABLE notification.templates (id UUID NOT NULL, code VARCHAR(100) NOT NULL, channel VARCHAR(20) NOT NULL, subject VARCHAR(255) DEFAULT NULL, body_template TEXT NOT NULL, locale VARCHAR(10) DEFAULT \'vi\' NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_notification_templates_active ON notification.templates (is_active)');
        $this->addSql('CREATE UNIQUE INDEX uq_notification_template_code_locale ON notification.templates (code, locale)');
        $this->addSql('CREATE TABLE notification.user_preferences (id UUID NOT NULL, user_id UUID NOT NULL, email_enabled BOOLEAN DEFAULT true NOT NULL, push_enabled BOOLEAN DEFAULT true NOT NULL, marketing_enabled BOOLEAN DEFAULT false NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_293C2AADA76ED395 ON notification.user_preferences (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA assessment');
        $this->addSql('CREATE SCHEMA payment');
        $this->addSql('CREATE SCHEMA enrollment');
        $this->addSql('CREATE SCHEMA course');
        $this->addSql('CREATE SCHEMA iam');
        $this->addSql('DROP TABLE notification.notifications');
        $this->addSql('DROP TABLE notification.templates');
        $this->addSql('DROP TABLE notification.user_preferences');
    }
}
