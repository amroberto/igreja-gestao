<?php

namespace App\Enums;

enum StatusPaymentEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
    case Canceled = 'canceled';

    // Método para obter os valores
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    public function getLabels(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Confirmed => 'Confirmado',
            self::Failed => 'Falhou',
            self::Canceled => 'Cancelado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Confirmed => 'Confirmado',
            self::Failed => 'Falhou',
            self::Canceled => 'Cancelado',
        };
    }
    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-500',
            self::Confirmed => 'bg-green-500',
            self::Failed => 'bg-red-500',
            self::Canceled => 'bg-gray-500',
        };
    }
}
