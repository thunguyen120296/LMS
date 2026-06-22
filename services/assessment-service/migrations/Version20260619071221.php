<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619071221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial assessment schema: quizzes, questions, attempts, answers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assessment.question_options (id UUID NOT NULL, content TEXT NOT NULL, is_correct BOOLEAN DEFAULT false NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, question_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_221235131E27F6BF ON assessment.question_options (question_id)');
        $this->addSql('CREATE INDEX idx_assessment_options_question ON assessment.question_options (question_id, sort_order)');
        $this->addSql('CREATE TABLE assessment.questions (id UUID NOT NULL, type VARCHAR(20) NOT NULL, content TEXT NOT NULL, explanation TEXT DEFAULT NULL, points NUMERIC(5, 2) DEFAULT \'1.00\' NOT NULL, sort_order SMALLINT DEFAULT 0 NOT NULL, quiz_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4126499F853CD175 ON assessment.questions (quiz_id)');
        $this->addSql('CREATE INDEX idx_assessment_questions_quiz_order ON assessment.questions (quiz_id, sort_order)');
        $this->addSql('CREATE TABLE assessment.quiz_answers (id UUID NOT NULL, selected_option_ids JSON DEFAULT NULL, text_answer TEXT DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, points_earned NUMERIC(5, 2) DEFAULT NULL, attempt_id UUID NOT NULL, question_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9D831465B191BE6B ON assessment.quiz_answers (attempt_id)');
        $this->addSql('CREATE INDEX IDX_9D8314651E27F6BF ON assessment.quiz_answers (question_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_assessment_attempt_question ON assessment.quiz_answers (attempt_id, question_id)');
        $this->addSql('CREATE TABLE assessment.quiz_attempts (id UUID NOT NULL, user_id UUID NOT NULL, enrollment_id UUID DEFAULT NULL, status VARCHAR(20) NOT NULL, score NUMERIC(5, 2) DEFAULT NULL, is_passed BOOLEAN DEFAULT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, time_spent_sec INT DEFAULT NULL, quiz_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6BB18454853CD175 ON assessment.quiz_attempts (quiz_id)');
        $this->addSql('CREATE INDEX idx_assessment_attempts_quiz_user ON assessment.quiz_attempts (quiz_id, user_id, status)');
        $this->addSql('CREATE INDEX idx_assessment_attempts_user_date ON assessment.quiz_attempts (user_id, started_at)');
        $this->addSql('CREATE TABLE assessment.quizzes (id UUID NOT NULL, course_id UUID NOT NULL, lesson_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, passing_score NUMERIC(5, 2) DEFAULT \'70.00\' NOT NULL, time_limit_minutes INT DEFAULT NULL, max_attempts INT DEFAULT NULL, shuffle_questions BOOLEAN DEFAULT true NOT NULL, is_published BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_assessment_quizzes_course ON assessment.quizzes (course_id, is_published)');
        $this->addSql('CREATE INDEX idx_assessment_quizzes_lesson ON assessment.quizzes (lesson_id)');
        $this->addSql('ALTER TABLE assessment.question_options ADD CONSTRAINT FK_221235131E27F6BF FOREIGN KEY (question_id) REFERENCES assessment.questions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE assessment.questions ADD CONSTRAINT FK_4126499F853CD175 FOREIGN KEY (quiz_id) REFERENCES assessment.quizzes (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE assessment.quiz_answers ADD CONSTRAINT FK_9D831465B191BE6B FOREIGN KEY (attempt_id) REFERENCES assessment.quiz_attempts (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE assessment.quiz_answers ADD CONSTRAINT FK_9D8314651E27F6BF FOREIGN KEY (question_id) REFERENCES assessment.questions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE assessment.quiz_attempts ADD CONSTRAINT FK_6BB18454853CD175 FOREIGN KEY (quiz_id) REFERENCES assessment.quizzes (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA notification');
        $this->addSql('CREATE SCHEMA payment');
        $this->addSql('CREATE SCHEMA enrollment');
        $this->addSql('CREATE SCHEMA course');
        $this->addSql('CREATE SCHEMA iam');
        $this->addSql('ALTER TABLE assessment.question_options DROP CONSTRAINT FK_221235131E27F6BF');
        $this->addSql('ALTER TABLE assessment.questions DROP CONSTRAINT FK_4126499F853CD175');
        $this->addSql('ALTER TABLE assessment.quiz_answers DROP CONSTRAINT FK_9D831465B191BE6B');
        $this->addSql('ALTER TABLE assessment.quiz_answers DROP CONSTRAINT FK_9D8314651E27F6BF');
        $this->addSql('ALTER TABLE assessment.quiz_attempts DROP CONSTRAINT FK_6BB18454853CD175');
        $this->addSql('DROP TABLE assessment.question_options');
        $this->addSql('DROP TABLE assessment.questions');
        $this->addSql('DROP TABLE assessment.quiz_answers');
        $this->addSql('DROP TABLE assessment.quiz_attempts');
        $this->addSql('DROP TABLE assessment.quizzes');
    }
}
