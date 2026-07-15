<?php

namespace App\Enum;

enum StatutCommande: string
{
    case EN_ATTENTE = 'en_attente';
    case PAYEE = 'payee';
    case ANNULEE = 'annulee';
}
