<?php

return [
    'registration' => [
        // Em produção, deixe vazio e informe um código para permitir auto-cadastro com convite.
        'invite_code' => env('ADMIN_INVITE_CODE', ''),

        // Papel padrão para cadastros via tela de registro (exceto o primeiro usuário).
        'default_role' => env('ADMIN_DEFAULT_ROLE', 'atendente'),

        // Em ambiente local, permite cadastro sem código.
        'allow_in_local' => (bool) env('ADMIN_REGISTRATION_ALLOW_IN_LOCAL', true),
    ],
];

