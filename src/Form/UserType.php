<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('identifiant', EmailType::class)
            ->add('password',  PasswordType::class)
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nom', // le champ de l’entité Role que je veux afficher. affichera "ROLE_ADMIN", "ROLE_SUPER_ADMIN", etc.
                'multiple' => true,     // un user peut avoir plusieurs rôles
                'expanded' => true,   // cases à cocher au lieu d’un <select>
                'by_reference' => false, // important pour ManyToMany
                'property_path' => 'roles', // important
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
