<?php

/**
 * @file
 * @brief       The plugin FrontendWebauthn definition
 * @ingroup     FrontendWebauthn
 *
 * @defgroup    FrontendWebauthn Plugin cinecturlink2.
 *
 * Use passkey on frontend.
 *
 * @author      Jean-Christian Paul Denis
 * @copyright   AGPL-3.0
 */
declare(strict_types=1);

$this->registerModule(
    'Frontend webauthn',
    'Use passkey on frontend.',
    'Jean-Christian Paul Denis and Contributors',
    '0.2',
    [
        'requires'    => [
            ['core', '2.36'],
            ['FrontendSession', '0.34']
        ],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-08-24T07:00:28+00:00',
    ]
);
