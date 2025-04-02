<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier', name:'panier')]
class PanierController extends AbstractController
{
    #[isGranted('ROLE_CLIENT')]
    #[Route('', name: '_index')]
    public function indexAction(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('info', 'Veuillez vous connecter pour voir votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $paniers = $user->getPaniers();

        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers
        ]);
    }

    #[Route('/retirer/{id}', name: '_retirer')]
    public function retirerAction(Panier $panier, EntityManagerInterface $em, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user || $panier->getClient() !== $user) {
            $this->addFlash('info', 'Action non autorisée.');
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

        $this->addFlash('info', 'Produit retiré du panier.');
        return $this->redirectToRoute('panier_index');
    }


    #[Route('/vider', name: '_vider')]
    public function viderAction(Security $security, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('info', 'Veuillez vous connecter pour vider le panier.');
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

        $this->addFlash('info', 'Le panier a été vidé.');
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/modifier', name: '_modifier', methods: ['POST'])]
    public function modifierAction(
        Request $request,
        EntityManagerInterface $em,
        ProduitRepository $produitRepository,
        Security $security
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('info', 'Connexion requise.');
            return $this->redirectToRoute('app_login');
        }

        $produitId = $request->request->get('produit_id');
        $quantiteModif = (int) $request->request->get('quantite');
        $produit = $produitRepository->find($produitId);

        if (!$produitId || !is_numeric($produitId)) {
            $this->addFlash('info', 'Erreur : identifiant du produit manquant ou invalide.');
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
                $this->addFlash('info', 'Stock insuffisant.');
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
        $this->addFlash('info', 'Panier mis à jour.');
        return $this->redirectToRoute('produit_liste');
    }

    #[Route('/commander', name: '_commander')]
    public function commanderAction(Security $security, EntityManagerInterface $em): Response
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

        $this->addFlash('info', 'Commande validée ! Merci pour votre achat.');
        return $this->redirectToRoute('accueil_index');
    }



}