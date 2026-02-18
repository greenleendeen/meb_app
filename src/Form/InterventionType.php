<?php

/**
 * Form Type: InterventionType
 * ---------------------------------------------------------------------
 * Ce formulaire permet de créer ou modifier une intervention.
 *
 * Champs :
 *  - clientNom : sélection du client concerné
 *  - référence : Numéro de la commande 
 *  - adresse: l'adresse de l'intervention
 *  - demande : la demande du client 
 *  - details : informations complémentaires
 *  - compteRendu : champ texte pour le compte rendu du technicien
 *
 * Utilisation :
 *  - Utilisé dans le contrôleur InterventionController (méthodes new/edit/show)
 *  - Relié à l'entité App\Entity\Intervention
 *
 * Objectif :
 *  Faciliter la saisie et la mise à jour des données d'intervention.

 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Intervention;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType; // <-- IMPORTANT
use App\Form\DocumentType; // importer le DocumentType 
use Symfony\Component\Form\Extension\Core\Type\FileType;


use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;


class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];
        $builder

            //  ->add('title', TextType::class, ['label' => 'Titre', ])
            ->add('clientNom', TextType::class, ['required' => false])
            ->add('reference', TextType::class, ['required' => false])
            ->add('adresse', TextType::class, ['required' => false])
            ->add('demande', TextareaType::class, ['required' => false])
            ->add('detail', TextareaType::class, ['required' => false])

            ->add('documents', CollectionType::class, [
                'entry_type' => DocumentType::class,
              //  'entry_options' => ['is_edit' => $isEdit],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])

            
            // j'ajoute des nouveaux documents 
          //    ->add('newDocuments', FileType::class, [
           //       'mapped' => false,
           //        'multiple' => true,
           //        'required' => false,
           //        'label' => 'Ajouter de nouveaux documents'
             //  ])
             
            //j'ajoute les champs dans le formulaire pour les testes et pour la suite
            ->add('dateIntervention', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date intervention',
            ])
            ->add('heureDebut', TimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Heure de début',
            ])
            ->add('heureFin', TimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Heure de fin',
            ])
            ->add('technicien', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'label' => 'Technicien',
                'required' => false,
            ]);



        // ->add('title', TextType::class, ['label' => 'Titre'])
        //  ->add('date', DateTimeType::class, [
        //      'label' => 'Date',
        //      'widget' => 'single_text',
        //    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'is_edit' => false,
        ]);
    }
}
