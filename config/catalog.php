<?php

return [
    'collections' => [
        [
            'name' => 'CDs',
            'slug' => 'cds',
            'description' => 'Formato práctico y coleccionable. CDs listos para sonar fuerte en cualquier momento.',
        ],
        [
            'name' => 'Vinyl',
            'slug' => 'vinyl',
            'description' => 'Vinilos para la experiencia completa: arte grande, ritual y presencia en tu estantería.',
        ],
        [
            'name' => 'Thrash Metal',
            'slug' => 'thrash-metal',
            'description' => 'Velocidad, riffs cortantes y actitud. Thrash para sacudir el cuello sin piedad.',
        ],
        [
            'name' => 'Death Metal',
            'slug' => 'death-metal',
            'description' => 'Pesadez, precisión y oscuridad. Death metal para escuchar con respeto y volumen.',
        ],
        [
            'name' => 'Tradicional',
            'slug' => 'traditional',
            'description' => 'La raíz del metal: himnos, melodías y legado. Clásicos que nunca envejecen.',
        ],
    ],

    'products' => [
        // --- THRASH --- //
        [
            'name' => 'Megadeth — Rust in Peace',
            'slug' => 'megadeth-rust-in-peace',
            'description' => 'Thrash técnico y afilado. Un clásico obligatorio en cualquier colección.',
            'price' => 34900,
            'published' => true,
            'total_product_stock' => 12,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['cds', 'thrash-metal'],
            'images' => ['catalog/rust-in-peace.webp'],
        ],
        [
            'name' => 'Metallica — Master of Puppets',
            'slug' => 'metallica-master-of-puppets',
            'description' => 'Riffs gigantes y energía constante. Thrash que define una era.',
            'price' => 32900,
            'published' => true,
            'total_product_stock' => 14,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['vinyl', 'thrash-metal'],
            'images' => ['catalog/mop.jpg'],
        ],

        // --- DEATH METAL --- //
        [
            'name' => 'Pestilence — Testimony of the Ancients',
            'slug' => 'pestilence-testimony-of-the-ancients',
            'description' => 'Death metal con capas, oscuridad y carácter. Ideal si te gusta el detalle.',
            'price' => 33900,
            'published' => true,
            'total_product_stock' => 10,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['cds', 'death-metal'],
            'images' => ['catalog/testimony-of-the-ancients.jpg'],
        ],
        [
            'name' => 'Pestilence — Hadeon',
            'slug' => 'pestilence-hadeon',
            'description' => 'Death metal moderno y contundente. Directo al cuello.',
            'price' => 31900,
            'published' => true,
            'total_product_stock' => 9,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['vinyl', 'death-metal'],
            'images' => ['catalog/hadeon.jpg'],
        ],
        [
            'name' => 'Dark Tranquillity — Atoma',
            'slug' => 'dark-tranquillity-atoma',
            'description' => 'Melodeath atmosférico y melancólico. Pesado, pero con melodía que se queda.',
            'price' => 32900,
            'published' => true,
            'total_product_stock' => 11,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['cds', 'death-metal'],
            'images' => ['catalog/atoma.jpg'],
        ],
        [
            'name' => 'Death — Symbolic',
            'slug' => 'death-symbolic',
            'description' => 'Técnico, emotivo y pesado. Un punto altísimo del death metal.',
            'price' => 35900,
            'published' => true,
            'total_product_stock' => 8,
            'stock_status' => 'low_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['vinyl', 'death-metal'],
            'images' => ['catalog/symbolic.jpg'],
        ],

        // --- POWER METAL (solo formato, no hay colección de power en tu lista) --- //
        [
            'name' => 'Stratovarius — Visions',
            'slug' => 'stratovarius-visions',
            'description' => 'Power metal melódico, veloz y épico. Coros grandes y energía positiva.',
            'price' => 32900,
            'published' => true,
            'total_product_stock' => 10,
            'stock_status' => 'in_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Solo formato
            'collections' => ['cds'],
            'images' => ['catalog/visions.jpg'],
        ],

        // --- TRADICIONAL --- //
        [
            'name' => 'Iron Maiden — Powerslave',
            'slug' => 'iron-maiden-powerslave',
            'description' => 'Heavy metal clásico en estado puro. De esos discos que se heredan.',
            'price' => 59900,
            'published' => true,
            'total_product_stock' => 6,
            'stock_status' => 'low_stock',
            'low_stock_threshold' => 5,
            'cached_quantity_sold' => 0,
            // Formato + subgénero
            'collections' => ['vinyl', 'traditional'],
            'images' => ['catalog/powerslave.jpg'],
        ],
    ],
];
