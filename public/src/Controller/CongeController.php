<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CongeRepository;
use App\Entity\Conge;
use App\Form\CongeType;
use Doctrine\ORM\EntityManagerInterface;

class CongeController extends AbstractController
{
    private $congeRepository;
    private $entityManager;

    public function __construct(CongeRepository $congeRepository, EntityManagerInterface $entityManager)
    {
        $this->congeRepository = $congeRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/conge', name: 'conge')]
    public function index(): Response
    {
        // Utiliser la méthode findAll() pour récupérer tous les congés
        $userConges = $this->congeRepository->findAll();

        // Passer les congés à la vue
        return $this->render('conge/index.html.twig', [
            'userConges' => $userConges,
        ]);
    }

    #[Route('/conge/cate', name: 'conge_cate')]
    public function congeCate(): Response
    {
        $congeCateType = $this->congeRepository->findCate();

        // Passer les congés à la vue
        return $this->render('conge/show_cate.html.twig', [
            'congesTypes' => $congeCateType,
        ]);
    }

    #[Route('/conge/cate/{type}', name: 'conge_show_cate_type')]
    public function congeCateByType($type): Response
    {
        $userConges = $this->congeRepository->findBy([
            'type' => $type
        ]);

        // Passer les congés à la vue
        return $this->render('conge/show_cate_type.html.twig', [
            'userConges' => $userConges,
        ]);
    }

    #[Route('/conge/create', name: 'conge_create')]
    public function create(Request $request): Response
    {
        $conge = new Conge();
        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Par défaut, le statut est "En attente"
            $conge->setStatut('En attente');

            $this->entityManager->persist($conge);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le congé a été créé avec succès!');
            return $this->redirectToRoute('conge');
        }

        return $this->render('conge/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}