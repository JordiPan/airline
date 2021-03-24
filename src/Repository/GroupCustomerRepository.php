<?php

namespace App\Repository;

use App\Entity\GroupCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GroupCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupCustomer[]    findAll()
 * @method GroupCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupCustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupCustomer::class);
    }

    // /**
    //  * @return GroupCustomer[] Returns an array of GroupCustomer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GroupCustomer
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
