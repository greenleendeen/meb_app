<?php

namespace App\Form;

use App\Entity\Document;
use App\Enum\DocumentType as DocumentEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filename', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false, // pas directement lié à l'entité
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de document',
                'choices' => [
                    'Bon de commande' => DocumentEnum::BON_COMMANDE,
                    'Devis' => DocumentEnum::DEVIS,
                    'Photo' => DocumentEnum::PHOTO,
                    'Compte rendu' => DocumentEnum::COMPTE_RENDU,
                    'Facture' => DocumentEnum::FACTURE,
                ],
                // IMPORTANT : symfony doit mapper l'objet enum
    'choice_value' => fn(?DocumentEnum $choice) => $choice?->value,
    'choice_label' => fn(?DocumentEnum $choice) => $choice?->value,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}