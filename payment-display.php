<?php
declare(strict_types=1);

function payment_destination_lines(array $enabledMethods, array $destinations): array
{
    $lines = [];
    $fields = [
        'bank_transfer' => [
            'label' => 'Bank transfer',
            'details' => [
                'bank_name' => 'Bank',
                'bank_account_name' => 'Account name',
                'bank_account_number' => 'Account number',
            ],
        ],
        'mtn_momo' => [
            'label' => 'MTN Mobile Money',
            'details' => [
                'mtn_momo_name' => 'Account name',
                'mtn_momo_number' => 'Number',
                'mtn_momo_code' => 'Payment code',
            ],
        ],
        'airtel_money' => [
            'label' => 'Airtel Money',
            'details' => [
                'airtel_money_name' => 'Account name',
                'airtel_money_number' => 'Number',
                'airtel_money_code' => 'Payment code',
            ],
        ],
    ];

    foreach ($enabledMethods as $method) {
        if ($method === 'cash') {
            $lines[] = 'Cash: Pay at the clinic.';
            continue;
        }
        if (!isset($fields[$method])) {
            continue;
        }

        $parts = [];
        foreach ($fields[$method]['details'] as $field => $label) {
            $value = trim((string) ($destinations[$field] ?? ''));
            if ($value !== '') {
                $parts[] = $label . ': ' . $value;
            }
        }
        $lines[] = $parts
            ? $fields[$method]['label'] . ' — ' . implode(' · ', $parts)
            : $fields[$method]['label'] . ': Contact the care team for payment details.';
    }

    return $lines;
}
