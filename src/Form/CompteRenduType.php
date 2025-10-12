<?php

namespace App\Form;

use App\Entity\CompteRendu;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompteRenduType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('dateCreation', null, [
                'widget' => 'single_text',
            ])
            ->add('technicien', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('intervention', EntityType::class, [
                'class' => Intervention::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompteRendu::class,
        ]);
    }
}
