<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case MEMBER = 'membro';
    case SEC = 'sec';
    case PRESBITERIO = 'presbiterio';
    case DIRETORIA = 'diretoria';

    // Método para obter os valores
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Método para obter os rótulos legíveis para os usuários
    public static function getLabels(): array
    {
        return [
            self::ADMIN->value => 'Administrador',
            self::SEC->value => 'Secretaria',
            self::MEMBER->value => 'Membro',
            self::PRESBITERIO->value => 'Presbitério',
            self::DIRETORIA->value => 'Diretoria'
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::MEMBER => 'Membro',
            self::SEC => 'Secretaria',
            self::PRESBITERIO => 'Presbitério',
            self::DIRETORIA => 'Diretoria',
        };
    }
}
