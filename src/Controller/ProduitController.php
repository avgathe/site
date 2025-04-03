<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\Panier;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProduitRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class ProduitController extends AbstractController
{

    #[Route('/produit/liste', name: 'produit_liste')]
    #[isGranted('ROLE_CLIENT')]
    public function listeAction(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('produit/ajout', name: 'produit_ajout')]
    #[IsGranted('ROLE_ADMIN')]
    public function ajouterAction(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        /** @var \App\Entity\User $user */
        if (!$user) {
            $this->addFlash('info', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login'); // ou 'accueil_index' si tu préfères
        }

        if (!$user->isAdmin()) {
            $this->addFlash('info', 'Seuls les administrateurs peuvent ajouter un produit.');
            return $this->redirectToRoute('accueil_index');
        }


        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($produit->getPrix() < 0) {
            $this->addFlash('info', 'Le prix ne peut pas être négatif.');
            return $this->render('produit/ajouter.html.twig', [
                'form' => $form->createView(),
            ]);
        }


        if ($produit->getStock() < 0) {
            $this->addFlash('info', 'Le stock ne peut pas être négatif.');
            return $this->render('produit/ajouter.html.twig', [
                'form' => $form->createView(),
            ]);
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            $this->addFlash('info', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('accueil_index'); // ou autre route
        }

        return $this->render('produit/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
