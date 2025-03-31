<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreationCompteClientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/form', name:'form')]
class FormController extends AbstractController
{
    #[Route('/edit/client', name: '_edit_client')]
    public function ajouterClient(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Instance vide de l'entité User
        $user = new User();

        // Création du formulaire
        $form = $this->createForm(CreationCompteClientType::class, $user);

        // Traitement de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe utilisateur
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Contraintes supplémentaires
            $user->setRoles(['ROLE_CLIENT']);
            $user->setIsAdmin(false);

            // Sauvegarde du client dans la BD
            $entityManager->persist($user);
            $entityManager->flush();

            // Message flash et redirection
            $this->addFlash('info', 'Le client a été ajouté avec succès !');
            return $this->redirectToRoute('Accueil/index.html.twig');
        }

        // Affichage du formulaire
        return $this->render('form/user_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil/edit', name: '_edit_profil')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Récupérer l'utilisateur authentifié
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }

        // Créer le formulaire avec les données pré-remplies de l'utilisateur
        $form = $this->createForm(CreationCompteClientType::class, $user, [
            'validation_groups' => ['Default'], // Groupe de validation pour vérifier l'unicité du login
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un nouveau mot de passe a été saisi
            if ($password = $user->getPassword()) {
                // Hacher le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }

            // S'assurer que les rôles et isAdmin ne sont jamais modifiés
            $user->setRoles($user->getRoles()); // Conserver les rôles tels qu'ils sont
            $user->setIsAdmin($user->isAdmin()); // Conserver la valeur isAdmin

            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Ajouter un message flash en fonction du rôle
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
                return $this->redirectToRoute('accueil_index'); // Page d'accueil pour les super-administrateurs
            } else {
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
                return $this->redirectToRoute('accueil_index'); // Page de listing pour les autres utilisateurs
            }
        }

        // Afficher la page du formulaire
        return $this->render('form/edit_profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}