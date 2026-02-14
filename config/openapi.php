<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Documentation Title
    |--------------------------------------------------------------------------
    */
    'title' => 'Taller 01 - SaaS Multitenant API',
    'description' => 'API Completa para Gestión de Inventario SaaS con Atributos Dinámicos',

    /*
    |--------------------------------------------------------------------------
    | OpenAPI/Swagger Version
    |--------------------------------------------------------------------------
    */
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    */
    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost'),
            'description' => 'Servidor Principal',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Schemes
    |--------------------------------------------------------------------------
    */
    'security_schemes' => [
        'bearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
        'cookieAuth' => [
            'type' => 'apiKey',
            'in' => 'cookie',
            'name' => 'XSRF-TOKEN',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'name' => 'Soporte Técnico',
        'email' => 'soporte@taller01.com',
        'url' => 'https://taller01.com/soporte',
    ],

    /*
    |--------------------------------------------------------------------------
    | License
    |--------------------------------------------------------------------------
    */
    'license' => [
        'name' => 'MIT',
        'url' => 'https://opensource.org/licenses/MIT',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tags (Agrupación de endpoints)
    |--------------------------------------------------------------------------
    */
    'tags' => [
        [
            'name' => 'Autenticación',
            'description' => 'Endpoints de login, logout y gestión de sesión',
        ],
        [
            'name' => 'Categorías',
            'description' => 'Gestión de categorías de productos (Multitenant)',
        ],
        [
            'name' => 'Repuestos',
            'description' => 'Gestión de repuestos e inventario con atributos dinámicos',
        ],
        [
            'name' => 'Atributos Dinámicos',
            'description' => 'Configuración de atributos personalizables por categoría',
        ],
        [
            'name' => 'Clientes',
            'description' => 'Gestión de clientes (Multitenant)',
        ],
        [
            'name' => 'Vehículos',
            'description' => 'Gestión de vehículos de clientes',
        ],
        [
            'name' => 'Reportes',
            'description' => 'Reportes de inventario, stock y auditoría',
        ],
    ],
];
