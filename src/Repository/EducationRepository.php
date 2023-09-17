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

    function add(Education $entity, bool $flush = true) : void {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param int offset
     * @param int limit
     * @return Education[]
     */
    public function getEducations(int $offset, int $limit = 10)
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setFirstResult(($offset - 1) * $limit)
            ->setMaxResults($limit)
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
        return $this->createQueryBuilder('e')
            ->where('e.category = :cat')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($maxResult)
            ->setParameter('cat', $category)
            ->getQuery()
            ->getResult()
        ;
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
            ->setFirstResult(($offset - 1) * $limit)
            ->setMaxResults($limit)
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

    /**
     * @return int Return the number of educations
     */
    public function countEducations()
    {
        return $this->createQueryBuilder("e")
            ->select("COUNT(e.id) as nbrEducations")
            ->getQuery()
            ->getSingleResult()["nbrEducations"]
        ;
    }

    /**
     * @param string category
     * @return int Return the number of educations by the specific category
     */
    public function countEducationsByCategory(string $category)
    {
        return $this->createQueryBuilder("e")
            ->select("COUNT(e.id) as nbrEducations")
            ->where("e.category = :cat")
            ->setParameter('cat', $category)
            ->getQuery()
            ->getSingleResult()["nbrEducations"]
        ;
    }
}
