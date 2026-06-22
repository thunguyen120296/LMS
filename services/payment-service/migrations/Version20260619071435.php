<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619071435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial payment schema: orders, transactions, coupons, payouts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment.coupons (id UUID NOT NULL, code VARCHAR(50) NOT NULL, discount_type VARCHAR(20) NOT NULL, discount_value NUMERIC(10, 2) NOT NULL, min_purchase NUMERIC(15, 2) DEFAULT NULL, max_uses INT DEFAULT NULL, uses_count INT DEFAULT 0 NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CD78EFC77153098 ON payment.coupons (code)');
        $this->addSql('CREATE INDEX idx_payment_coupons_active ON payment.coupons (is_active, starts_at, expires_at)');
        $this->addSql('CREATE TABLE payment.order_items (id UUID NOT NULL, course_id UUID NOT NULL, course_title VARCHAR(255) NOT NULL, unit_price NUMERIC(15, 2) NOT NULL, final_price NUMERIC(15, 2) NOT NULL, order_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2BE380F18D9F6D38 ON payment.order_items (order_id)');
        $this->addSql('CREATE INDEX idx_payment_order_items_course ON payment.order_items (course_id)');
        $this->addSql('CREATE TABLE payment.orders (id UUID NOT NULL, user_id UUID NOT NULL, order_number VARCHAR(30) NOT NULL, status VARCHAR(20) NOT NULL, currency VARCHAR(3) DEFAULT \'VND\' NOT NULL, subtotal NUMERIC(15, 2) DEFAULT \'0.00\' NOT NULL, discount_amount NUMERIC(15, 2) DEFAULT \'0.00\' NOT NULL, tax_amount NUMERIC(15, 2) DEFAULT \'0.00\' NOT NULL, total_amount NUMERIC(15, 2) DEFAULT \'0.00\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, coupon_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A08BDE7551F0F81 ON payment.orders (order_number)');
        $this->addSql('CREATE INDEX IDX_8A08BDE766C5951B ON payment.orders (coupon_id)');
        $this->addSql('CREATE INDEX idx_payment_orders_user_status ON payment.orders (user_id, status)');
        $this->addSql('CREATE INDEX idx_payment_orders_status_date ON payment.orders (status, created_at)');
        $this->addSql('CREATE TABLE payment.payouts (id UUID NOT NULL, instructor_id UUID NOT NULL, gross_amount NUMERIC(15, 2) NOT NULL, platform_fee NUMERIC(15, 2) NOT NULL, net_amount NUMERIC(15, 2) NOT NULL, status VARCHAR(20) NOT NULL, method VARCHAR(50) NOT NULL, bank_details JSON NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_payment_payouts_instructor_status ON payment.payouts (instructor_id, status)');
        $this->addSql('CREATE INDEX idx_payment_payouts_status_date ON payment.payouts (status, requested_at)');
        $this->addSql('CREATE TABLE payment.transactions (id UUID NOT NULL, provider VARCHAR(50) NOT NULL, provider_txn_id VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, amount NUMERIC(15, 2) NOT NULL, currency VARCHAR(3) NOT NULL, provider_response JSON DEFAULT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, order_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_payment_txn_order ON payment.transactions (order_id)');
        $this->addSql('CREATE INDEX idx_payment_txn_provider ON payment.transactions (provider, provider_txn_id)');
        $this->addSql('CREATE INDEX idx_payment_txn_status_date ON payment.transactions (status, created_at)');
        $this->addSql('ALTER TABLE payment.order_items ADD CONSTRAINT FK_2BE380F18D9F6D38 FOREIGN KEY (order_id) REFERENCES payment.orders (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE payment.orders ADD CONSTRAINT FK_8A08BDE766C5951B FOREIGN KEY (coupon_id) REFERENCES payment.coupons (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE payment.transactions ADD CONSTRAINT FK_EB3A08578D9F6D38 FOREIGN KEY (order_id) REFERENCES payment.orders (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA assessment');
        $this->addSql('CREATE SCHEMA notification');
        $this->addSql('CREATE SCHEMA enrollment');
        $this->addSql('CREATE SCHEMA course');
        $this->addSql('CREATE SCHEMA iam');
        $this->addSql('ALTER TABLE payment.order_items DROP CONSTRAINT FK_2BE380F18D9F6D38');
        $this->addSql('ALTER TABLE payment.orders DROP CONSTRAINT FK_8A08BDE766C5951B');
        $this->addSql('ALTER TABLE payment.transactions DROP CONSTRAINT FK_EB3A08578D9F6D38');
        $this->addSql('DROP TABLE payment.coupons');
        $this->addSql('DROP TABLE payment.order_items');
        $this->addSql('DROP TABLE payment.orders');
        $this->addSql('DROP TABLE payment.payouts');
        $this->addSql('DROP TABLE payment.transactions');
    }
}
