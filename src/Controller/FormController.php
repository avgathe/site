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
    #[Route('/edit-client', name: '_edit_client')]
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
}