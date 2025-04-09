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
// Route principale du calendrier
#[Route('/calendrier', name: 'stats_team_calendar')]
public function index(Request $request, CongeRepository $congesRepo, PeriodeFermetureRepository $periodeFermetureRepo): Response
{
// Récupération du mois et de l'année depuis les paramètres de l'URL
$month = $request->query->getInt('month', (int)date('m')); // Mois par défaut au mois courant
$year = $request->query->getInt('year', (int)date('Y'));  // Année par défaut à l'année courante

// Récupération des congés des utilisateurs pour le mois et l'année spécifiés
$conges = $congesRepo->findCongesByMonthAndYear($month, $year);

// Récupération des périodes de fermeture pour le même mois et année
$fermetures = $periodeFermetureRepo->findFermeturesEntreDates(
new \DateTime("$year-$month-01"),
new \DateTime("$year-$month-" . date('t', strtotime("$year-$month-01")))
);

// Formatage des événements de congés pour FullCalendar
$calendarData = [];
foreach ($conges as $conge) {
$calendarData[] = [
'title' => $conge->getUser()->getFullName(), // Nom de l'utilisateur
'start' => $conge->getDateDebut()->format('Y-m-d'),
'end' => $conge->getDateFin()->format('Y-m-d'),
'backgroundColor' => $this->getLeaveTypeColor($conge->getType()), // Couleur en fonction du type
'description' => 'Congé : ' . $conge->getType(), // Description (type de congé)
'type' => 'Congé',
'userName' => $conge->getUser()->getFullName(),
'leaveType' => $conge->getType(),
'status' => $conge->getStatus(),
'comment' => $conge->getCommentaire()
];
}

// Formatage des événements de fermeture pour FullCalendar
foreach ($fermetures as $fermeture) {
$calendarData[] = [
'title' => $fermeture->getTitre(), // Titre de la fermeture
'start' => $fermeture->getDateDebut()->format('Y-m-d'),
'end' => $fermeture->getDateFin()->format('Y-m-d'),
'backgroundColor' => '#f44336', // Couleur pour les fermetures (rouge ici)
'description' => 'Fermeture : ' . $fermeture->getTitre(),
'type' => 'Fermeture',
];
}

// Passage des données à la vue (Twig)
return $this->render('stats/team_calendar.html.twig', [
'calendarData' => $calendarData,
'currentMonth' => $month,
'currentYear' => $year,
]);
}

// Fonction pour obtenir la couleur en fonction du type de congé
private function getLeaveTypeColor(string $type): string
{
switch ($type) {
case 'CP':
return '#28a745'; // Couleur pour Congé payé
case 'CM':
return '#dc3545'; // Couleur pour Congé Maladie
case 'RTT':
return '#fd7e14'; // Couleur pour RTT
default:
return '#007bff'; // Couleur par défaut
}
}
}
