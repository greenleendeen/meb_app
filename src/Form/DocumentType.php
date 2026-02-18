<?php

/**
 * Form Type: DocumentType
 * ---------------------------------------------------------------------
 * Ce formulaire permet de créer ou modifier un document.
 * Champs :
 *  - filename: le nom du fichier
 *  - type : le type de fichier: commande, devis, facture etc
 * Utilisation :
 *  - Utilisé dans le contrôleur DocumentController (méthodes new/edit/show/ upload)
 *  - Relié à l'entité App\Entity\Document
 * Objectif :
 *  Le telechargement, la saisie et la mise à jour des données des documents attaches à des missions traitées dans l'appli.
 */

namespace App\Form;

use App\Entity\Document;
use App\Enum\DocumentType as DocumentEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;


class DocumentType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('file', FileType::class, [
                'label' => 'Fichier PDF ou image',
                'required' => false, // IMPORTANT
                'mapped' => true //false,
            ])

            ->add('type', EnumType::class, [
                'class' => DocumentEnum::class,
                'label' => 'Type de document',
                'placeholder' => 'Choisir le type du document',
                'choice_label' => fn(DocumentEnum $e) => $e->label(),
            ]);

        // ->add('type', ChoiceType::class, [
        //    'label' => 'Type de document',
        // 'choices' => array_combine(
        //      array_map(fn($e) => $e->value, DocumentEnum::cases()),
        //     DocumentEnum::cases()
        // ),
        //        fn (DocumentEnum $e) => $e->value,
        //   'placeholder' => 'Choisir le type du document',
        //  'required' => true,
        // 'choice_label' => fn(DocumentEnum $e) => $e->value,
        // 'required' => true,
        //    ]);
        //   ->add('extractedText', TextareaType::class, [
        //       'label' => 'Texte extrait du PDF',
        //       'required' => false,
        //       'attr' => ['rows' => 10],
        //    ]);

        // if ($isEdit) {
        //     $builder->add('filename', TextType::class, [
        //         'label' => 'Nom du fichier',
        //        'required' => false,
        //     ]);
        //  }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'is_edit' => false,
        ]);
    }
}
