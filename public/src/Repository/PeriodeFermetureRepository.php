<?php

namespace App\Repository;

use App\Entity\PeriodeFermeture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PeriodeFermeture|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeriodeFermeture|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeriodeFermeture[]    findAll()
 * @method PeriodeFermeture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodeFermetureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeriodeFermeture::class);
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.dateDebut >= :startDate')
            ->andWhere('p.dateFin <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
