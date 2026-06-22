<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619065541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial enrollment schema: enrollments, progress, reviews, certificates, wishlists';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE enrollment.certificates (id UUID NOT NULL, user_id UUID NOT NULL, course_id UUID NOT NULL, certificate_number VARCHAR(50) NOT NULL, template_url TEXT DEFAULT NULL, issued_pdf_url TEXT DEFAULT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, enrollment_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4980C89A3005EFE3 ON enrollment.certificates (certificate_number)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4980C89A8F7DB25B ON enrollment.certificates (enrollment_id)');
        $this->addSql('CREATE INDEX idx_enrollment_cert_user ON enrollment.certificates (user_id)');
        $this->addSql('CREATE INDEX idx_enrollment_cert_course ON enrollment.certificates (course_id)');
        $this->addSql('CREATE TABLE enrollment.enrollments (id UUID NOT NULL, user_id UUID NOT NULL, course_id UUID NOT NULL, status VARCHAR(20) NOT NULL, completion_percent NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, enrolled_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_enrollment_user_status ON enrollment.enrollments (user_id, status)');
        $this->addSql('CREATE INDEX idx_enrollment_course_status ON enrollment.enrollments (course_id, status)');
        $this->addSql('CREATE INDEX idx_enrollment_enrolled_at ON enrollment.enrollments (enrolled_at)');
        $this->addSql('CREATE UNIQUE INDEX uq_enrollment_user_course ON enrollment.enrollments (user_id, course_id)');
        $this->addSql('CREATE TABLE enrollment.lesson_progresses (id UUID NOT NULL, lesson_id UUID NOT NULL, is_completed BOOLEAN DEFAULT false NOT NULL, watch_duration_sec INT DEFAULT 0 NOT NULL, last_watched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, enrollment_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_D9CEBE848F7DB25B ON enrollment.lesson_progresses (enrollment_id)');
        $this->addSql('CREATE INDEX idx_enrollment_progress_completed ON enrollment.lesson_progresses (enrollment_id, is_completed)');
        $this->addSql('CREATE UNIQUE INDEX uq_enrollment_lesson_progress ON enrollment.lesson_progresses (enrollment_id, lesson_id)');
        $this->addSql('CREATE TABLE enrollment.reviews (id UUID NOT NULL, user_id UUID NOT NULL, course_id UUID NOT NULL, rating SMALLINT NOT NULL, comment TEXT DEFAULT NULL, is_published BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, enrollment_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C70511548F7DB25B ON enrollment.reviews (enrollment_id)');
        $this->addSql('CREATE INDEX idx_enrollment_reviews_course ON enrollment.reviews (course_id, is_published, rating)');
        $this->addSql('CREATE INDEX idx_enrollment_reviews_user ON enrollment.reviews (user_id)');
        $this->addSql('CREATE TABLE enrollment.wishlists (id UUID NOT NULL, user_id UUID NOT NULL, course_id UUID NOT NULL, added_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_enrollment_wishlist_user ON enrollment.wishlists (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_enrollment_wishlist ON enrollment.wishlists (user_id, course_id)');
        $this->addSql('ALTER TABLE enrollment.certificates ADD CONSTRAINT FK_4980C89A8F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollment.enrollments (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE enrollment.lesson_progresses ADD CONSTRAINT FK_D9CEBE848F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollment.enrollments (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE enrollment.reviews ADD CONSTRAINT FK_C70511548F7DB25B FOREIGN KEY (enrollment_id) REFERENCES enrollment.enrollments (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA assessment');
        $this->addSql('CREATE SCHEMA notification');
        $this->addSql('CREATE SCHEMA payment');
        $this->addSql('CREATE SCHEMA course');
        $this->addSql('CREATE SCHEMA iam');
        $this->addSql('ALTER TABLE enrollment.certificates DROP CONSTRAINT FK_4980C89A8F7DB25B');
        $this->addSql('ALTER TABLE enrollment.lesson_progresses DROP CONSTRAINT FK_D9CEBE848F7DB25B');
        $this->addSql('ALTER TABLE enrollment.reviews DROP CONSTRAINT FK_C70511548F7DB25B');
        $this->addSql('DROP TABLE enrollment.certificates');
        $this->addSql('DROP TABLE enrollment.enrollments');
        $this->addSql('DROP TABLE enrollment.lesson_progresses');
        $this->addSql('DROP TABLE enrollment.reviews');
        $this->addSql('DROP TABLE enrollment.wishlists');
    }
}
