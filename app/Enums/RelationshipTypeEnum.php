<?php

namespace App\Enums;

enum RelationshipTypeEnum: string
{
    case TITULAR = 'titular';
    case PAI = 'pai';
    case MAE = 'mãe';
    case ESPOSA = 'esposa';
    case FILHO = 'filho';
    case PARENTE = 'parente';

    public function label(): string
    {
        return match($this) {
            self::TITULAR => 'Titular',
            self::PAI => 'Pai',
            self::MAE => 'Mãe',
            self::ESPOSA => 'Esposa',
            self::FILHO => 'Filho',
            self::PARENTE => 'Parente',
        };
    } 

    public function inverse(): ?self
    {
        return match($this) {
            self::FILHO => self::PAI,
            self::PAI => self::FILHO,
            self::MAE => self::FILHO,
            self::ESPOSA => self::PAI,
            self::PARENTE => self::PARENTE,
            default => null,
        };
    }
}