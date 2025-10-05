<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Intervention;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          //  ->add('title', TextType::class, ['label' => 'Titre', ])
            ->add('clientNom', TextType::class, ['required' => false])
            ->add('reference', TextType::class, ['required' => false])
            ->add('adresse', TextType::class, ['required' => false])
            ->add('demande', TextareaType::class, ['required' => false])
            ->add('detail', TextareaType::class, ['required' => false]);
           // ->add('title', TextType::class, ['label' => 'Titre'])
          //  ->add('date', DateTimeType::class, [
          //      'label' => 'Date',
          //      'widget' => 'single_text',
        //    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Intervention::class,
        ]);
    }
}
