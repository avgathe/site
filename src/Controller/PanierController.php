<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier_index')]
    public function index(Security $security): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour voir votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $paniers = $user->getPaniers();

        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers
        ]);
    }

    #[Route('/panier/retirer/{id}', name: 'panier_retirer')]
    public function retirer(Panier $panier, EntityManagerInterface $em, Security $security): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user || $panier->getClient() !== $user) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('panier_index');
        }

        $em->remove($panier);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('panier_index');
    }


}