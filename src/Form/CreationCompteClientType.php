<?php

namespace App\Form;

use App\Entity\Pays;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreationCompteClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login') // Login utilisateur
            ->add('password') // Mot de passe non haché
            ->add('nom') // Nom
            ->add('prenom') // Prénom
            ->add('dateNaissance', null, [ // Date de naissance avec un widget simple
                'widget' => 'single_text'
            ])
            ->add('pays', EntityType::class, [ // Pays est facultatif
                'class' => Pays::class,
                'choice_label' => 'nom', // Affiche le nom du pays
                'required' => false    // Champ non obligatoire
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // La classe liée au formulaire
        ]);
    }
}