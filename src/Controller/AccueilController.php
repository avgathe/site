<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil_index')]
    public function index(Security $security): Response
    {

        $user = $security->getUser();
        $roles = $user ? $user->getRoles() : ['ROLE_VISITEUR'];

        return $this->render('Accueil/index.html.twig', [
            'roles' => $roles,
        ]);
    }


    public function menuAction(Security $security): Response
    {
        // Récupération de l'utilisateur via le composant Security
        $user = $security->getUser();
        $articleCount = 0;

        // Vérifier si un utilisateur est connecté
        if ($user && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            // Récupérer le panier depuis l'utilisateur ou un service centralisé
            $basket = $user->getPaniers(); // Méthode personnalisée qui retourne le panier
            $articleCount = array_sum(array_map(fn($panier) => $panier->getQuantite(), $basket->toArray()));

        }

        return $this->render('Layouts/_menu.html.twig', [
            'user' => $user,
            'articleCount' => $articleCount,
        ]);
    }


}
