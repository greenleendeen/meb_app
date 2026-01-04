<?php

namespace App\Enum;

enum DocumentType: string {
    case DEVIS = 'devis';
    case FACTURE = 'facture';
    case BON_COMMANDE = 'bon_commande';
    case PHOTO = 'photo';
    case COMPTE_RENDU = 'compte_rendu';

    public function getTypeLabel(): string
    {
        return match($this) {
            self::DEVIS => 'Devis',
            self::FACTURE => 'Facture',
            self::BON_COMMANDE => 'Bon de commande',
                        self::PHOTO => 'Photo',
            self::COMPTE_RENDU => 'Compte rendu',
        };
    }
}