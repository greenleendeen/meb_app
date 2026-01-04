<?php

namespace App\Form;

use App\Entity\CompteRendu;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use App\Enum\DocumentType;



class CompteRenduType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextareaType::class, [
                'label' => 'Compte rendu',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Décrire l’intervention, les constats, les actions réalisées…',
                ],
            ])
            ->add('documents', CollectionType::class, [
                'entry_type' => DocumentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])

          //  ->add('description')
         //   ->add('dateCreation', null, [
          //      'widget' => 'single_text',
         //   ])
          //  ->add('technicien', EntityType::class, [
           //     'class' => User::class,
         //       'choice_label' => 'id',
          //  ])
          //  ->add('intervention', EntityType::class, [
          //      'class' => Intervention::class,
           //     'choice_label' => 'id',
          //  ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompteRendu::class,
        ]);
    }
}
