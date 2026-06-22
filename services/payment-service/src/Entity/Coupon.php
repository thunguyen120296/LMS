<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DiscountType;
use App\Repository\CouponRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupons', schema: 'payment')]
#[ORM\Index(columns: ['is_active', 'starts_at', 'expires_at'], name: 'idx_payment_coupons_active')]
#[ORM\HasLifecycleCallbacks]
class Coupon
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $code;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: DiscountType::class)]
    private DiscountType $discountType;

    /** Percentage (0–100) or fixed amount in currency */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $discountValue;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $minPurchase = null;

    /** null = unlimited */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxUses = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $usesCount = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Business logic ---

    public function isValid(): bool
    {
        if (!$this->isActive) return false;

        $now = new \DateTimeImmutable();

        if ($this->startsAt !== null && $now < $this->startsAt) return false;
        if ($this->expiresAt !== null && $now > $this->expiresAt) return false;
        if ($this->maxUses !== null && $this->usesCount >= $this->maxUses) return false;

        return true;
    }

    public function computeDiscount(float $subtotal): float
    {
        if (!$this->isValid()) return 0.0;

        if ($this->discountType === DiscountType::Percentage) {
            return min($subtotal, $subtotal * ((float) $this->discountValue / 100));
        }

        return min($subtotal, (float) $this->discountValue);
    }

    public function incrementUsage(): void
    {
        ++$this->usesCount;
    }

    public function getRemainingUses(): ?int
    {
        if ($this->maxUses === null) return null;
        return max(0, $this->maxUses - $this->usesCount);
    }

    // --- Getters & Setters ---

    public function getId(): string { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = strtoupper($code); return $this; }
    public function getDiscountType(): DiscountType { return $this->discountType; }
    public function setDiscountType(DiscountType $type): static { $this->discountType = $type; return $this; }
    public function getDiscountValue(): string { return $this->discountValue; }
    public function setDiscountValue(string $value): static { $this->discountValue = $value; return $this; }
    public function getMinPurchase(): ?string { return $this->minPurchase; }
    public function setMinPurchase(?string $min): static { $this->minPurchase = $min; return $this; }
    public function getMaxUses(): ?int { return $this->maxUses; }
    public function setMaxUses(?int $max): static { $this->maxUses = $max; return $this; }
    public function getUsesCount(): int { return $this->usesCount; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): static { $this->isActive = $active; return $this; }
    public function getStartsAt(): ?\DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(?\DateTimeImmutable $dt): static { $this->startsAt = $dt; return $this; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeImmutable $dt): static { $this->expiresAt = $dt; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}