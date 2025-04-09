<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CongeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/stats/team-calendar', name: 'stats_team_calendar')]
    public function teamCalendar(Request $request): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le mois et l'année (par défaut mois actuel)
        $month = $request->query->getInt('month', (int)date('m'));
        $year = $request->query->getInt('year', (int)date('Y'));

        // Si l'utilisateur est un manager, il peut voir l'équipe entière
        // Sinon, il ne verra que ses propres congés
        if ($this->isGranted('ROLE_MANAGER')) {
            // Si l'utilisateur est un manager, récupérer son équipe
            $team = $this->userRepository->findByManager($user);
        } else {
            // Si l'utilisateur n'est pas un manager, récupérer uniquement ses congés
            $team = [$user];
        }

        // Récupérer tous les congés pour l'équipe pour le mois donné
        $firstDay = new \DateTime("$year-$month-01");
        $lastDay = (clone $firstDay)->modify('last day of this month');

        $teamLeaves = $this->congeRepository->findTeamLeavesByDateRange($team, $firstDay, $lastDay);

        // Formater les données des congés pour le calendrier JavaScript
        $formattedLeaves = [];
        foreach ($teamLeaves as $conge) {
            $formattedLeaves[] = [
                'id' => $conge->getId(),
                'title' => $conge->getUser()->getFullName(),
                'start' => $conge->getDateDebut()->format('Y-m-d'),
                'end' => $conge->getDateFin()->format('Y-m-d'),
                'type' => $conge->getType(),
                'backgroundColor' => $this->getColorForLeaveType($conge->getType()),
                'borderColor' => $this->getColorForLeaveType($conge->getType()),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'userName' => $conge->getUser()->getFullName(),
                    'leaveType' => $conge->getType(),
                    'status' => $conge->getStatut(),
                    'comment' => $conge->getCommentaire()
                ]
            ];
        }

        // Préparer les données pour le template
        $calendarData = json_encode($formattedLeaves);

        return $this->render('stats/team_calendar.html.twig', [
            'calendarData' => $calendarData,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    /**
     * Retourne une couleur selon le type de congé
     */
    private function getColorForLeaveType(string $type): string
    {
        return match ($type) {
            'CP' => '#28a745',    // Congé payé - vert
            'CM' => '#dc3545',    // Congé maladie - rouge
            'RTT' => '#6f42c1',   // RTT - violet
            default => '#6c757d', // Défaut - gris
        };
    }
}
