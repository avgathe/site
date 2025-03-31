<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

#[IsGranted('ROLE_SUPER_ADMIN')]
class AdminController extends AbstractController
{

    #[Route('/admin/gestion-admins', name: 'admin_gestion_admins')]
    public function gestionAdmins(UserRepository $userRepository): Response
    {
        $utilisateurs = $userRepository->findAll();

        return $this->render('admin/gestion_admins.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

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

    #[Route('/admin/retrograder/{id}', name: 'admin_retrograder')]
    public function retrograder(User $user, EntityManagerInterface $em): Response
    {
        $roles = array_filter($user->getRoles(), fn($role) => $role !== 'ROLE_ADMIN');
        $user->setRoles($roles);

        $em->flush();
        $this->addFlash('success', 'Administrateur rétrogradé en client.');

        return $this->redirectToRoute('admin_gestion_admins');
    }


}