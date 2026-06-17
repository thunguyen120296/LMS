<?php

declare(strict_types=1);

namespace App\Payment\Entity;

use App\Payment\Enum\TransactionStatus;
use App\Payment\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transactions', schema: 'payment')]
#[ORM\Index(columns: ['order_id'], name: 'idx_payment_txn_order')]
#[ORM\Index(columns: ['provider', 'provider_txn_id'], name: 'idx_payment_txn_provider')]
#[ORM\Index(columns: ['status', 'created_at'], name: 'idx_payment_txn_status_date')]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    /** e.g. "vnpay", "momo", "stripe", "paypal" */
    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $provider;

    /** Transaction ID from the payment gateway */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $providerTxnId = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: TransactionStatus::class)]
    private TransactionStatus $status = TransactionStatus::Pending;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $amount;

    #[ORM\Column(type: Types::STRING, length: 3)]
    private string $currency;

    /**
     * Raw JSON response from the payment gateway — for audit/debugging.
     *
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $providerResponse = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(Order $order, string $provider, string $amount, string $currency)
    {
        $this->order    = $order;
        $this->provider = $provider;
        $this->amount   = $amount;
        $this->currency = $currency;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Business logic ---

    public function markSuccess(string $providerTxnId, array $response = []): void
    {
        $this->status          = TransactionStatus::Success;
        $this->providerTxnId   = $providerTxnId;
        $this->providerResponse = $response;
        $this->processedAt     = new \DateTimeImmutable();
    }

    public function markFailed(array $response = []): void
    {
        $this->status           = TransactionStatus::Failed;
        $this->providerResponse = $response;
        $this->processedAt      = new \DateTimeImmutable();
    }

    public function markRefunded(array $response = []): void
    {
        $this->status           = TransactionStatus::Refunded;
        $this->providerResponse = array_merge($this->providerResponse ?? [], $response);
        $this->processedAt      = new \DateTimeImmutable();
    }

    public function isSuccessful(): bool { return $this->status === TransactionStatus::Success; }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getOrder(): Order { return $this->order; }
    public function getProvider(): string { return $this->provider; }
    public function getProviderTxnId(): ?string { return $this->providerTxnId; }
    public function getStatus(): TransactionStatus { return $this->status; }
    public function getAmount(): string { return $this->amount; }
    public function getCurrency(): string { return $this->currency; }
    public function getProviderResponse(): ?array { return $this->providerResponse; }
    public function getProcessedAt(): ?\DateTimeImmutable { return $this->processedAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}