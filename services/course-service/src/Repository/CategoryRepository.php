<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Enum\CourseStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    // ----------------------------------------------------------------
    // Single finders
    // ----------------------------------------------------------------

    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findActiveBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    /**
     * Tìm category kèm children — dùng cho admin detail page.
     */
    public function findWithChildren(string $id): ?Category
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->orderBy('ch.sortOrder', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    // ----------------------------------------------------------------
    // Tree queries
    // ----------------------------------------------------------------

    /**
     * Chỉ trả về root categories (không có parent), đang active.
     * Dùng cho top-nav menu.
     *
     * @return Category[]
     */
    public function findRoots(bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.parent IS NULL')
            ->orderBy('c.sortOrder', 'ASC');

        if ($activeOnly) {
            $qb->andWhere('c.isActive = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Full tree: root + children eagerly loaded trong 1 query.
     * Dùng cho sidebar navigation, sitemap.
     *
     * @return Category[]
     */
    public function findTree(bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->where('c.parent IS NULL')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('ch.sortOrder', 'ASC');

        if ($activeOnly) {
            $qb->andWhere('c.isActive = true')
               ->andWhere('ch.isActive = true OR ch.isActive IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Sub-categories của một root category.
     *
     * @return Category[]
     */
    public function findChildren(string $parentId, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.parent = :parentId')
            ->setParameter('parentId', $parentId)
            ->orderBy('c.sortOrder', 'ASC');

        if ($activeOnly) {
            $qb->andWhere('c.isActive = true');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Tất cả categories (root + sub) flatten — dùng cho admin select dropdown.
     *
     * @return Category[]
     */
    public function findAllFlat(bool $activeOnly = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'p')
            ->addSelect('p')
            ->orderBy('COALESCE(p.sortOrder, c.sortOrder)', 'ASC')
            ->addOrderBy('c.sortOrder', 'ASC');

        if ($activeOnly) {
            $qb->where('c.isActive = true');
        }

        return $qb->getQuery()->getResult();
    }

    // ----------------------------------------------------------------
    // Admin / stats queries
    // ----------------------------------------------------------------

    /**
     * Tìm categories có ít nhất 1 course published — dùng cho browse page.
     *
     * @return Category[]
     */
    public function findWithCourses(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = true')
            ->andWhere('c.courseCount > 0')
            ->orderBy('c.courseCount', 'DESC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recount và update courseCount cho một category.
     * Gọi từ scheduled job hoặc sau bulk import.
     */
    public function recomputeCourseCount(string $categoryId): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Category cat
             SET cat.courseCount = (
                 SELECT COUNT(c.id)
                 FROM App\Entity\Course c
                 WHERE c.category = cat
                   AND c.status = :published
                   AND c.deletedAt IS NULL
             )
             WHERE cat.id = :id'
        )
        ->setParameter('published', CourseStatus::Published)
        ->setParameter('id', $categoryId)
        ->execute();
    }

    /**
     * Recount tất cả categories — dùng cho maintenance job.
     */
    public function recomputeAllCourseCounts(): void
    {
        // Lấy danh sách category IDs rồi update từng cái
        $ids = $this->createQueryBuilder('c')
            ->select('c.id')
            ->getQuery()
            ->getScalarResult();

        foreach ($ids as $row) {
            $this->recomputeCourseCount($row['id']);
        }
    }

    // ----------------------------------------------------------------
    // Uniqueness checks
    // ----------------------------------------------------------------

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

    /**
     * Sort order cao nhất hiện tại ở cùng level — dùng khi thêm mới.
     * level = root (parentId = null) hoặc sub (parentId = uuid)
     */
    public function findMaxSortOrder(?string $parentId = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('MAX(c.sortOrder)');

        if ($parentId === null) {
            $qb->where('c.parent IS NULL');
        } else {
            $qb->where('c.parent = :parentId')
               ->setParameter('parentId', $parentId);
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result !== null ? (int) $result : -1;
    }

    // ----------------------------------------------------------------
    // Persist helpers
    // ----------------------------------------------------------------

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
