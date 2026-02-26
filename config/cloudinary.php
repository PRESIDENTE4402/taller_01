<?php

return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    'folders' => [
        'logo' => 'landing-images/logo',
        'service' => 'landing-images/services',
        'gallery' => 'landing-images/gallery',
    ],

    'upload_options' => [
        'quality' => 'auto',
        'fetch_format' => 'auto',
        'max_size' => 5120, // KB
    ],
];
