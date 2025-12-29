<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings for the Azure Socialite plugin.
    | You can override these per panel when registering the plugin.
    |
    */

    'defaults' => [
        'enabled' => true,
        'button_label' => 'Login with Microsoft',
        'hook' => 'after', // 'before' or 'after' the login form
        'allow_registration' => true,
        'allowed_domains' => null, // null = no restriction, array of domains
        'allowed_tenants' => null, // null = no restriction, array of tenant IDs
    ],
];

