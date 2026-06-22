<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders', schema: 'payment')]
#[ORM\Index(columns: ['user_id', 'status'], name: 'idx_payment_orders_user_status')]
#[ORM\Index(columns: ['status', 'created_at'], name: 'idx_payment_orders_status_date')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref to iam.users */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: Types::STRING, length: 30, unique: true)]
    private string $orderNumber;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::Pending;

    #[ORM\Column(type: Types::STRING, length: 3, options: ['default' => 'VND'])]
    private string $currency = 'VND';

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00'])]
    private string $subtotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00'])]
    private string $discountAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00'])]
    private string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, options: ['default' => '0.00'])]
    private string $totalAmount = '0.00';

    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(name: 'coupon_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Coupon $coupon = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Transaction::class, cascade: ['persist'], orphanRemoval: false)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $transactions;

    public function __construct(string $userId, string $orderNumber, string $currency = 'VND')
    {
        $this->userId      = $userId;
        $this->orderNumber = $orderNumber;
        $this->currency    = $currency;
        $this->items       = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // --- Business logic ---

    public function addItem(OrderItem $item): void
    {
        $this->items->add($item);
        $this->recalculateTotals();
    }

    public function applyCoupon(Coupon $coupon): void
    {
        if (!$coupon->isValid()) {
            throw new \DomainException('Coupon is not valid or has expired.');
        }

        $subtotal = (float) $this->subtotal;
        if ($coupon->getMinPurchase() !== null && $subtotal < (float) $coupon->getMinPurchase()) {
            throw new \DomainException(sprintf(
                'Minimum purchase amount of %s required for this coupon.',
                $coupon->getMinPurchase()
            ));
        }

        $this->coupon = $coupon;
        $this->recalculateTotals();
    }

    public function recalculateTotals(): void
    {
        $subtotal = array_reduce(
            $this->items->toArray(),
            fn(float $carry, OrderItem $item) => $carry + (float) $item->getUnitPrice(),
            0.0
        );

        $discount = 0.0;
        if ($this->coupon !== null) {
            $discount = $this->coupon->computeDiscount($subtotal);
        }

        $this->subtotal       = number_format($subtotal, 2, '.', '');
        $this->discountAmount = number_format($discount, 2, '.', '');
        $this->totalAmount    = number_format(max(0, $subtotal - $discount + (float) $this->taxAmount), 2, '.', '');

        // Update each item's finalPrice
        foreach ($this->items as $item) {
            $ratio = $subtotal > 0 ? ((float) $item->getUnitPrice() / $subtotal) : 0;
            $itemDiscount = $discount * $ratio;
            $item->setFinalPrice(
                number_format(max(0, (float) $item->getUnitPrice() - $itemDiscount), 2, '.', '')
            );
        }
    }

    public function markAsPaid(): void
    {
        if ($this->status !== OrderStatus::Pending) {
            throw new \DomainException('Only pending orders can be marked as paid.');
        }
        $this->status = OrderStatus::Paid;
        $this->coupon?->incrementUsage();
    }

    public function markAsFailed(): void
    {
        $this->status = OrderStatus::Failed;
    }

    public function refund(): void
    {
        if ($this->status !== OrderStatus::Paid) {
            throw new \DomainException('Only paid orders can be refunded.');
        }
        $this->status = OrderStatus::Refunded;
    }

    public function cancel(): void
    {
        if ($this->status === OrderStatus::Paid) {
            throw new \DomainException('Paid orders must be refunded, not cancelled.');
        }
        $this->status = OrderStatus::Cancelled;
    }

    public function isPaid(): bool { return $this->status === OrderStatus::Paid; }
    public function isPending(): bool { return $this->status === OrderStatus::Pending; }

    // --- Getters ---

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getStatus(): OrderStatus { return $this->status; }
    public function getCurrency(): string { return $this->currency; }
    public function getSubtotal(): string { return $this->subtotal; }
    public function setSubtotal(string $subtotal): static { $this->subtotal = $subtotal; return $this; }
    public function getDiscountAmount(): string { return $this->discountAmount; }
    public function getTaxAmount(): string { return $this->taxAmount; }
    public function setTaxAmount(string $tax): static { $this->taxAmount = $tax; return $this; }
    public function getTotalAmount(): string { return $this->totalAmount; }
    public function getCoupon(): ?Coupon { return $this->coupon; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function getItems(): Collection { return $this->items; }
    public function getTransactions(): Collection { return $this->transactions; }
}