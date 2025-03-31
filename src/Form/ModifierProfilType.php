<?php

namespace App\Form;

use App\Entity\Pays;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ModifierProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', null, [
                'label' => 'Login / Email',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le login est obligatoire.']),
                ],
            ])
            ->add('password', null, [
                'label' => 'Mot de passe',
                'required' => false, // Le champ peut rester vide si l'utilisateur ne souhaite pas le modifier
            ])
            ->add('nom', null, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                ],
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                ],
            ])
            ->add('dateNaissance', null, [
                'label' => 'Date de naissance',
                'widget' => 'single_text', // Widget pour un champ de date simple
                'required' => false, // La date de naissance est facultative
            ])
            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nom', // Affiche le nom complet des pays
                'required' => false, // Le choix du pays est facultatif
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // Associe le formulaire à l'entité User
        ]);
    }
}