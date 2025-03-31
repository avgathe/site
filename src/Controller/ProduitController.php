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



final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    #[Route('/produits', name: 'produit_liste')]
    public function liste(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter', methods: ['POST'])]
    public function ajouterAuPanier(
        int $id,
        Request $request,
        ProduitRepository $produitRepository,
        Security $security,
        EntityManagerInterface $em
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        $quantite = max(1, (int) $request->request->get('quantite'));

        if ($produit->getStock() < $quantite) {
            $this->addFlash('error', 'Stock insuffisant pour cette quantité.');
            return $this->redirectToRoute('produit_liste');
        }

        // Ajouter au panier
        $panier = $user->getPaniers()->filter(fn($p) => $p->getProduit() === $produit)->first() ?: null;

        if ($panier) {
            $panier->setQuantite($panier->getQuantite() + $quantite);
        } else {
            $panier = new Panier();
            $panier->setClient($user);
            $panier->setProduit($produit);
            $panier->setQuantite($quantite);
            $em->persist($panier);
        }

        // Réduire le stock
        $produit->setStock($produit->getStock() - $quantite);

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier.');
        return $this->redirectToRoute('produit_liste');
    }



    #[Route('/admin/produit/ajout', name: 'produit_ajout')]
    public function ajouter(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        /** @var \App\Entity\User $user */
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login'); // ou 'accueil_index' si tu préfères
        }

        if (!$user->isAdmin()) {
            $this->addFlash('error', 'Seuls les administrateurs peuvent ajouter un produit.');
            return $this->redirectToRoute('accueil_index');
        }


        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($produit->getPrix() < 0) {
            $this->addFlash('danger', 'Le prix ne peut pas être négatif.');
            return $this->render('produit/ajouter.html.twig', [
                'form' => $form->createView(),
            ]);
        }


        if ($produit->getStock() < 0) {
            $this->addFlash('danger', 'Le stock ne peut pas être négatif.');
            return $this->render('produit/ajouter.html.twig', [
                'form' => $form->createView(),
            ]);
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('accueil_index'); // ou autre route
        }

        return $this->render('produit/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
