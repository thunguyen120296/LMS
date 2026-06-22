<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Template;
use App\Enum\NotificationChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Template> */
class TemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    public function findActiveByCode(string $code, string $locale = 'vi', ?NotificationChannel $channel = null): ?Template
    {
        $criteria = ['code' => $code, 'locale' => $locale, 'isActive' => true];
        if ($channel !== null) {
            $criteria['channel'] = $channel;
        }

        return $this->findOneBy($criteria);
    }
}
