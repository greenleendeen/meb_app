<?php
/**
 * Form Type: DocumentType
 * ---------------------------------------------------------------------
 * Ce formulaire permet de créer ou modifier un document.
 *
 * Champs :
 *  - filename: le nom du fichier
 *  - type : le type de fichier: commande, devis, facture etc
 *
 * Utilisation :
 *  - Utilisé dans le contrôleur DocumentController (méthodes new/edit/show/ upload)
 *  - Relié à l'entité App\Entity\Document
 *
 * Objectif :
 *  Le telechargement, la saisie et la mise à jour des données des documents attaches à des missions traitées dans l'appli.

 */
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