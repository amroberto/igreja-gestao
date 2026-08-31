<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CreditCard = 'creditcard';
    case DebitCard = 'debitcard';
    case BankSlip = 'boleto_bancario';
    case Pix = 'pix';
    case Cash = 'cash';
    case BankCheck = 'bank_check';
    case BankTransfer = 'transf_bancaria';

    // Rótulo legível para exibição
    public function label(): string
    {
        return match ($this) {
            self::CreditCard => __('Cartão de Crédito'),
            self::DebitCard => __('Cartão de Débito'),
            self::BankSlip => __('Boleto Bancário'),
            self::Pix => __('PIX'),
            self::Cash => __('Dinheiro'),
            self::BankCheck => __('Cheque'),
            self::BankTransfer => __('Transferência Bancária'),
        };
    }

    // Retorna array associativo para preencher selects no formulário [ 'transf_bancaria' => 'Transferência Bancária' ]
    public static function array(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }
}