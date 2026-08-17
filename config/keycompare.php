<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        // Set to false in development if you get SSL errors downloading
        // from sites with self-signed certs. Keep true in production.
        'verify_ssl' => env('MEDIA_VERIFY_SSL', true),

        // Max file size for manual uploads (in KB)
        'max_upload_kb' => env('MEDIA_MAX_UPLOAD_KB', 10240),
    ],
];
