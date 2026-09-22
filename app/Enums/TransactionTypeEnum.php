<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case Credit = 'C';
    case Debit = 'D';

    public function label(): string
    {
        return match ($this) {
            self::Credit => 'Crédito',
            self::Debit => 'Débito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Credit => 'green',
            self::Debit => 'red',
        };
    }
}
