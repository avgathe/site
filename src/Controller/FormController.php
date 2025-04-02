<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreationCompteClientType;
use App\Form\ModifierProfilType;
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
    public function ajouterClientAction(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            $this->addFlash('info', 'Vous avez déja un compte.');
            return $this->redirectToRoute('accueil_index');
        }
        $user = new User();

        $form = $this->createForm(CreationCompteClientType::class, $user);

        $form->handleRequest($request);

        if ($user->getLogin() === $form->get('password')->getData()) {
            $this->addFlash('info', 'Le mot de passe ne peut pas être identique au login.');

            return $this->render('form/edit_client.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);

            $user->setRoles(['ROLE_CLIENT']);
            $user->setIsAdmin(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('info', 'Le client a été ajouté avec succès !');
            return $this->redirectToRoute('accueil_index');
        }

        return $this->render('form/edit_client.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/profil', name: '_edit_profil')]
    public function editProfileAction(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }

        $form = $this->createForm(ModifierProfilType::class, $user, [
            'validation_groups' => ['Default'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $user->setRoles($user->getRoles());
            $user->setIsAdmin($user->isAdmin());

            $entityManager->flush();

            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $this->addFlash('info', 'Votre profil a été mis à jour avec succès !');
                return $this->redirectToRoute('accueil_index');
            } else {
                $this->addFlash('info', 'Votre profil a été mis à jour avec succès !');
                return $this->redirectToRoute('produit_liste');
            }
        }


        return $this->render('form/edit_profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}