<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    #[Route('/user', name: 'user')]
    public function List(): Response
    {
        // Utiliser la méthode findAll() pour récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();

        // Passer les utilisateurs à la vue
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/conge/{email}', name: 'user_show_conge')]
    public function showConge($email): Response
    {
        $userConges = $this->userRepository->findCongeByEmail($email);

        // Passer les congés à la vue
        return $this->render('user/conge_user.html.twig', [
            'userConges' => $userConges,
        ]);
    }
}

