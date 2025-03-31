<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;


class AdminController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/gestion-admins', name: 'admin_gestion_admins')]
    public function gestionAdmins(UserRepository $userRepository): Response
    {
        $utilisateurs = $userRepository->findAll();

        return $this->render('admin/gestion_admins.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/promouvoir/{id}', name: 'admin_promouvoir')]
    public function promouvoir(User $user, EntityManagerInterface $em): Response
    {
        $roles = $user->getRoles();

        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $em->flush();

            $this->addFlash('success', 'Utilisateur promu administrateur.');
        }

        return $this->redirectToRoute('admin_gestion_admins');
    }
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/retrograder/{id}', name: 'admin_retrograder')]
    public function retrograder(User $user, EntityManagerInterface $em): Response
    {
        $roles = array_filter($user->getRoles(), fn($role) => $role !== 'ROLE_ADMIN');
        $user->setRoles($roles);

        $em->flush();
        $this->addFlash('success', 'Administrateur rétrogradé en client.');

        return $this->redirectToRoute('admin_gestion_admins');
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/comptes', name: 'admin_comptes')]
    public function listeUtilisateurs(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/comptes.html.twig', [
            'users' => $users,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/utilisateur/{id}/supprimer', name: 'admin_utilisateur_supprimer')]
    public function supprimerUtilisateur(User $user, EntityManagerInterface $em): Response
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $this->addFlash('danger', 'Impossible de supprimer un super administrateur.');
            return $this->redirectToRoute('admin_comptes');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_comptes');
    }



}