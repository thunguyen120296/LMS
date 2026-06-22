<?php
declare(strict_types=1);
namespace App\Repository;
 
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
 
/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }
 
    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
 
    /**
     * @param string[] $slugs
     * @return Tag[]
     */
    public function findBySlugs(array $slugs): array
    {
        if (empty($slugs)) return [];
 
        return $this->createQueryBuilder('t')
            ->where('t.slug IN (:slugs)')
            ->setParameter('slugs', $slugs)
            ->getQuery()
            ->getResult();
    }
 
    /**
     * @return Tag[]
     */
    public function findPopular(int $limit = 20): array
    {
        // Ordered by how many courses use each tag
        return $this->createQueryBuilder('t')
            ->leftJoin('t.courseTags', 'ct')
            ->groupBy('t.id')
            ->orderBy('COUNT(ct.course)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
 
    /**
     * Search tags by partial name — useful for tag autocomplete UI.
     *
     * @return Tag[]
     */
    public function search(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
 
    public function save(Tag $tag, bool $flush = false): void
    {
        $this->getEntityManager()->persist($tag);
        if ($flush) $this->getEntityManager()->flush();
    }
 
    public function remove(Tag $tag, bool $flush = false): void
    {
        $this->getEntityManager()->remove($tag);
        if ($flush) $this->getEntityManager()->flush();
    }
}
