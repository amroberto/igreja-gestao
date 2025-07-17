<?php

if (!function_exists('formatCnpj')) {
    function formatCnpj(?string $cnpj): string
    {
        if (!$cnpj) return '-';
        return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj);
    }
}

if (!function_exists('formatCpf')) {
    function formatCpf(?string $cpf): string
    {
        if (!$cpf) return '-';
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
}

if (!function_exists('formatRg')) {
    function formatRg(?string $rg): string
    {
        if (!$rg) return '-';
        return preg_replace("/(\d{1})(\d{3})(\d{3})(\d{1})/", "$1.$2.$3-$4", $rg);
    }
}

if (!function_exists('formatPhone')) {
    function formatPhone(?string $phone): string
    {
        if (!$phone) return '-';
        return preg_replace("/(\d{2})(\d{4,5})(\d{4})/", "($1) $2-$3", $phone);
    }
}

if (!function_exists('formatCep')) {
    function formatCep(?string $cep): string
    {
        if (!$cep) return '-';
        return preg_replace("/(\d{5})(\d{3})/", "$1-$2", $cep);
    }
}