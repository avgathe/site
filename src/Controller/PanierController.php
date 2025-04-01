<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class PanierController extends AbstractController
{
    #[isGranted('ROLE_CLIENT')]
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

        // Récupérer la quantité retirée
        $quantite = $panier->getQuantite();

        // Récupérer le produit concerné
        $produit = $panier->getProduit();

        // Réajouter la quantité au stock
        $produit->setStock($produit->getStock() + $quantite);

        // Supprimer la ligne du panier
        $em->remove($panier);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('panier_index');
    }


    #[Route('/panier/vider', name: 'panier_vider')]
    public function vider(Security $security, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour vider le panier.');
            return $this->redirectToRoute('app_login');
        }

        foreach ($user->getPaniers() as $ligne) {
            // Restaurer la quantité dans le stock du produit
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();

            $produit->setStock($produit->getStock() + $quantite);

            // Supprimer la ligne du panier
            $em->remove($ligne);
        }

        $em->flush();

        $this->addFlash('success', 'Le panier a été vidé.');
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/modifier', name: 'panier_modifier', methods: ['POST'])]
    public function modifier(
        Request $request,
        EntityManagerInterface $em,
        ProduitRepository $produitRepository,
        Security $security
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Connexion requise.');
            return $this->redirectToRoute('app_login');
        }

        $produitId = $request->request->get('produit_id');
        $quantiteModif = (int) $request->request->get('quantite');
        $produit = $produitRepository->find($produitId);

        if (!$produitId || !is_numeric($produitId)) {
            $this->addFlash('error', 'Erreur : identifiant du produit manquant ou invalide.');
            return $this->redirectToRoute('produit_liste');
        }


        $panier = $user->getPaniers()->filter(fn($p) => $p->getProduit()->getId() === $produit->getId())->first() ?: null;

        // Retirer du panier (quantité négative)
        if ($quantiteModif < 0) {
            if ($panier) {
                $nouvelleQuantite = $panier->getQuantite() + $quantiteModif;

                if ($nouvelleQuantite <= 0) {
                    // Supprimer la ligne si la quantité tombe à 0
                    $em->remove($panier);
                } else {
                    $panier->setQuantite($nouvelleQuantite);
                }

                // Restaurer la quantité retirée au stock
                $produit->setStock($produit->getStock() - $quantiteModif); // -(-x) = +x
            }
        }

        // Ajouter au panier
        elseif ($quantiteModif > 0) {
            if ($produit->getStock() < $quantiteModif) {
                $this->addFlash('error', 'Stock insuffisant.');
                return $this->redirectToRoute('produit_liste');
            }

            if ($panier) {
                $panier->setQuantite($panier->getQuantite() + $quantiteModif);
            } else {
                $panier = new Panier();
                $panier->setClient($user);
                $panier->setProduit($produit);
                $panier->setQuantite($quantiteModif);
                $em->persist($panier);
            }

            $produit->setStock($produit->getStock() - $quantiteModif);
        }

        $em->flush();
        $this->addFlash('success', 'Panier mis à jour.');
        return $this->redirectToRoute('produit_liste');
    }

    #[Route('/panier/commander', name: 'panier_commander')]
    public function commander(Security $security, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour commander.');
            return $this->redirectToRoute('app_login');
        }

        $paniers = $user->getPaniers();

        foreach ($paniers as $panier) {

            //nul besoin de vérifier la quantité en stock car la sélection du nombre d'articles empêche de commander plus qu'il n'y a d'articles en stock
            // Suppression du panier
            $em->remove($panier);
        }

        $em->flush();

        $this->addFlash('success', 'Commande validée ! Merci pour votre achat.');
        return $this->redirectToRoute('accueil_index');
    }



}