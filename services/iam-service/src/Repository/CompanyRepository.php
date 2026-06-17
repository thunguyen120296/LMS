<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findByName(string $name): ?Company
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @return Company[]
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], ['name' => 'ASC']);
    }

    public function save(Company $company, bool $flush = false): void
    {
        $this->getEntityManager()->persist($company);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Company $company, bool $flush = false): void
    {
        $this->getEntityManager()->remove($company);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
