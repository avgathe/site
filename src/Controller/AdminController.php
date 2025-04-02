<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin', name:'admin')]
class AdminController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/gestion-admins', name: '_gestion_admins')]
    public function gestionAdmins(UserRepository $userRepository): Response
    {
        $utilisateurs = $userRepository->findAll();

        return $this->render('admin/gestion_admins.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/promouvoir/{id}', name: '_promouvoir')]
    public function promouvoir(User $user, EntityManagerInterface $em): Response
    {
        $roles = $user->getRoles();

        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $em->flush();

            $this->addFlash('info', 'Utilisateur promu administrateur.');
        }

        return $this->redirectToRoute('admin_gestion_admins');
    }
    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/retrograder/{id}', name: '_retrograder')]
    public function retrograder(User $user, EntityManagerInterface $em): Response
    {
        $roles = array_filter($user->getRoles(), fn($role) => $role !== 'ROLE_ADMIN');
        $user->setRoles($roles);

        $em->flush();
        $this->addFlash('info', 'Administrateur rétrogradé en client.');

        return $this->redirectToRoute('admin_gestion_admins');
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/comptes', name: '_comptes')]
    public function listeUtilisateurs(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/comptes.html.twig', [
            'users' => $users,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/utilisateur/{id}/supprimer', name: '_utilisateur_supprimer')]
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