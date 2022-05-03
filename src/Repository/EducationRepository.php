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

    public function getEducations()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getEducationFromCategory($category)
    {
        return $this->createQueryBuilder('e')
            ->where('e.category = :cat')
            ->setParameter('cat', $category)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Education[] Returns an array of Education objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Education
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
