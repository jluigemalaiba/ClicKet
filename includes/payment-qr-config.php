<?php

declare(strict_types=1);

function clicketPaymentQrConfig(): array {
    return [
        'moa-arena' => [
            'label' => 'Mall of Asia Arena',
            'aliases' => ['MOA Arena', 'Mall of Asia Arena'],
            'qrph' => 'assets/payment/Moa_QRph.png',
        ],
        'smart-araneta-coliseum' => [
            'label' => 'Smart Araneta Coliseum',
            'aliases' => ['Smart Araneta Coliseum', 'Smart Araneta', 'Araneta Coliseum'],
            'qrph' => 'assets/payment/araneta_QRph.png',
        ],
        'philsports-arena' => [
            'label' => 'PhilSports Arena',
            'aliases' => ['PhilSports Arena', 'Philsports Arena'],
            'qrph' => 'assets/payment/Philsport_QRph.png',
        ],
        'philippine-arena' => [
            'label' => 'Philippine Arena',
            'aliases' => ['Philippine Arena'],
            'qrph' => 'assets/payment/Arena_QRph.png',
        ],
        'newport-performing-arts-theater' => [
            'label' => 'Newport Performing Arts Theater',
            'aliases' => ['Newport Performing Arts Theater'],
            'qrph' => 'assets/payment/Newport_QRPH.png',
        ],
        'the-theatre-at-solaire' => [
            'label' => 'The Theatre at Solaire',
            'aliases' => ['The Theatre at Solaire', 'Solaire Resort Entertainment City', 'Solaire'],
            'qrph' => 'assets/payment/solaire_QRph.png',
        ],
        'tanghalang-ignacio-jimenez' => [
            'label' => 'Tanghalang Ignacio Jimenez',
            'aliases' => ['Tanghalang Ignacio Jimenez', 'Tanghalang Pilipino'],
            'qrph' => 'assets/payment/tanghalan_QRph.png',
        ],
    ];
}

function clicketPaymentQrSlug(string $value): string {
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $value) ?? ''));
    return trim($slug, '-');
}

function clicketPaymentQrForVenue(string $venue, string $method = 'qrph'): ?array {
    if (strtolower(trim($method)) !== 'qrph') {
        return null;
    }

    $venueSlug = clicketPaymentQrSlug($venue);
    foreach (clicketPaymentQrConfig() as $key => $definition) {
        $aliases = array_merge([$definition['label'] ?? $key], (array) ($definition['aliases'] ?? []));
        $slugs = array_map('clicketPaymentQrSlug', array_map('strval', $aliases));
        if ($venueSlug !== $key && !in_array($venueSlug, $slugs, true)) {
            continue;
        }

        $path = (string) ($definition['qrph'] ?? '');
        return [
            'venue_key' => $key,
            'venue_label' => (string) ($definition['label'] ?? $venue),
            'method' => 'qrph',
            'path' => $path,
            'exists' => $path !== '' && is_file(dirname(__DIR__) . '/' . $path),
        ];
    }

    return null;
}
