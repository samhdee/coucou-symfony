<?php
namespace App\Enum;

enum TimerStatus: string
{
    case Running = 'running';
    case Paused = 'paused';
    case Stopped = 'stopped';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Décompte',
            self::Paused => 'En pause',
            self::Stopped => 'Arrêté',
            self::Finished => 'Terminé',
        };
    }
}
