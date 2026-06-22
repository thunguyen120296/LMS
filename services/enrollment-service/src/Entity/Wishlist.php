<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WishlistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ORM\Table(name: 'wishlists', schema: 'enrollment')]
#[ORM\UniqueConstraint(name: 'uq_enrollment_wishlist', columns: ['user_id', 'course_id'])]
#[ORM\Index(columns: ['user_id'], name: 'idx_enrollment_wishlist_user')]
class Wishlist
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    /** Cross-service ref */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    /** Cross-service ref */
    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $addedAt;

    public function __construct(string $userId, string $courseId)
    {
        $this->userId   = $userId;
        $this->courseId = $courseId;
        $this->addedAt  = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): string { return $this->userId; }
    public function getCourseId(): string { return $this->courseId; }
    public function getAddedAt(): \DateTimeImmutable { return $this->addedAt; }
}