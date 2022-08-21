<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function add(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllTricksWithDefaultImage()
    {
/*
dd($this->createQueryBuilder('t')
->leftJoin('t.trickImages', 'ti')
->where('ti.isDefault = 1')
->addOrderBy('t.name')
->getQuery()
->getResult());
*/
        $db = $this->getEntityManager()->getConnection();

        $sql = "SELECT  t.*,
                        ti.path AS imagePath
                FROM trick t
                LEFT JOIN trick_image ti ON ti.trick_id = t.id
                WHERE ti.is_default = 1";
        
        $stmt = $db->prepare($sql);

        return $stmt->executeQuery()->fetchAllAssociative();

/*
        return $this->createQueryBuilder('t')
            ->leftJoin('t.trickImages', 'ti')
            ->where('ti.isDefault = 1')
            ->addOrderBy('t.name')
            ->getQuery()
            ->getResult();
*/
    }


    public function findTrickInfo(Trick $trick)
    {

        $db = $this->getEntityManager()->getConnection();

        $sql = "SELECT
                    MIN(h.date) AS createdAt,
                    MAX(h.date) AS updatedAt,
                    ti.path AS defaultImage
                FROM trick_history h
                LEFT JOIN trick_image ti ON ti.trick_id = :trickId
                WHERE h.trick_id = :trickId AND ti.is_default = 1";
        
        $stmt = $db->prepare($sql);

        return $stmt->executeQuery(['trickId' => $trick->getId()])->fetchAssociative();
    }


//    /**
//     * @return Trick[] Returns an array of Trick objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Trick
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
