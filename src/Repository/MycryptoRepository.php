<?php

namespace App\Repository;

use App\Entity\Mycrypto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mycrypto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mycrypto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mycrypto[]    findAll()
 * @method Mycrypto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MycryptoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mycrypto::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Mycrypto $entity, bool $flush = true): void
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
    public function remove(Mycrypto $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


//    public function findAllJoinedToId(): ?Mycrypto
//    {
//        $entityManager = $this->getEntityManager();
//
//        $query = $entityManager->createQuery(
//            'SELECT p, c
//            FROM App\Entity\Mycrypto p
//            INNER JOIN p.cryptolist c
//            WHERE p.id = :id'
//        )->setParameter('id', $mycryptoId);
//
//        return $query->getOneOrNullResult();
//    }


    // /**
    //  * @return Mycrypto[] Returns an array of Mycrypto objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mycrypto
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
