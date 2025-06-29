<?php

namespace App\Enums;

enum DeliveryMethodEnum: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';
    case CORREIO = 'correio';
    case ONLINE = 'online';

    public static function getValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    public function value(): string
    {
        return match ($this) {
            self::EMAIL => 'E-mail',
            self::SMS => 'SMS',
            self::WHATSAPP => 'WhatsApp',
            self::CORREIO => 'Correio',
            self::ONLINE => 'On-line',
        };
    }
    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::WHATSAPP => 'WhatsApp',
            self::CORREIO => 'Correio',
            self::ONLINE => 'Online',
        };
    }
    public function icon(): string
    {
        return match ($this) {
            self::EMAIL => 'envelope',
            self::SMS => 'mobile-alt',
            self::WHATSAPP => 'whatsapp',
            self::CORREIO => 'mail-bulk',
            self::ONLINE => 'laptop-code',
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::EMAIL => 'text-blue-500',
            self::SMS => 'text-green-500',
            self::WHATSAPP => 'text-green-600',
            self::CORREIO => 'text-red-500',
            self::ONLINE => 'text-gray-500',
        };
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::EMAIL => 'text-blue-500',
            self::SMS => 'text-green-500',
            self::WHATSAPP => 'text-green-600',
            self::CORREIO => 'text-red-500',
            self::ONLINE => 'text-gray-500',
        };
    }

    public function iconSize(): string
    {
        return match ($this) {
            self::EMAIL => 'text-2xl',
            self::SMS => 'text-2xl',
            self::WHATSAPP => 'text-2xl',
            self::CORREIO => 'text-2xl',
            self::ONLINE => 'text-2xl',
        };
    }

    public function iconClass(): string
    {
        return match ($this) {
            self::EMAIL => 'fa-solid fa-envelope',
            self::SMS => 'fa-solid fa-mobile-alt',
            self::WHATSAPP => 'fa-brands fa-whatsapp',
            self::CORREIO => 'fa-solid fa-mail-bulk',
            self::ONLINE => 'fa-solid fa-laptop-code',
        };
    }

    public function iconSizeClass(): string
    {
        return match ($this) {
            self::EMAIL => 'text-2xl',
            self::SMS => 'text-2xl',
            self::WHATSAPP => 'text-2xl',
            self::CORREIO => 'text-2xl',
            self::ONLINE => 'text-2xl',
        };
    }

    public function iconColorClass(): string
    {
        return match ($this) {
            self::EMAIL => 'text-blue-500',
            self::SMS => 'text-green-500',
            self::WHATSAPP => 'text-green-600',
            self::CORREIO => 'text-red-500',
            self::ONLINE => 'text-gray-500',
        };
    }

}
