<?php

return [
  'settings' => 
  [
    'settings' => 
    [
      'poller_groups' => 
      [
        'description' => 'Grupos asignados',
        'help' => 'Este nodo solo actuará sobre dispositivos en estos grupos de poller.',
      ],
      'poller_enabled' => 
      [
        'description' => 'Poller habilitado',
        'help' => 'Habilitar workers de poller en este nodo.',
      ],
      'poller_workers' => 
      [
        'description' => 'Workers de poller',
        'help' => 'Cantidad de workers de poller a iniciar en este nodo.',
      ],
      'poller_frequency' => 
      [
        'description' => 'Frecuencia de poller (¡Advertencia!)',
        'help' => 'Cada cuánto sondear dispositivos en este nodo. ¡Advertencia! Cambiar esto sin corregir los archivos rrd romperá las gráficas. Ver la documentación para más información.',
      ],
      'poller_down_retry' => 
      [
        'description' => 'Reintento de dispositivo caído',
        'help' => 'Si un dispositivo está caído cuando se intenta el polling en este nodo, este es el tiempo de espera antes de reintentar.',
      ],
      'discovery_enabled' => 
      [
        'description' => 'Descubrimiento habilitado',
        'help' => 'Habilitar workers de descubrimiento en este nodo.',
      ],
      'discovery_workers' => 
      [
        'description' => 'Workers de descubrimiento',
        'help' => 'Cantidad de workers de descubrimiento a ejecutar en este nodo. Configurar demasiado alto puede causar sobrecarga.',
      ],
      'discovery_frequency' => 
      [
        'description' => 'Frecuencia de descubrimiento',
        'help' => 'Cada cuánto ejecutar el descubrimiento en este nodo. El valor predeterminado es 4 veces al día.',
      ],
      'services_enabled' => 
      [
        'description' => 'Servicios habilitados',
        'help' => 'Habilitar workers de servicios en este nodo.',
      ],
      'services_workers' => 
      [
        'description' => 'Workers de servicios',
        'help' => 'Cantidad de workers de servicios en este nodo.',
      ],
      'services_frequency' => 
      [
        'description' => 'Frecuencia de servicios',
        'help' => 'Cada cuánto ejecutar los servicios en este nodo. Debe coincidir con la frecuencia del poller.',
      ],
      'billing_enabled' => 
      [
        'description' => 'Facturación habilitada',
        'help' => 'Habilitar workers de facturación en este nodo.',
      ],
      'billing_frequency' => 
      [
        'description' => 'Frecuencia de facturación',
        'help' => 'Cada cuánto recolectar datos de facturación en este nodo.',
      ],
      'billing_calculate_frequency' => 
      [
        'description' => 'Frecuencia de cálculo de facturación',
        'help' => 'Cada cuánto calcular el uso de facturación en este nodo.',
      ],
      'alerting_enabled' => 
      [
        'description' => 'Alertas habilitadas',
        'help' => 'Habilitar el worker de alertas en este nodo.',
      ],
      'alerting_frequency' => 
      [
        'description' => 'Frecuencia de alertas',
        'help' => 'Cada cuánto verificar las reglas de alerta en este nodo. Nota: los datos solo se actualizan según la frecuencia del poller.',
      ],
      'ping_enabled' => 
      [
        'description' => 'Ping rápido habilitado',
        'help' => 'El ping rápido solo hace ping a los dispositivos para comprobar si están activos o caídos.',
      ],
      'ping_frequency' => 
      [
        'description' => 'Frecuencia de ping',
        'help' => 'Cada cuánto verificar el ping en este nodo. ¡Advertencia! Si cambia esto debe realizar cambios adicionales. Consulte la documentación de Ping Rápido.',
      ],
      'update_enabled' => 
      [
        'description' => 'Mantenimiento diario habilitado',
        'help' => 'Ejecutar el script de mantenimiento daily.sh y reiniciar el servicio dispatcher después.',
      ],
      'update_frequency' => 
      [
        'description' => 'Frecuencia de mantenimiento',
        'help' => 'Cada cuánto ejecutar el mantenimiento diario en este nodo. El valor predeterminado es 1 día. Se recomienda no cambiar esto.',
      ],
      'loglevel' => 
      [
        'description' => 'Nivel de registro',
        'help' => 'Nivel de registro del servicio dispatcher.',
      ],
      'watchdog_enabled' => 
      [
        'description' => 'Watchdog habilitado',
        'help' => 'El watchdog monitorea el archivo de registro y reinicia el servicio si no ha sido actualizado.',
      ],
      'watchdog_log' => 
      [
        'description' => 'Archivo de registro a monitorear',
        'help' => 'El valor predeterminado es el archivo de registro de LibreNMS.',
      ],
    ],
    'units' => 
    [
      'seconds' => 'Segundos',
      'workers' => 'Workers',
    ],
  ],
];
