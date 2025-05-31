<?php

namespace App\Repository;

use App\Entity\Conge;
use App\Entity\User;
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

    public function findTeamLeavesByDateRange(array $teamMembers, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        if (empty($teamMembers)) {
            return [];
        }

        $teamMembers = array_filter($teamMembers, fn($user) => $user !== null);

        if (empty($teamMembers)) {
            return [];
        }

        $userIds = array_map(fn($user) => $user->getId(), $teamMembers);

        return $this->createQueryBuilder('c')
            ->andWhere('c.user IN (:userIds)')
            ->andWhere('(c.dateDebut <= :end AND c.dateFin >= :start)')
            ->setParameter('userIds', $userIds)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserDepartment(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.departement = :departement')
            ->setParameter('departement', $user->getDepartement())
            ->andWhere('u != :user')
            ->setParameter('user', $user)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les congés pour un mois/année donné avec un statut spécifique (ex : "Approuvé")
     */
    public function findByMonthYearAndStatus(int $month, int $year, string $status): array
    {
        // Protection basique : statut doit être une des valeurs prévues
        $allowedStatuses = ['Approuvé', 'En attente', 'Refusé'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException("Statut de congé invalide : $status");
        }

        $start = new \DateTimeImmutable("$year-$month-01");
        $end = $start->modify('last day of this month');

        return $this->createQueryBuilder('c')
            ->andWhere('c.dateDebut <= :end')
            ->andWhere('c.dateFin >= :start')
            ->andWhere('c.statut = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', $status)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
