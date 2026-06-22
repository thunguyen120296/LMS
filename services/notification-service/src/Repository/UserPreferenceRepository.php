<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserPreference> */
class UserPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPreference::class);
    }

    public function findByUserId(string $userId): ?UserPreference
    {
        return $this->findOneBy(['userId' => $userId]);
    }

    public function getOrCreate(string $userId): UserPreference
    {
        return $this->findByUserId($userId) ?? new UserPreference($userId);
    }

    public function save(UserPreference $preference, bool $flush = false): void
    {
        $this->getEntityManager()->persist($preference);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
