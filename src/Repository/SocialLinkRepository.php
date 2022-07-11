<?php

namespace App\Repository;

use App\Entity\SocialLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

/**
 * @method SocialLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialLink[]    findAll()
 * @method SocialLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialLinkRepository extends ServiceEntityRepository
{
    public function __construct(PersistenceManagerRegistry $registry)
    {
        parent::__construct($registry, SocialLink::class);
    }
}
