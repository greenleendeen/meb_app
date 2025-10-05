<?php

namespace App\Form;

use App\Entity\Document;
use App\Enum\DocumentType as DocumentEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $builder
            // Champ pour le fichier PDF / PJ
            ->add('filename', FileType::class, [
                'label' => 'Fichier PDF ou image',
                'mapped' => false, // ⚠️ Ne pas mapper pour éviter les erreurs d’upload
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de document',
                'choices' => DocumentEnum::cases(),
                'choice_value' => fn(?DocumentEnum $choice) => $choice?->value,
                'choice_label' => fn(?DocumentEnum $choice) => $choice?->value,
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}