<?php
namespace App\Enum;

enum TimerPresetAdded: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';

    // Optionnel : un libellé lisible pour l'affichage
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Paid => 'Payée',
            self::Shipped => 'Expédiée',
            self::Cancelled => 'Annulée',
        };
    }
}