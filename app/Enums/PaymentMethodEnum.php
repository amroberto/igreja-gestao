<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CreditCard = 'creditcard';
    case DebitCard = 'debitcard';
    case BankSlip = 'boleto_bancario';
    case Pix = 'pix';
    case Cash = 'cash';
    case BankTransfer = 'transf_bancaria';
    
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    // Método para obter os valores
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Método para obter o rótulo amigável
    public function label(): string
    {
        return match ($this) {
            self::CreditCard => __('Cartão de Crédito'),
            self::DebitCard => __('Cartão de Débito'),
            self::BankSlip => __('Boleto Bancário'),
            self::Pix => __('PIX'),
            self::Cash => __('Dinheiro'),
            self::BankTransfer => __('Transferência Bancária'),
        };
    }

    public function getLabels(): array
    {
        return [
            self::CreditCard->value => __('Cartão de Crédito'),
            self::DebitCard->value => __('Cartão de Débito'),
            self::BankSlip->value => __('Boleto Bancário'),
            self::Pix->value => __('PIX'),
            self::Cash->value => __('Dinheiro'),
            self::BankTransfer->value => __('Transferência Bancária'),
        ];
    }

}
