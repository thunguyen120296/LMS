<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items', schema: 'payment')]
#[ORM\Index(columns: ['course_id'], name: 'idx_payment_order_items_course')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    /** Cross-service ref to course.courses */
    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    /**
     * Snapshot of the course title at time of purchase.
     * Decoupled from Course Service — survives course renames.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $courseTitle;

    /** Original price at purchase time */
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $unitPrice;

    /** Price after coupon/discount — updated by Order::recalculateTotals() */
    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private string $finalPrice;

    public function __construct(
        Order $order,
        string $courseId,
        string $courseTitle,
        string $unitPrice,
    ) {
        $this->order       = $order;
        $this->courseId    = $courseId;
        $this->courseTitle = $courseTitle;
        $this->unitPrice   = $unitPrice;
        $this->finalPrice  = $unitPrice; // default; adjusted by Order
    }

    public function getId(): string { return $this->id; }
    public function getOrder(): Order { return $this->order; }
    public function getCourseId(): string { return $this->courseId; }
    public function getCourseTitle(): string { return $this->courseTitle; }
    public function getUnitPrice(): string { return $this->unitPrice; }
    public function getFinalPrice(): string { return $this->finalPrice; }
    public function setFinalPrice(string $finalPrice): void { $this->finalPrice = $finalPrice; }
}