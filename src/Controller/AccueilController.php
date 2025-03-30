<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil_index')]
    public function index(Security $security): Response
    {

        $user = $security->getUser();
        $roles = $user ? $user->getRoles() : ['ROLE_VISITEUR'];

        return $this->render('index.html.twig', [
            'roles' => $roles,
        ]);
    }


    public function menuAction(): Response
    {
        $args = array(
        );
        return $this->render('Layouts/_menu.html.twig', $args);
    }

}
