<?php
namespace App\Enum;

enum TimerPresetAdded: string
{
    case NewTimer = 'new_timer';
    case Other = 'other';

    // Optionnel : un libellé lisible pour l'affichage
    public function label(): string
    {
        return match ($this) {
            self::NewTimer => 'Création de timer',
            self::Other => 'Autre',
        };
    }
}
