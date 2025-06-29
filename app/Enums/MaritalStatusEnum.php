<?php
namespace App\Enums;

enum MaritalStatusEnum: string
{
    case SINGLE = 'solteiro';
    case MARRIED = 'casado';
    case DIVORCED = 'divorciado';
    case WIDOWED = 'viuvo';
    case SEPARATED = 'separado';
    case COMMON_LAW = 'uniao_estavel';
    case OTHER = 'outro';

    public static function getValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Solteiro(a)',
            self::MARRIED => 'Casado(a)',
            self::DIVORCED => 'Divorciado(a)',
            self::WIDOWED => 'Viúvo(a)',
            self::SEPARATED => 'Separado(a)',
            self::COMMON_LAW => 'União Estável',
            self::OTHER => 'Outro',
        };
    }

    public function value(): string
    {
        return match ($this) {
            self::SINGLE => 'solteiro',
            self::MARRIED => 'casado',
            self::DIVORCED => 'divorciado',
            self::WIDOWED => 'viuvo',
            self::SEPARATED => 'separado',
            self::COMMON_LAW => 'uniao_estavel',
            self::OTHER => 'outro',
        };
    }
    public static function toArray(): array
    {
        return [
            self::SINGLE->value => self::SINGLE->label(),
            self::MARRIED->value => self::MARRIED->label(),
            self::DIVORCED->value => self::DIVORCED->label(),
            self::WIDOWED->value => self::WIDOWED->label(),
            self::SEPARATED->value => self::SEPARATED->label(),
            self::COMMON_LAW->value => self::COMMON_LAW->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
    public static function fromValue(string $value): self
    {
        return match ($value) {
            'solteiro' => self::SINGLE,
            'casado' => self::MARRIED,
            'divorciado' => self::DIVORCED,
            'viuvo' => self::WIDOWED,
            'separado' => self::SEPARATED,
            'uniao_estavel' => self::COMMON_LAW,
            'outro' => self::OTHER,
        };
    }

    public static function getIcons(): array
    {
        return [
            self::SINGLE->value => 'user',
            self::MARRIED->value => 'heart',
            self::DIVORCED->value => 'broken-heart',
            self::WIDOWED->value => 'cross',
            self::SEPARATED->value => 'handshake',
            self::COMMON_LAW->value => 'users',
            self::OTHER->value => 'question-circle',
        ];
    }
    public static function getIconColors(): array
    {
        return [
            self::SINGLE->value => 'text-blue-500',
            self::MARRIED->value => 'text-green-500',
            self::DIVORCED->value => 'text-red-500',
            self::WIDOWED->value => 'text-gray-500',
            self::SEPARATED->value => 'text-yellow-500',
            self::COMMON_LAW->value => 'text-purple-500',
            self::OTHER->value => 'text-gray-400',
        ];
    }

    public static function getIconSizes(): array
    {
        return [
            self::SINGLE->value => 'text-2xl',
            self::MARRIED->value => 'text-2xl',
            self::DIVORCED->value => 'text-2xl',
            self::WIDOWED->value => 'text-2xl',
            self::SEPARATED->value => 'text-2xl',
            self::COMMON_LAW->value => 'text-2xl',
            self::OTHER->value => 'text-2xl',
        ];
    }

    public static function getColors(): array
    {
        return [
            self::SINGLE->value => 'text-blue-500',
            self::MARRIED->value => 'text-green-500',
            self::DIVORCED->value => 'text-red-500',
            self::WIDOWED->value => 'text-gray-500',
            self::SEPARATED->value => 'text-yellow-500',
            self::COMMON_LAW->value => 'text-purple-500',
            self::OTHER->value => 'text-gray-400',
        ];
    }

    public static function getLabels(): array
    {
        return [
            self::SINGLE->value => self::SINGLE->label(),
            self::MARRIED->value => self::MARRIED->label(),
            self::DIVORCED->value => self::DIVORCED->label(),
            self::WIDOWED->value => self::WIDOWED->label(),
            self::SEPARATED->value => self::SEPARATED->label(),
            self::COMMON_LAW->value => self::COMMON_LAW->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
    public static function getValuesWithLabels(): array
    {
        return array_map(fn(self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }

    public static function getValuesWithIcons(): array
    {
        return array_map(fn(self $case) => ['value' => $case->value, 'icon' => self::getIcons()[$case->value]], self::cases());
    }
}
