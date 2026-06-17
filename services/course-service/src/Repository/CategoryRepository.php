<?php

declare(strict_types=1);

namespace App\Course\Repository;

use App\Course\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Returns only root-level categories (no parent), active only.
     *
     * @return Category[]
     */
    public function findRoots(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.parent IS NULL')
            ->andWhere('c.isActive = true')
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the full tree: root categories with their children eagerly loaded.
     *
     * @return Category[]
     */
    public function findTree(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->where('c.parent IS NULL')
            ->andWhere('c.isActive = true')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('ch.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    /**
     * @return Category[]
     */
    public function findChildren(string $parentId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.parent = :parentId')
            ->andWhere('c.isActive = true')
            ->setParameter('parentId', $parentId)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function isSlugTaken(string $slug, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('c.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function save(Category $category, bool $flush = false): void
    {
        $this->getEntityManager()->persist($category);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $category, bool $flush = false): void
    {
        $this->getEntityManager()->remove($category);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}