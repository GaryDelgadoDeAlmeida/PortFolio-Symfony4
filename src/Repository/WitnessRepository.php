<?php

namespace App\Repository;

use App\Entity\Witness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Witness>
 *
 * @method Witness|null find($id, $lockMode = null, $lockVersion = null)
 * @method Witness|null findOneBy(array $criteria, array $orderBy = null)
 * @method Witness[]    findAll()
 * @method Witness[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WitnessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Witness::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Witness $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Witness $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Count all witness inside the database
     * 
     * @return int Return the number of witness
     */
    public function countWitness()
    {
        return $this->createQueryBuilder("witness")
            ->select("COUNT(witness.id) as nbrWitness")
            ->getQuery()
            ->getSingleResult()["nbrWitness"]
        ;
    }
}
