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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $userConges = $this->congeRepository->findAll();

        return $this->render('conge/index.html.twig', [
            'userConges' => $userConges,
        ]);
    }

    #[Route('/conge/cate', name: 'conge_cate')]
    public function congeCate(): Response
    {
        $congeCateType = $this->congeRepository->findCate();

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

    #[Route('/conge/edit/{id}', name: 'app_conge_edit')]
    public function edit(Request $request, Conge $conge, EntityManagerInterface $entityManager): Response
    {
        if ($conge->getUser() !== $this->getUser()) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier cette demande de congé.');
        }

        if ($conge->getStatut() !== 'En attente') {
            $this->addFlash('error', 'Vous ne pouvez pas modifier une demande de congé déjà traitée.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        $form = $this->createForm(CongeType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $conge->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de congé a été modifiée avec succès.');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('conge/edit.html.twig', [
            'form' => $form->createView(),
            'conge' => $conge,
        ]);
    }

    // ✅ Approuver un congé
    #[Route('/conge/{id}/approve', name: 'conge_approve')]
    public function approve(int $id, EntityManagerInterface $em): Response
    {
        $conge = $em->getRepository(Conge::class)->find($id);

        if (!$conge) {
            throw $this->createNotFoundException('Le congé n\'existe pas.');
        }

        $conge->setStatut('Approuvé');
        $em->flush();

        $this->addFlash('success', 'Le congé a été approuvé.');
        return $this->redirectToRoute('conge');
    }

    // ✅ Refuser un congé
    #[Route('/conge/{id}/reject', name: 'conge_reject')]
    public function reject(int $id, EntityManagerInterface $em): Response
    {
        $conge = $em->getRepository(Conge::class)->find($id);

        if (!$conge) {
            throw $this->createNotFoundException('Le congé n\'existe pas.');
        }

        $conge->setStatut('Refusé');
        $em->flush();

        $this->addFlash('success', 'Le congé a été refusé.');
        return $this->redirectToRoute('conge');
    }

    // ✅ Remettre un congé en attente
    #[Route('/conge/{id}/pending', name: 'conge_set_pending')]
    public function setPending(int $id, EntityManagerInterface $em): Response
    {
        $conge = $em->getRepository(Conge::class)->find($id);

        if (!$conge) {
            throw $this->createNotFoundException('Le congé n\'existe pas.');
        }

        $conge->setStatut('En attente');
        $em->flush();

        $this->addFlash('success', 'Le congé a été remis en attente.');
        return $this->redirectToRoute('conge');
    }
}
