<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(PersistenceManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function getLatestMails(int $maxResults = 3)
    {
        return $this->createQueryBuilder('c')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return a limited number of mails of the database
     */
    public function getMails(int $offset, int $limit)
    {
        return $this->createQueryBuilder('c')
            ->setFirstResult(($offset - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy("c.createdAt", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Count all mail sended and stored into my database
     */
    public function countContact()
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as nbrContact')
            ->where('c.isRead = 0')
            ->getQuery()
            ->getSingleResult()["nbrContact"]
        ;
    }
    
}
