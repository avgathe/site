<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Pays;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --- Création de quelques pays ---
        $france = new Pays();
        $france->setNom('France');
        $france->setCode('FR');

        $espagne = new Pays();
        $espagne->setNom('Espagne');
        $espagne->setCode('ES');

        $japon = new Pays();
        $japon->setNom('Japon');
        $japon->setCode('JP');

        $manager->persist($france);
        $manager->persist($espagne);
        $manager->persist($japon);

        // --- Création des 4 utilisateurs requis ---
        $users = [
            ['login' => 'sadmin', 'password' => 'nimdas', 'role' => ['ROLE_SUPER_ADMIN'], 'nom' => 'Admin', 'prenom' => 'Super', 'admin' => false],
            ['login' => 'gilles', 'password' => 'sellig', 'role' => ['ROLE_ADMIN','ROLE_CLIENT'], 'nom' => 'Subrenat', 'prenom' => 'Gilles', 'admin' => true],
            ['login' => 'rita', 'password' => 'atir', 'role' => ['ROLE_CLIENT'], 'nom' => 'Zrour', 'prenom' => 'Rita', 'admin' => false],
            ['login' => 'boumediene', 'password' => 'eneidemuob', 'role' => ['ROLE_CLIENT'], 'nom' => 'Saidi', 'prenom' => 'Boumediene', 'admin' => false],
        ];

        foreach ($users as $u) {
            $user = new User();
            $user->setLogin($u['login']);
            $user->setNom($u['nom']);
            $user->setPrenom($u['prenom']);
            $user->setIsAdmin($u['admin']);
            $user->setDateNaissance(new \DateTime('1990-01-01')); // fixe pour les tests
            $user->setRoles($u['role']);
            $user->setPassword($this->hasher->hashPassword($user, $u['password']));
            // On associe tous les utilisateurs à la France par défaut
            $user->setPays($france);
            $manager->persist($user);
        }

        // --- Création de quelques produits ---
        $produit1 = new Produit();
        $produit1->setLibelle('Banane');
        $produit1->setPrix(1.99);
        $produit1->setStock(20);
        $produit1->addPays($france);
        $produit1->addPays($espagne);

        $produit2 = new Produit();
        $produit2->setLibelle('Cerise');
        $produit2->setPrix(2.49);
        $produit2->setStock(15);
        $produit2->addPays($france);
        $produit2->addPays($japon);

        $manager->persist($produit1);
        $manager->persist($produit2);

        // --- Flush final ---
        $manager->flush();
    }
}
