<?php

namespace App\Service;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
class PanierService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function compterArticlesDansPanier(int $userId): int
    {
        $user = $this->em->getRepository(User::class)->find($userId);

        if (!$user) {
            return 0;
        }

        $paniers = $user->getPaniers(); // relation OneToMany
        $total = 0;

        foreach ($paniers as $panier) {
            $total += $panier->getQuantite(); // méthode dans l’entité Panier
        }

        return $total;
    }
}