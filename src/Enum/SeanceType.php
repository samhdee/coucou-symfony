<?php
namespace App\Enum;

enum SeanceType: string
{
    case SevenByNine = 'seven-by-nine';
    case Session = 'session';

    // Optionnel : un libellé lisible pour l'affichage
    public function label(): string
    {
        return match ($this) {
            self::SevenByNine => '7x9',
            self::Session => 'Session',
        };
    }
}
