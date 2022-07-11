<?php

namespace App\Repository;

use App\Entity\Skills;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skills>
 *
 * @method Skills|null find($id, $lockMode = null, $lockVersion = null)
 * @method Skills|null findOneBy(array $criteria, array $orderBy = null)
 * @method Skills[]    findAll()
 * @method Skills[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skills::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Skills $entity, bool $flush = true): void
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
    public function remove(Skills $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Get all skills of a category
     * 
     * @param string category
     * @return Skills[]|[]
     */
    public function getSkillsByCategory(string $category)
    {
        return $this->createQueryBuilder("s")
            ->where("s.type = :category")
            ->orderBy("s.id", "ASC")
            ->setParameter("category", $category)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Search a skill by his name and the category
     * 
     * @param string skill
     * @param string category
     * @return Skills|null
     */
    public function searchSkill(string $skill, string $category)
    {
        return $this->createQueryBuilder("s")
            ->where("s.skill LIKE :skill")
            ->andWhere("s.type = :category")
            ->setParameters([
                "skill" => $skill,
                "category" => $category
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
