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
        return array_column(self::cases(), 'value');
    }

    /**
     * Rótulo amigável em português para exibição em Views, PDFs e relatórios Excel.
     */
    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'E-mail',
            self::SMS => 'SMS',
            self::WHATSAPP => 'WhatsApp',
            self::CORREIO => 'Correio / Postal',
            self::ONLINE => 'On-line',
        };
    }

    /**
     * Classe de ícone FontAwesome para o AdminLTE.
     */
    public function iconClass(): string
    {
        return match ($this) {
            self::EMAIL => 'fas fa-envelope',
            self::SMS => 'fas fa-mobile-alt',
            self::WHATSAPP => 'fab fa-whatsapp',
            self::CORREIO => 'fas fa-mail-bulk',
            self::ONLINE => 'fas fa-laptop-code',
        };
    }

    /**
     * Cor da badge / contexto compatível com Bootstrap / AdminLTE.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::EMAIL => 'info',
            self::SMS => 'warning',
            self::WHATSAPP => 'success',
            self::CORREIO => 'danger',
            self::ONLINE => 'secondary',
        };
    }
}