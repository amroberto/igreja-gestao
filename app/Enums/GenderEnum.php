<?php

namespace App\Enums;

enum GenderEnum: string
{
    case Male = 'masculino';
    case Female = 'feminino';
    case Other = 'outro';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

public function label(): string
{
    return match ($this->value) {
        'masculino' => 'Masculino',
        'feminino' => 'Feminino',
        'outro' => 'Outro',
        default => '-',
    };
}

    public function getLabels(): array
    {
        return [
            self::Male->value => self::Male->label(),
            self::Female->value => self::Female->label(),
            self::Other->value => self::Other->label(),
        ];
    }
}
