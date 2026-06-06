<?php

return [
  'alpha_space' => 'El campo :attribute solo puede contener letras, números, guiones bajos y espacios.',
  'ip_or_hostname' => 'El campo :attribute debe ser una dirección IP/red válida o un nombre de host.',
  'is_regex' => 'El campo :attribute no es una expresión regular válida',
  'array_keys_not_empty' => 'El campo :attribute contiene claves de array vacías.',
  'custom' => 
  [
    'attribute-name' => 
    [
    ],
  ],
  'attributes' => 
  [
  ],
  'results' => 
  [
    'autofix' => 'Intentar corregir automáticamente',
    'fix' => 'Corregir',
    'fixed' => 'La corrección se completó, recargue para volver a ejecutar las validaciones.',
    'fetch_failed' => 'Error al obtener resultados de validación',
    'backend_failed' => 'Error al cargar datos del backend, ejecute ./validate.php en la consola para verificar.',
    'invalid_fixer' => 'Corrector inválido',
    'show_all' => 'Mostrar todo',
    'show_less' => 'Mostrar menos',
    'validate' => 'Validar',
    'validating' => 'Validando',
    'skipped' => 'Omitido',
    'run' => 'Ejecutar',
  ],
  'validations' => 
  [
    'groups' => 
    [
      'configuration' => 'Configuración',
      'database' => 'Base de datos',
      'dependencies' => 'Dependencias',
      'disk' => 'Disco',
      'distributedpoller' => 'Poller distribuido',
      'mail' => 'Correo',
      'php' => 'PHP',
      'poller' => 'Poller',
      'programs' => 'Programas',
      'python' => 'Python',
      'rrd' => 'RRD',
      'rrdcheck' => 'Verificación RRD',
      'scheduler' => 'Programador',
      'system' => 'Sistema',
      'updates' => 'Actualizaciones',
      'user' => 'Usuario',
      'webserver' => 'Servidor Web',
    ],
    'rrd' => 
    [
      'CheckRrdVersion' => 
      [
        'fail' => 'La versión de rrdtool especificada es más nueva que la instalada. Config: :config_version Instalada: :installed_version',
        'fix' => 'Comente o elimine $config[\'rrdtool_version\'] = \':version\'; de su archivo config.php',
        'ok' => 'Versión de rrdtool correcta',
      ],
      'CheckRrdcachedConnectivity' => 
      [
        'fail_socket' => ':socket no parece existir, prueba de conectividad con rrdcached fallida',
        'fail_port' => 'No se puede conectar al servidor rrdcached :server en el puerto :port',
        'ok' => 'Conectado a rrdcached',
      ],
      'CheckRrdDirPermissions' => 
      [
        'fail_root' => 'Su directorio RRD es propiedad de root, considere cambiarlo a un usuario no root',
        'fail_mode' => 'Su directorio RRD no está configurado en 0775',
        'ok' => 'rrd_dir tiene permisos de escritura',
      ],
      'CheckRrdStep' => 
      [
        'fail' => 'Algunos archivos RRD tienen el step incorrecto. :bad/:total',
        'fail_bad_files' => 'Errores al leer archivos RRD. :bad/:total',
        'list_bad_step_title' => 'Archivos RRD con step incorrecto',
        'list_bad_files_title' => 'Error al ejecutar rrdinfo en archivos',
        'list_bad_step_item' => ':file: el step es :step, debería ser :target',
        'ok' => 'Todos los :total archivos RRD tienen el step correcto.',
        'timeout' => 'La verificación de archivos RRD tardó demasiado, verificación omitida. Puede ejecutar :command para verificar y corregir todos los archivos rrd.',
      ],
    ],
    'database' => 
    [
      'CheckDatabaseConnected' => 
      [
        'fail' => 'No se puede conectar a la base de datos',
        'fail_connect' => 'No se puede conectar a la base de datos. Confirme que el servidor de base de datos está en ejecución y que la información de conexión es correcta. Verifique DB_HOST, DB_PORT y DB_NAME en el entorno o en :env_file',
        'fail_access' => 'Base de datos conectada, pero el usuario no tiene permiso para acceder. Ejecute la consulta SQL para otorgar permisos (cambie localhost por el nombre de host local si la base de datos es remota]',
        'fail_auth' => 'Credenciales de base de datos incorrectas. Verifique DB_USERNAME y DB_PASSWORD en el entorno o en :env_file',
        'ok' => 'Base de datos conectada',
      ],
      'CheckDatabaseTableNamesCase' => 
      [
        'fail' => 'Tiene lower_case_table_names configurado en 1 o true en la configuración de MySQL.',
        'fix' => 'Establezca lower_case_table_names=0 en su archivo de configuración MySQL en la sección [mysqld].',
        'ok' => 'lower_case_table_names está habilitado',
      ],
      'CheckDatabaseServerVersion' => 
      [
        'fail' => ':server versión :min es la versión mínima soportada desde :date.',
        'fix' => 'Actualice :server a una versión soportada, se sugiere :suggested.',
        'ok' => 'El servidor SQL cumple los requisitos mínimos',
      ],
      'CheckMysqlEngine' => 
      [
        'fail' => 'Algunas tablas no están usando el motor InnoDB recomendado, esto puede causarle problemas.',
        'tables' => 'Tablas',
        'ok' => 'El motor MySQL es óptimo',
      ],
      'CheckSqlServerTime' => 
      [
        'fail' => 'La diferencia de tiempo entre este servidor y la base de datos MySQL es incorrecta
 Hora MySQL: :mysql_time
 Hora PHP: :php_time',
        'ok' => 'La hora de MySQL y PHP coinciden',
      ],
      'CheckSchemaVersion' => 
      [
        'fail_outdated' => '¡Su base de datos está desactualizada!',
        'fail_legacy_outdated' => 'Su esquema de base de datos (:current] es más antiguo que el último (:latest].',
        'fix_legacy_outdated' => 'Ejecute manualmente ./daily.sh y verifique si hay errores.',
        'warn_extra_migrations' => 'Su esquema de base de datos tiene migraciones adicionales (:migrations]. Si acaba de cambiar de la versión diaria a la estable, su base de datos está entre versiones y esto se resolverá con la próxima versión.',
        'warn_legacy_newer' => 'Su esquema de base de datos (:current] es más nuevo de lo esperado (:latest]. Si acaba de cambiar de la versión diaria a la estable, su base de datos está entre versiones y esto se resolverá con la próxima versión.',
        'ok' => 'El esquema de base de datos está actualizado',
      ],
      'CheckSchemaCollation' => 
      [
        'ok' => 'Las intercalaciones de base de datos y columnas son correctas',
      ],
    ],
    'distributedpoller' => 
    [
      'CheckDistributedPollerEnabled' => 
      [
        'ok' => 'La configuración de polling distribuido está habilitada globalmente',
        'not_enabled' => 'No ha habilitado distributed_poller',
        'not_enabled_globally' => 'No ha habilitado distributed_poller globalmente',
      ],
      'CheckMemcached' => 
      [
        'not_configured_host' => 'No ha configurado distributed_poller_memcached_host',
        'not_configured_port' => 'No ha configurado distributed_poller_memcached_port',
        'could_not_connect' => 'No se pudo conectar al servidor memcached',
        'ok' => 'La conexión a memcached es correcta',
      ],
      'CheckRrdcached' => 
      [
        'fail' => 'No ha habilitado rrdcached',
      ],
    ],
    'poller' => 
    [
      'CheckActivePoller' => 
      [
        'fail' => 'El poller no está en ejecución. Ningún poller ha ejecutado en los últimos :interval segundos.',
        'both_fail' => 'Tanto el Servicio Dispatcher como el Wrapper de Python estuvieron activos recientemente, esto podría causar doble polling',
        'ok' => 'Pollers activos encontrados',
      ],
      'CheckDispatcherService' => 
      [
        'fail' => 'No se encontraron nodos del dispatcher activos',
        'ok' => 'El servicio Dispatcher está habilitado',
        'nodes_down' => 'Algunos nodos del dispatcher no han respondido recientemente',
        'not_detected' => 'Servicio Dispatcher no detectado',
        'warn' => 'El servicio Dispatcher ha sido usado, pero no recientemente',
      ],
      'CheckLocking' => 
      [
        'fail' => 'Problema con el servidor de caché: :message',
        'ok' => 'Los bloqueos funcionan correctamente',
      ],
      'CheckPythonWrapper' => 
      [
        'fail' => 'No se encontraron pollers del wrapper de Python activos',
        'no_pollers' => 'No se encontraron pollers del wrapper de Python',
        'cron_unread' => 'No se pudieron leer los archivos cron',
        'ok' => 'El wrapper de Python está realizando polling',
        'nodes_down' => 'Algunos nodos del poller no han respondido recientemente',
        'not_detected' => 'La entrada cron del wrapper de Python no está presente',
      ],
      'CheckRedis' => 
      [
        'bad_driver' => 'Usando :driver para bloqueo, debería establecer CACHE_STORE=redis',
        'ok' => 'Redis funciona correctamente',
        'unavailable' => 'Redis no está disponible',
      ],
    ],
  ],
];
