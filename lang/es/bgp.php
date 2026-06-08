<?php

return [
  'error_codes' => 
  [
    0 => 'Reservado',
    1 => 'Error en cabecera de mensaje',
    2 => 'Error en mensaje OPEN',
    3 => 'Error en mensaje UPDATE',
    4 => 'Temporizador de espera expirado',
    5 => 'Error en máquina de estados finitos',
    6 => 'Cese',
    7 => 'Error en mensaje ROUTE-REFRESH',
  ],
  'error_subcodes' => 
  [
    1 => 
    [
      0 => 'No especificado',
      1 => 'Conexión no sincronizada',
      2 => 'Longitud de mensaje incorrecta',
      3 => 'Tipo de mensaje incorrecto',
    ],
    2 => 
    [
      0 => 'No especificado',
      1 => 'Número de versión no soportado',
      2 => 'AS par incorrecto',
      3 => 'Identificador BGP incorrecto',
      4 => 'Parámetro opcional no soportado',
      5 => '[Obsoleto]',
      6 => 'Tiempo de espera inaceptable',
      7 => 'Conflicto de rol (Borrador BGP temporal)',
    ],
    3 => 
    [
      0 => 'No especificado',
      1 => 'Lista de atributos malformada',
      2 => 'Atributo bien conocido no reconocido',
      3 => 'Atributo bien conocido faltante',
      4 => 'Error en flags de atributo',
      5 => 'Error en longitud de atributo',
      6 => 'Atributo ORIGIN inválido',
      7 => '[Obsoleto]',
      8 => 'Atributo NEXT_HOP inválido',
      9 => 'Error en atributo opcional',
      10 => 'Campo de red inválido',
      11 => 'AS_PATH malformado',
    ],
    5 => 
    [
      0 => 'Error no especificado',
      1 => 'Mensaje inesperado recibido en estado OpenSent',
      2 => 'Mensaje inesperado recibido en estado OpenConfirm',
      3 => 'Mensaje inesperado recibido en estado Established',
    ],
    6 => 
    [
      0 => 'Reservado',
      1 => 'Número máximo de prefijos alcanzado',
      2 => 'Apagado administrativo',
      3 => 'Par desconfigurado',
      4 => 'Reinicio administrativo',
      5 => 'Conexión rechazada',
      6 => 'Otro cambio de configuración',
      7 => 'Resolución de colisión de conexión',
      8 => 'Sin recursos',
      9 => 'Reinicio forzado',
    ],
  ],
];
