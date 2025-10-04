<?php

namespace App\Enum;

enum DocumentType: string
{
    case BON_COMMANDE = 'bon_commande';
    case DEVIS = 'devis';
    case PHOTO = 'photo';
    case COMPTE_RENDU = 'compte_rendu';
    case FACTURE = 'facture';
}