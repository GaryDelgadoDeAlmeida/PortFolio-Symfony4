<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(PersistenceManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param Project entity
     * @param bool flush (save into database)
     */
    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Project entity
     * @param bool flush (save into database)
     */
    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get the lastest project added
     */
    public function getLastestProject(int $maxResults = 3)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all project added to the database
     */
    public function getProject(int $offset, int $limit)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get projects using name
     */
    public function getProjectByName(string $projectName)
    {
        return $this->createQueryBuilder('p')
            ->where("p.name LIKE :projectName")
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter(':projectName', '%'.$projectName.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get a project by the name and the version
     * 
     * @param string name
     * @param int version
     * @return Project
     */
    public function getProjectByNameAndVersion(string $name, int $version)
    {
        return $this->createQueryBuilder('p')
            ->where("p.name LIKE :projectName")
            ->andWhere("p.version = :version")
            ->setParameters([
                "projectName" => $name,
                "version" => $version,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Count all existing project
     */
    public function countProject()
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as nbrProject')
            ->getQuery()
            ->getSingleResult()["nbrProject"];
    }
}
