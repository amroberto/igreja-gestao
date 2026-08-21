<?php

namespace App\Helpers;

class PixHelper
{
    public static function generatePayload(
        string $pixKey,
        string $receiverName,
        string $city,
        ?float $amount = null,
        ?string $txid = '***',
        ?string $pixKeyType = null
    ): string {
        // 1. Sanitização da Chave
        $type = strtolower((string)$pixKeyType);
        if (in_array($type, ['cnpj', 'cpf']) || preg_match('/^\d{2,3}\.\d{3}/', $pixKey)) {
            $pixKey = preg_replace('/[^0-9]/', '', $pixKey);
        } else {
            $pixKey = preg_replace('/[^a-zA-Z0-9@.\-\+]/', '', $pixKey);
        }

        $receiverName = self::sanitizeString($receiverName, 25);
        $city = self::sanitizeString($city, 15);

        // 2. Sanitização do TXID (Apenas letras e números, máximo 25 caracteres)
        $txid = preg_replace('/[^a-zA-Z0-9]/', '', $txid ?? '');
        $txid = empty($txid) ? '***' : substr($txid, 0, 25);

        // 3. Montagem do Payload EMV
        $merchantAccount = self::formatField('00', 'br.gov.bcb.pix')
            . self::formatField('01', $pixKey);

        $payload = self::formatField('00', '01')
            . self::formatField('26', $merchantAccount)
            . self::formatField('52', '0000')
            . self::formatField('53', '986');

        if ($amount !== null && $amount > 0) {
            $payload .= self::formatField('54', number_format($amount, 2, '.', ''));
        }

        $payload .= self::formatField('58', 'BR')
            . self::formatField('59', $receiverName)
            . self::formatField('60', $city)
            . self::formatField('62', self::formatField('05', $txid)) // Campo 05 = TXID no aplicativo do banco
            . '6304';

        return $payload . self::calculateCrc16($payload);
    }

    /**
     * Formata um campo do payload PIX (ID + Tamanho em Bytes + Valor)
     */
    private static function formatField(string $id, string $value): string
    {
        // Usa strlen para garantir a contagem correta em bytes
        $length = sprintf('%02d', strlen($value));
        return $id . $length . $value;
    }

    /**
     * Remove acentos e caracteres especiais para validação do Banco Central
     */
    private static function sanitizeString(string $string, int $maxLength): string
    {
        // Remove acentos
        $string = preg_replace(
            '/[áàâãäåæçéèêëíìîïñóòôõöøúùûüýÿ]/ui',
            'a',
            $string
        );
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        // Mantém apenas letras, números e espaços
        $string = preg_replace('/[^a-zA-Z0-9 ]/', '', $string);
        $string = strtoupper(trim($string));

        return substr($string, 0, $maxLength);
    }

    /**
     * Algoritmo Oficial CRC16 (CCITT-FALSE) exigido pelo Banco Central
     */
    private static function calculateCrc16(string $payload): string
    {
        $crc = 0xFFFF;
        $length = strlen($payload);

        for ($i = 0; $i < $length; $i++) {
            $crc ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(sprintf('%04X', $crc));
    }
}
