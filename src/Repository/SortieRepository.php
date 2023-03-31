<?php

namespace App\Repository;

use App\Entity\Sortie;

use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    /**
     * @return Sortie[] Returns an array of Sortie objects
     */


    public function findSorties($participantId)
    {

        $qb = $this->createQueryBuilder('s');
        $qb->innerjoin('s.participants', 'p')
            ->where('p.id = :participantId')
            ->setParameter('participantId', $participantId);


        return $qb->getQuery()->getResult();
    }

    public function findSortiesnoninscrite($participantId)
    {
        $qb = $this->createQueryBuilder('s');
        $nots = $qb->innerjoin('s.participants', 'p')
            ->where('p.id = :participantId')
            ->setParameter('participantId', $participantId);

        $qb = $this->createQueryBuilder('s2');
        $libre = $qb->leftjoin('s2.participants', 'p2')
            ->where('p2.id IS NULL')
            ->orWhere('s2.id NOT IN (' . $nots . ')')
            ->setParameter('participantId', $participantId);

        return $libre->getQuery()->getResult();

    }

    public function findbySortiestext(string $text)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.nom LIKE :nom')
            ->setParameter('nom', '%' . $text . '%')
            ->getQuery()
            ->getResult();

        return $qb->getQuery()->getResult();
    }

    public function findbySortiesdate(DateTime $debut1,DateTime $debut2)
    {

        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.dateHeureDebut > :debut1')
            ->andWhere('s.dateHeureDebut < :debut2')
            ->setParameter('debut1', $debut1)
            ->setParameter('debut2',  $debut2);

        return $qb->getQuery()->getResult();
    }

    public function findSortiespasses()
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.etat > :idetat')
            ->setParameter('idetat',  4);
             return $qb->getQuery()->getResult();

    }




}
