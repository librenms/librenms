<?php

return [
    'maintenance' => [
        'maintenance' => 'Mantenimiento',
        'behavior' => [
            'options' => [
                'skip_alerts' => 'Omitir alertas',
                'mute_alerts' => 'Silenciar alertas',
                'run_alerts' => 'Ejecutar alertas',
            ],
            'tooltip' => '- Omitir alertas: No se crearán nuevas alertas y las existentes no se resolverán.
                - Silenciar alertas: Las alertas se crearán y resolverán normalmente, pero se suprimen las notificaciones al usuario (como correo electrónico).
                - Ejecutar alertas: Las alertas se ejecutan normalmente y se notifica a los usuarios. Esta opción es esencialmente un mantenimiento \'solo cosmético\'',
        ],
    ],
];
