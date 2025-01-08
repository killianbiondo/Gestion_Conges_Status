<?php

namespace App\Repository;

use App\Entity\Conge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conge>
 *
 * @method Conge|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conge|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conge[]    findAll()
 * @method Conge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CongeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conge::class);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findCate(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT c.type, COUNT(c.id) as count 
                FROM conge c 
                GROUP BY c.type
                ORDER BY type ASC';

        return $connection->prepare($sql)->executeQuery()->fetchAllAssociative();
    }
}
