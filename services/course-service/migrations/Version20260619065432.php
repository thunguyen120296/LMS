<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619065432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial course schema: categories, courses, sections, lessons, tags';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course.categories (id UUID NOT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description TEXT DEFAULT NULL, icon_url TEXT DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, course_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, parent_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DABD85D8989D9B62 ON course.categories (slug)');
        $this->addSql('CREATE INDEX IDX_DABD85D8727ACA70 ON course.categories (parent_id)');
        $this->addSql('CREATE INDEX idx_course_category_slug ON course.categories (slug)');
        $this->addSql('CREATE INDEX idx_course_category_parent_active ON course.categories (parent_id, is_active, sort_order)');
        $this->addSql('CREATE INDEX idx_course_category_active ON course.categories (is_active)');
        $this->addSql('CREATE TABLE course.course_learning_objectives (id UUID NOT NULL, description VARCHAR(500) NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, course_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B75B57DB591CC992 ON course.course_learning_objectives (course_id)');
        $this->addSql('CREATE INDEX idx_course_objectives_order ON course.course_learning_objectives (course_id, sort_order)');
        $this->addSql('CREATE TABLE course.course_requirements (id UUID NOT NULL, description VARCHAR(500) NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, course_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4F494CB8591CC992 ON course.course_requirements (course_id)');
        $this->addSql('CREATE INDEX idx_course_requirements_order ON course.course_requirements (course_id, sort_order)');
        $this->addSql('CREATE TABLE course.course_tags (course_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY (course_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_6C9FB46F591CC992 ON course.course_tags (course_id)');
        $this->addSql('CREATE INDEX IDX_6C9FB46FBAD26311 ON course.course_tags (tag_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_course_tag ON course.course_tags (course_id, tag_id)');
        $this->addSql('CREATE TABLE course.courses (id UUID NOT NULL, instructor_id UUID NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(300) NOT NULL, description TEXT DEFAULT NULL, short_description VARCHAR(500) DEFAULT NULL, thumbnail_url TEXT DEFAULT NULL, preview_video_url TEXT DEFAULT NULL, language VARCHAR(5) DEFAULT \'vi\' NOT NULL, level VARCHAR(20) DEFAULT \'all_levels\' NOT NULL, price_type VARCHAR(20) DEFAULT \'paid\' NOT NULL, price NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, discount_price NUMERIC(12, 2) DEFAULT NULL, currency VARCHAR(3) DEFAULT \'VND\' NOT NULL, status VARCHAR(20) DEFAULT \'draft\' NOT NULL, rejection_reason TEXT DEFAULT NULL, duration_minutes INT DEFAULT 0 NOT NULL, total_lessons INT DEFAULT 0 NOT NULL, avg_rating DOUBLE PRECISION DEFAULT 0 NOT NULL, total_reviews INT DEFAULT 0 NOT NULL, total_students INT DEFAULT 0 NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, category_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C19CCEAE989D9B62 ON course.courses (slug)');
        $this->addSql('CREATE INDEX IDX_C19CCEAE12469DE2 ON course.courses (category_id)');
        $this->addSql('CREATE INDEX idx_course_courses_slug ON course.courses (slug)');
        $this->addSql('CREATE INDEX idx_course_courses_instructor ON course.courses (instructor_id)');
        $this->addSql('CREATE INDEX idx_course_courses_category_status ON course.courses (category_id, status)');
        $this->addSql('CREATE INDEX idx_course_courses_status ON course.courses (status, deleted_at)');
        $this->addSql('CREATE INDEX idx_course_courses_rating ON course.courses (avg_rating)');
        $this->addSql('CREATE INDEX idx_course_courses_students ON course.courses (total_students)');
        $this->addSql('CREATE TABLE course.lesson_resources (id UUID NOT NULL, title VARCHAR(255) NOT NULL, file_url TEXT NOT NULL, file_type VARCHAR(50) DEFAULT NULL, file_size_bytes BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, lesson_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_course_lesson_resources_lesson ON course.lesson_resources (lesson_id)');
        $this->addSql('CREATE TABLE course.lessons (id UUID NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(20) DEFAULT \'video\' NOT NULL, content TEXT DEFAULT NULL, video_url TEXT DEFAULT NULL, video_duration_sec INT DEFAULT 0 NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, is_free_preview BOOLEAN DEFAULT false NOT NULL, is_published BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, section_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_577B8C3BD823E37A ON course.lessons (section_id)');
        $this->addSql('CREATE INDEX idx_course_lessons_section_order ON course.lessons (section_id, sort_order)');
        $this->addSql('CREATE INDEX idx_course_lessons_published ON course.lessons (is_published)');
        $this->addSql('CREATE INDEX idx_course_lessons_type ON course.lessons (type)');
        $this->addSql('CREATE TABLE course.sections (id UUID NOT NULL, title VARCHAR(255) NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, course_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_65FAF958591CC992 ON course.sections (course_id)');
        $this->addSql('CREATE INDEX idx_course_sections_course_order ON course.sections (course_id, sort_order)');
        $this->addSql('CREATE TABLE course.tags (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_63AC805F5E237E06 ON course.tags (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_63AC805F989D9B62 ON course.tags (slug)');
        $this->addSql('ALTER TABLE course.categories ADD CONSTRAINT FK_DABD85D8727ACA70 FOREIGN KEY (parent_id) REFERENCES course.categories (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.course_learning_objectives ADD CONSTRAINT FK_B75B57DB591CC992 FOREIGN KEY (course_id) REFERENCES course.courses (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.course_requirements ADD CONSTRAINT FK_4F494CB8591CC992 FOREIGN KEY (course_id) REFERENCES course.courses (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.course_tags ADD CONSTRAINT FK_6C9FB46F591CC992 FOREIGN KEY (course_id) REFERENCES course.courses (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.course_tags ADD CONSTRAINT FK_6C9FB46FBAD26311 FOREIGN KEY (tag_id) REFERENCES course.tags (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.courses ADD CONSTRAINT FK_C19CCEAE12469DE2 FOREIGN KEY (category_id) REFERENCES course.categories (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.lesson_resources ADD CONSTRAINT FK_ED137AF4CDF80196 FOREIGN KEY (lesson_id) REFERENCES course.lessons (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.lessons ADD CONSTRAINT FK_577B8C3BD823E37A FOREIGN KEY (section_id) REFERENCES course.sections (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE course.sections ADD CONSTRAINT FK_65FAF958591CC992 FOREIGN KEY (course_id) REFERENCES course.courses (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA assessment');
        $this->addSql('CREATE SCHEMA notification');
        $this->addSql('CREATE SCHEMA payment');
        $this->addSql('CREATE SCHEMA enrollment');
        $this->addSql('CREATE SCHEMA iam');
        $this->addSql('ALTER TABLE course.categories DROP CONSTRAINT FK_DABD85D8727ACA70');
        $this->addSql('ALTER TABLE course.course_learning_objectives DROP CONSTRAINT FK_B75B57DB591CC992');
        $this->addSql('ALTER TABLE course.course_requirements DROP CONSTRAINT FK_4F494CB8591CC992');
        $this->addSql('ALTER TABLE course.course_tags DROP CONSTRAINT FK_6C9FB46F591CC992');
        $this->addSql('ALTER TABLE course.course_tags DROP CONSTRAINT FK_6C9FB46FBAD26311');
        $this->addSql('ALTER TABLE course.courses DROP CONSTRAINT FK_C19CCEAE12469DE2');
        $this->addSql('ALTER TABLE course.lesson_resources DROP CONSTRAINT FK_ED137AF4CDF80196');
        $this->addSql('ALTER TABLE course.lessons DROP CONSTRAINT FK_577B8C3BD823E37A');
        $this->addSql('ALTER TABLE course.sections DROP CONSTRAINT FK_65FAF958591CC992');
        $this->addSql('DROP TABLE course.categories');
        $this->addSql('DROP TABLE course.course_learning_objectives');
        $this->addSql('DROP TABLE course.course_requirements');
        $this->addSql('DROP TABLE course.course_tags');
        $this->addSql('DROP TABLE course.courses');
        $this->addSql('DROP TABLE course.lesson_resources');
        $this->addSql('DROP TABLE course.lessons');
        $this->addSql('DROP TABLE course.sections');
        $this->addSql('DROP TABLE course.tags');
    }
}
