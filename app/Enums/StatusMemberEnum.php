<?php

namespace App\Enums;

enum StatusMemberEnum: string
{
    case ATIVO = 'ativo';
    case SIMPATIZANTE = 'simpatisante';
    case ENDERECO_DESATUALIZADO = 'endereco_desatualizado';
    case INATIVO = 'inativo';
    case EM_TRANSFERENCIA = 'em_transferencia';
    case DEPENDENTE = 'dependente';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::SIMPATIZANTE => 'Simpatisante',
            self::ENDERECO_DESATUALIZADO => 'Endereço desatualizado',
            self::INATIVO => 'Inativo',
            self::EM_TRANSFERENCIA => 'Em transferência',
            self::DEPENDENTE => 'Dependente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ATIVO => 'success',
            self::SIMPATIZANTE => 'warning',
            self::ENDERECO_DESATUALIZADO => 'danger',
            self::INATIVO => 'secondary',
            self::EM_TRANSFERENCIA => 'info',
            self::DEPENDENTE => 'primary',
        };
    }
    public function value(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::SIMPATIZANTE => 'Simpatisante',
            self::ENDERECO_DESATUALIZADO => 'Endereço desatualizado',
            self::INATIVO => 'Inativo',
            self::EM_TRANSFERENCIA => 'Em transferência',
            self::DEPENDENTE => 'Dependente',
        };
    }

}
