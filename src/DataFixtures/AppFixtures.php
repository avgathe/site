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
        $produit1->setLibelle('Lit à pois rouge');
        $produit1->setPrix(599.99);
        $produit1->setStock(16);
        $produit1->addPays($france);
        $produit1->addPays($espagne);

        $produit2 = new Produit();
        $produit2->setLibelle('Fauteuil pois rouge');
        $produit2->setPrix(479.99);
        $produit2->setStock(5);
        $produit2->addPays($france);
        $produit2->addPays($japon);

        $produit3 = new Produit();
        $produit3->setLibelle('Commode à pois rouge');
        $produit3->setPrix(599.99);
        $produit3->setStock(10);
        $produit3->addPays($espagne);

        $produit4 = new Produit();
        $produit4->setLibelle('canapé à pois rouge');
        $produit4->setPrix(699.99);
        $produit4->setStock(11);
        $produit4->addPays($japon);

        $produit5 = new Produit();
        $produit5->setLibelle('Armoire à pois rouge');
        $produit5->setPrix(1199.99);
        $produit5->setStock(22);
        $produit5->addPays($france);
        $produit5->addPays($espagne);

        $produit6 = new Produit();
        $produit6->setLibelle('Lit moderne');
        $produit6->setPrix(2319.99);
        $produit6->setStock(7);
        $produit6->addPays($france);

        $produit7 = new Produit();
        $produit7->setLibelle('Commode moderne');
        $produit7->setPrix(2249.99);
        $produit7->setStock(12);
        $produit7->addPays($japon);

        $produit8 = new Produit();
        $produit8->setLibelle('Chaise moderne');
        $produit8->setPrix(1499.99);
        $produit8->setStock(15);
        $produit8->addPays($espagne);
        $produit8->addPays($france);

        $produit9 = new Produit();
        $produit9->setLibelle('Armoire moderne');
        $produit9->setPrix(2559.99);
        $produit9->setStock(4);
        $produit9->addPays($japon);

        $produit10 = new Produit();
        $produit10->setLibelle('Canapé moderne');
        $produit10->setPrix(2619.99);
        $produit10->setStock(6);
        $produit10->addPays($france);
        $produit10->addPays($espagne);

        $manager->persist($produit1);
        $manager->persist($produit2);
        $manager->persist($produit3);
        $manager->persist($produit4);
        $manager->persist($produit5);
        $manager->persist($produit6);
        $manager->persist($produit7);
        $manager->persist($produit8);
        $manager->persist($produit9);
        $manager->persist($produit10);


        // --- Flush final ---
        $manager->flush();
    }
}
