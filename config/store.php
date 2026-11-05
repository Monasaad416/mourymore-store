<?php

return [

    'name' => 'mourymore_store',

    'logo' => 'images/logo&cover/moury_more_logo.png',

    'cover' => 'images/logo&cover/moury_more_cover.jpg',

    'tagline' => 'Egypt’s modern marketplace',

    'free_shipping_over' => 1500,

    'promo_code' => 'mourymore_store10',

    'promo_rate' => 0.10,

    'cart_ttl_days' => 30,

    // Set true and restore PaymentMethod `cod` in StoreSeeder to re-enable cash on delivery.
    'cod_enabled' => false,

    'greater_cairo' => ['Cairo', 'Giza', 'Qalyubia'],

    'delta' => [
        'Alexandria',
        'Beheira',
        'Dakahlia',
        'Damietta',
        'Gharbia',
        'Kafr El Sheikh',
        'Monufia',
        'Sharqia',
    ],

    'governorates' => [
        'Cairo',
        'Giza',
        'Alexandria',
        'Qalyubia',
        'Sharqia',
        'Dakahlia',
        'Gharbia',
        'Monufia',
        'Beheira',
        'Kafr El Sheikh',
        'Damietta',
        'Port Said',
        'Ismailia',
        'Suez',
        'Fayoum',
        'Beni Suef',
        'Minya',
        'Assiut',
        'Sohag',
        'Qena',
        'Luxor',
        'Aswan',
        'Red Sea',
        'New Valley',
        'Matrouh',
        'North Sinai',
        'South Sinai',
    ],

    'payments' => [
        'paymob' => [
            'label' => 'Paymob',
            'hint' => 'Pay securely by card or wallet. Stock is held until payment succeeds.',
            'tag' => 'Card',
        ],
        // Legacy labels kept for older orders / content copy:
        'instapay' => [
            'label' => 'InstaPay',
            'hint' => 'We send an InstaPay request by SMS after you place the order.',
            'tag' => 'Instant',
        ],
        'vodafone' => [
            'label' => 'Vodafone Cash',
            'hint' => 'Pay from your Vodafone Cash wallet. No card needed.',
            'tag' => 'Wallet',
        ],
        'fawry' => [
            'label' => 'Fawry',
            'hint' => 'Pay at any Fawry outlet with the order code we text you.',
            'tag' => 'Cash points',
        ],
        'card' => [
            'label' => 'Visa or Mastercard',
            'hint' => 'A secure payment link arrives by SMS. Card numbers are never stored here.',
            'tag' => 'Card',
        ],
    ],

];
