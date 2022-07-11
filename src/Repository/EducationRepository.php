<?php

namespace App\Repository;

use App\Entity\Education;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

/**
 * @method Education|null find($id, $lockMode = null, $lockVersion = null)
 * @method Education|null findOneBy(array $criteria, array $orderBy = null)
 * @method Education[]    findAll()
 * @method Education[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EducationRepository extends ServiceEntityRepository
{
    public function __construct(PersistenceManagerRegistry $registry)
    {
        parent::__construct($registry, Education::class);
    }

    /**
     * @return Education[]
     */
    public function getEducations()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string category
     * @param int maxResults
     * @return Education[]
     */
    public function getLatestEducationFromCategory(string $category, int $maxResult = 3)
    {
        return [];
    }

    /**
     * @param string category (experience or formation)
     * @param int offset
     * @param int limit
     * @return Education[]
     */
    public function getEducationFromCategory(string $category, int $offset, int $limit)
    {
        return $this->createQueryBuilder('e')
            ->where('e.category = :cat')
            ->setFirstResult($offset - 1)
            ->setMaxResults(($offset - 1) * $limit)
            ->setParameter('cat', $category)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string category (experience or formation)
     * @return Education[]
     */
    public function getIntervalEducationFromCategory(string $category)
    {
        return $this->createQueryBuilder("e")
            ->select("e.startDate, e.endDate")
            ->where("e.category = :cat")
            ->setParameter('cat', $category)
            ->getQuery()
            ->getResult()
        ;
    }
}
