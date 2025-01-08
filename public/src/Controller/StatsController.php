<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CongeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class StatsController extends AbstractController
{
    private $congeRepository;
    private $userRepository;

    public function __construct(CongeRepository $congeRepository, UserRepository $userRepository)
    {
        $this->congeRepository = $congeRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/stats', name: 'app_stats')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer le nombre total de congés
        $totalConges = $this->congeRepository->count([]);
        $congesParType = $this->congeRepository->createQueryBuilder('c')
            ->select('c.type, COUNT(c.id) as count')
            ->groupBy('c.type')
            ->getQuery()
            ->getResult();

        // Requête SQL pour récupérer le nombre total de congés par type
        $connection = $entityManager->getConnection();
        $sql = 'SELECT c.type, COUNT(c.id) as count 
                FROM conge c 
                GROUP BY c.type';
        $stmt = $connection->prepare($sql);
        $congesParTypeSQL = $stmt->executeQuery()->fetchAllAssociative();

        return $this->render('stats/index.html.twig', [
            'totalConges' => $totalConges,
            'congesParType' => $congesParType,
            'congesParTypeSQL' => $congesParTypeSQL,
        ]);
    }

    #[Route('/stats/user', name: 'app_stats_user')]
    public function statsParUtilisateur(EntityManagerInterface $entityManager): Response
    {
        // Requête DQL pour compter les congés par utilisateur
        $startTimeDQL = microtime(true);
        $congesParUser = $this->congeRepository->createQueryBuilder('c')
            ->select('u.nom, u.prenom, COUNT(c.id) as count')
            ->join('c.user', 'u')
            ->groupBy('u.id')
            ->getQuery()
            ->getResult();
        $endTimeDQL = microtime(true);
        $executionTimeDQL = round(($endTimeDQL - $startTimeDQL) * 1000, 2); // Temps en millisecondes, arrondi à 2 décimales

        // Requête SQL directe pour compter les congés par utilisateur
        $startTimeSQL = microtime(true);
        $connection = $entityManager->getConnection();
        $sql = 'SELECT u.nom, u.prenom, COUNT(c.id) as count 
            FROM conge c 
            JOIN `user` u ON c.user_id = u.id 
            GROUP BY u.nom, u.prenom';

        $stmt = $connection->prepare($sql);
        $congesParUserSQL = $stmt->executeQuery()->fetchAllAssociative();
        $endTimeSQL = microtime(true);
        $executionTimeSQL = round(($endTimeSQL - $startTimeSQL) * 1000, 2); // Temps en millisecondes, arrondi à 2 décimales

        return $this->render('stats/user_stats.html.twig', [
            'congesParUser' => $congesParUser,
            'executionTimeDQL' => $executionTimeDQL . " ms",
            'congesParUserSQL' => $congesParUserSQL,
            'executionTimeSQL' => $executionTimeSQL . " ms",
        ]);
    }
}