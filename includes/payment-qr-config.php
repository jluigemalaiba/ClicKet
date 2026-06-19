<?php

declare(strict_types=1);

function clicketPaymentQrConfig(): array {
    return [
        'moa-arena' => [
            'label' => 'MOA Arena',
            'aliases' => ['MOA Arena', 'Mall of Asia Arena'],
            'methods' => [
                'gcash' => 'assets/MOA_gcash.png',
                'maya' => 'assets/MOA_paymaya.png',
                'qrph' => 'assets/Moa_QRph.png',
            ],
        ],
        'smart-araneta-coliseum' => [
            'label' => 'Smart Araneta Coliseum',
            'aliases' => ['Smart Araneta Coliseum', 'Smart Araneta'],
            'methods' => [
                'gcash' => 'assets/Smart_GCash.png',
                'maya' => 'assets/Smart_paymaya.png',
                'qrph' => 'assets/Smart_QRph.png',
            ],
        ],
        'philsports-arena' => [
            'label' => 'PhilSports Arena',
            'aliases' => ['PhilSports Arena', 'Philsports Arena'],
            'methods' => [
                'gcash' => 'assets/Philsport_Gcash.png',
                'maya' => 'assets/Philsport_paymaya.png',
                'qrph' => 'assets/Philsport_QRph.png',
            ],
        ],
        'philippine-arena' => [
            'label' => 'Philippine Arena',
            'aliases' => ['Philippine Arena'],
            'methods' => [
                'gcash' => 'assets/Philippine_Gcash.png',
                'maya' => 'assets/Arena_Paymaya.png',
                'qrph' => 'assets/Arena_QRph.png',
            ],
        ],
        'newport-performing-arts-theater' => [
            'label' => 'Newport Performing Arts Theater',
            'aliases' => ['Newport Performing Arts Theater'],
            'methods' => [
                'gcash' => 'assets/Newsports_Gcash.png',
                'maya' => 'assets/Newport_Arena.png',
                'qrph' => 'assets/Newport_QRPH.png',
            ],
        ],
        'the-theatre-at-solaire' => [
            'label' => 'The Theatre at Solaire',
            'aliases' => ['The Theatre at Solaire', 'Solaire Resort Entertainment City', 'Solaire'],
            'methods' => [
                'gcash' => 'assets/theatre_Gcash.png',
                'maya' => 'assets/Theatre_paymaya.png',
                'qrph' => 'assets/Theatre_QRph.png',
            ],
        ],
        'tanghalang-ignacio-jimenez' => [
            'label' => 'Tanghalang Ignacio Jimenez',
            'aliases' => ['Tanghalang Ignacio Jimenez', 'Tanghalang Pilipino'],
            'methods' => [
                'gcash' => 'assets/Tanghalang_Gcash.png',
                'maya' => 'assets/tanghalan_paymaya.png',
                'qrph' => 'assets/tanghalan_QRph.png',
            ],
        ],
    ];
}

function clicketPaymentQrSlug(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? ''));
    return trim($slug, '-');
}

function clicketPaymentQrForVenue(string $venue, string $method): ?array {
    $method = strtolower(trim($method));
    if (!in_array($method, ['gcash', 'maya', 'qrph'], true)) {
        return null;
    }

    $venueSlug = clicketPaymentQrSlug($venue);
    foreach (clicketPaymentQrConfig() as $key => $definition) {
        $aliases = array_merge([$definition['label'] ?? $key], (array) ($definition['aliases'] ?? []));
        $slugs = array_map('clicketPaymentQrSlug', array_map('strval', $aliases));
        if ($venueSlug !== $key && !in_array($venueSlug, $slugs, true)) {
            continue;
        }

        $path = (string) (($definition['methods'] ?? [])[$method] ?? '');
        return [
            'venue_key' => $key,
            'venue_label' => (string) ($definition['label'] ?? $venue),
            'method' => $method,
            'path' => $path,
            'exists' => $path !== '' && is_file(dirname(__DIR__) . '/' . $path),
        ];
    }

    return null;
}
