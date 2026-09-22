<?php

namespace App\Enums;

enum AccountTypeEnum: string
{
    case BankAccount = 'bank_account';
    case CashDrawer = 'cash_drawer';
    case CreditCard = 'credit_card';
    case Savings = 'savings';
    case Investment = 'investment';

    public function label(): string
    {
        return match ($this) {
            self::BankAccount => 'Conta Corrente',
            self::CashDrawer => 'Caixa',
            self::CreditCard => 'Cartão de Crédito / Débito',
            self::Savings => 'Poupança',
            self::Investment => 'Aplicação Financeira',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BankAccount => 'bank',
            self::CashDrawer => 'cash',
            self::CreditCard => 'credit-card',
            self::Savings => 'savings',
            self::Investment => 'investment',
        };
    }
}
