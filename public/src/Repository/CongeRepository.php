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



    /**
     * Trouve les congés d'une équipe pour une période donnée.
     */
    public function findTeamLeavesByDateRange(array $teamMembers, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        // Vérifier qu'il y a bien des membres dans l'équipe
        if (empty($teamMembers)) {
            return [];
        }

        // Filtrer les utilisateurs valides (non null)
        $teamMembers = array_filter($teamMembers, fn($user) => $user !== null);

        // Si l'équipe est vide après le filtrage, retourner un tableau vide
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

    /**
     * Trouve les membres du même département qu'un utilisateur (hors lui-même).
     *
     * @param User $user L'utilisateur courant
     * @return User[] Liste des collègues du même département
     */
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



}
