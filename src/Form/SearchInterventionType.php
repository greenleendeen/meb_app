<?php

namespace App\Form;

use App\Enum\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'required' => false,
                'label' => 'Référence',
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'label' => 'Adresse contient',
            ])
            ->add('technicien', ChoiceType::class, [
                'required' => false,
                'label' => 'Technicien',
                'choices' => $options['techniciens'], // envoyé par le contrôleur
                'choice_label' => fn($tech) => $tech->getNom(),
                'choice_value' => 'id',
                'placeholder' => 'Tous les techniciens',
            ])
            ->add('typeDocument', ChoiceType::class, [
                'label' => 'Type de document',
                'required' => false,
                'choices' => array_combine(
    array_map(fn($e) => $e->getTypeLabel(), DocumentType::cases()),
    DocumentType::cases()
                ),
                'placeholder' => 'Tous les types',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // pour passer la liste des techniciens depuis ton controller
            'techniciens' => [],
            'csrf_protection' => false,
        ]);
    }
}
