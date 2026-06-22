<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PayoutStatus;
use App\Repository\PayoutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PayoutRepository::class)]
#[ORM\Table(name: 'payouts', schema: 'payment')]
#[ORM\Index(columns: ['instructor_id', 'status'], name: 'idx_payment_payouts_instructor_status')]
#[ORM\Index(columns: ['status', 'requested_at'], name: 'idx_payment_payouts_status_date')]
#[ORM\HasLifecycleCallbacks]
class Payout
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref to iam.users where role = instructor */
    #[ORM\Column(type: 'uuid')]
    private string $instructorId;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $grossAmount;

    /** Platform commission (e.g. 30% of gross) */
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $platformFee;

    /** Net = gross - platformFee */
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $netAmount;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: PayoutStatus::class)]
    private PayoutStatus $status = PayoutStatus::Pending;

    /** e.g. "bank_transfer", "paypal", "momo" */
    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $method;

    /**
     * Encrypted or masked bank details snapshot.
     *
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $bankDetails = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    public function __construct(
        string $instructorId,
        string $grossAmount,
        string $platformFee,
        string $method,
        array $bankDetails = [],
    ) {
        $this->instructorId = $instructorId;
        $this->grossAmount  = $grossAmount;
        $this->platformFee  = $platformFee;
        $this->netAmount    = number_format((float) $grossAmount - (float) $platformFee, 2, '.', '');
        $this->method       = $method;
        $this->bankDetails  = $bankDetails;
        $this->requestedAt  = new \DateTimeImmutable();
    }

    // --- Business logic ---

    public function approve(): void
    {
        if ($this->status !== PayoutStatus::Pending) {
            throw new \DomainException('Only pending payouts can be approved.');
        }
        $this->status      = PayoutStatus::Processing;
    }

    public function markCompleted(): void
    {
        $this->status      = PayoutStatus::Completed;
        $this->processedAt = new \DateTimeImmutable();
    }

    public function reject(string $reason = ''): void
    {
        $this->status      = PayoutStatus::Rejected;
        $this->processedAt = new \DateTimeImmutable();
        if ($reason) {
            $this->bankDetails['rejection_reason'] = $reason;
        }
    }

    public function fail(): void
    {
        $this->status      = PayoutStatus::Failed;
        $this->processedAt = new \DateTimeImmutable();
    }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getInstructorId(): string { return $this->instructorId; }
    public function getGrossAmount(): string { return $this->grossAmount; }
    public function getPlatformFee(): string { return $this->platformFee; }
    public function getNetAmount(): string { return $this->netAmount; }
    public function getStatus(): PayoutStatus { return $this->status; }
    public function getMethod(): string { return $this->method; }
    public function getBankDetails(): array { return $this->bankDetails; }
    public function getRequestedAt(): \DateTimeImmutable { return $this->requestedAt; }
    public function getProcessedAt(): ?\DateTimeImmutable { return $this->processedAt; }
}