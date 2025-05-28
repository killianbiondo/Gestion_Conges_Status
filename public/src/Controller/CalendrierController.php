<?php

namespace App\Controller;

use App\Repository\CongeRepository;
use App\Repository\PeriodeFermetureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendrierController extends AbstractController
{
    #[Route('/calendrier', name: 'stats_team_calendar')]
    public function index(Request $request, CongeRepository $congesRepo, PeriodeFermetureRepository $periodeFermetureRepo): Response
    {
        $month = $request->query->getInt('month', (int)date('m'));
        $year = $request->query->getInt('year', (int)date('Y'));

        // ✅ On ne récupère que les congés approuvés
        $conges = $congesRepo->findByMonthYearAndStatus($month, $year, 'Approuvé');

        // Récupération des périodes de fermeture
        $fermetures = $periodeFermetureRepo->findFermeturesEntreDates(
            new \DateTime("$year-$month-01"),
            new \DateTime("$year-$month-" . date('t', strtotime("$year-$month-01")))
        );

        $calendarData = [];

        // ✅ Transformation des congés approuvés en événements
        foreach ($conges as $conge) {
            $calendarData[] = [
                'title' => $conge->getUser()->getFullName(),
                'start' => $conge->getDateDebut()->format('Y-m-d'),
                'end' => $conge->getDateFin()->modify('+1 day')->format('Y-m-d'),
                'backgroundColor' => $this->getLeaveTypeColor($conge->getType()),
                'description' => 'Congé : ' . $conge->getType(),
                'type' => 'Congé',
                'userName' => $conge->getUser()->getFullName(),
                'leaveType' => $conge->getType(),
                'status' => $conge->getStatut(),
                'comment' => $conge->getCommentaire(),
            ];
        }

        // ✅ Transformation des fermetures en événements
        foreach ($fermetures as $fermeture) {
            $calendarData[] = [
                'title' => $fermeture->getTitre(),
                'start' => $fermeture->getDateDebut()->format('Y-m-d'),
                'end' => $fermeture->getDateFin()->modify('+1 day')->format('Y-m-d'),
                'backgroundColor' => '#f44336',
                'description' => 'Fermeture : ' . $fermeture->getTitre(),
                'type' => 'Fermeture',
            ];
        }

        return $this->render('stats/team_calendar.html.twig', [
            'calendarData' => $calendarData,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    private function getLeaveTypeColor(string $type): string
    {
        return match ($type) {
            'CP' => '#28a745',    // Vert : Congé payé
            'CM' => '#dc3545',    // Rouge : Maladie
            'RTT' => '#6f42c1',   // Violet : RTT
            default => '#007bff', // Bleu par défaut
        };
    }
}
