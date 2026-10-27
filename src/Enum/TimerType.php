<?php
namespace App\Enum;

enum TimerType: string
{
    case Task = 'task';
    case Pause = 'pause';

    // Optionnel : un libellé lisible pour l'affichage
    public function label(): string
    {
        return match ($this) {
            self::Task => 'Tâche',
            self::Pause => 'Pause',
        };
    }
}
