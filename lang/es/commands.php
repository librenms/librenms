<?php

return [
  'errors' => 
  [
    'db_connect' => 'Error al conectar con la base de datos. Verifique que el servicio de base de datos esté en ejecución y revise la configuración de conexión.',
    'db_auth' => 'Error al conectar con la base de datos. Verifique las credenciales: :error',
    'no_devices' => 'No se encontraron dispositivos que coincidan con la especificación dada',
    'no_new_devices' => 'No hay nuevos dispositivos',
  ],
  'config:clear' => 
  [
    'description' => 'Limpiar caché de configuración. Esto permitirá que cualquier cambio realizado desde la última carga completa de configuración se refleje en la configuración actual.',
  ],
  'config:get' => 
  [
    'description' => 'Obtener valor de configuración',
    'arguments' => 
    [
      'setting' => 'Configuración de la que obtener el valor en notación de puntos (ejemplo: snmp.community.0)',
    ],
    'options' => 
    [
      'dump' => 'Imprimir toda la configuración como JSON',
    ],
  ],
  'config:list' => 
  [
    'description' => 'Listar y buscar configuraciones',
    'arguments' => 
    [
      'search' => 'Buscar una configuración por nombre o descripción',
    ],
    'not_found' => 'No se encontraron configuraciones que coincidan con \':search\'',
  ],
  'config:set' => 
  [
    'description' => 'Establecer valor de configuración (o eliminar)',
    'arguments' => 
    [
      'setting' => 'Configuración a establecer en notación de puntos (ejemplo: snmp.community.0). Para agregar a un array, agregue .+ al final',
      'value' => 'Valor a establecer; si se omite, se eliminará la configuración',
    ],
    'options' => 
    [
      'ignore-checks' => 'Ignorar todas las verificaciones de seguridad',
    ],
    'confirm' => '¿Restablecer :setting al valor predeterminado?',
    'forget_from' => '¿Olvidar :path de :parent?',
    'errors' => 
    [
      'append' => 'No se puede agregar a una configuración que no es un array',
      'failed' => 'Error al establecer :setting',
      'invalid' => 'Esta no es una configuración válida. Por favor verifique su entrada',
      'invalid_os' => 'El OS especificado (:os) no existe',
      'nodb' => 'La base de datos no está conectada',
      'no-validation' => 'No se puede establecer :setting, falta la definición de validación.',
    ],
  ],
  'db:seed' => 
  [
    'existing_config' => 'La base de datos contiene configuraciones existentes. ¿Continuar?',
  ],
  'dev:check' => 
  [
    'description' => 'Verificaciones de código de LibreNMS. Ejecutar sin opciones ejecuta todas las verificaciones',
    'arguments' => 
    [
      'check' => 'Ejecutar la verificación especificada :checks',
    ],
    'options' => 
    [
      'commands' => 'Solo imprimir los comandos que se ejecutarían, sin verificar',
      'db' => 'Ejecutar pruebas unitarias que requieren conexión a base de datos',
      'fail-fast' => 'Detener las verificaciones cuando se encuentre algún error',
      'full' => 'Ejecutar verificaciones completas ignorando el filtrado de archivos modificados',
      'module' => 'Módulo específico para ejecutar pruebas. Implica unit, --db, --snmpsim',
      'os' => 'OS específico para ejecutar pruebas. Puede ser regex o lista separada por comas. Implica unit, --db, --snmpsim',
      'os-modules-only' => 'Omitir prueba de detección de OS al especificar un OS específico. Acelera el tiempo de prueba para cambios no relacionados con detección.',
      'quiet' => 'Ocultar salida a menos que haya un error',
      'snmpsim' => 'Usar snmpsim para pruebas unitarias',
    ],
  ],
  'dev:simulate' => 
  [
    'description' => 'Simular dispositivos usando datos de prueba',
    'arguments' => 
    [
      'file' => 'Nombre del archivo (solo nombre base) del archivo snmprec a actualizar o agregar a LibreNMS. Si no se especifica el archivo, no se agregará ni actualizará ningún dispositivo.',
    ],
    'options' => 
    [
      'multiple' => 'Usar nombre de comunidad para hostname en lugar de snmpsim',
      'remove' => 'Eliminar el dispositivo al detener',
    ],
    'added' => 'Dispositivo :hostname (:id) agregado',
    'exit' => 'Ctrl-C para detener',
    'removed' => 'Dispositivo :id eliminado',
    'updated' => 'Dispositivo :hostname (:id) actualizado',
    'setup' => 'Configurando venv de snmpsim en :dir',
  ],
  'device:add' => 
  [
    'description' => 'Agregar un nuevo dispositivo',
    'arguments' => 
    [
      'device spec' => 'Hostname o IP a agregar',
    ],
    'options' => 
    [
      'v1' => 'Usar SNMP v1',
      'v2c' => 'Usar SNMP v2c',
      'v3' => 'Usar SNMP v3',
      'display-name' => 'Cadena de texto para mostrar como nombre del dispositivo, por defecto es el hostname.
Puede ser una plantilla simple usando reemplazos: {{ $hostname }}, {{ $sysName }}, {{ $sysName_fallback }}, {{ $ip }}',
      'force' => 'Agregar el dispositivo directamente, sin verificaciones de seguridad',
      'group' => 'Grupo de poller (para polling distribuido)',
      'ping-fallback' => 'Agregar el dispositivo como solo ping si no responde a SNMP',
      'port-association-mode' => 'Establece cómo se mapean los puertos. ifName es recomendado para Linux/Unix',
      'community' => 'Comunidad SNMP v1 o v2',
      'transport' => 'Transporte para conectarse al dispositivo',
      'port' => 'Puerto de transporte SNMP',
      'security-name' => 'Nombre de usuario de seguridad SNMPv3',
      'auth-password' => 'Contraseña de autenticación SNMPv3',
      'auth-protocol' => 'Protocolo de autenticación SNMPv3',
      'privacy-protocol' => 'Protocolo de privacidad SNMPv3',
      'privacy-password' => 'Contraseña de privacidad SNMPv3',
      'ping-only' => 'Agregar un dispositivo solo ping',
      'os' => 'Solo ping: especificar OS',
      'hardware' => 'Solo ping: especificar hardware',
      'sysName' => 'Solo ping: especificar sysName',
    ],
    'validation-errors' => 
    [
      'port.between' => 'El puerto debe estar entre 1 y 65535',
      'poller-group.in' => 'El grupo de poller especificado no existe',
    ],
    'messages' => 
    [
      'save_failed' => 'Error al guardar el dispositivo :hostname',
      'try_force' => 'Puede intentar con la opción --force para omitir las verificaciones de seguridad',
      'added' => 'Dispositivo :hostname (:device_id) agregado',
    ],
  ],
  'device:discover' => 
  [
    'description' => 'Descubrir información sobre dispositivos existentes, define qué se sondará',
    'arguments' => 
    [
      'device spec' => 'Especificación del dispositivo a descubrir: device_id, hostname, comodín (*), odd, even, all',
    ],
    'options' => 
    [
      'modules' => 'Especificar módulo(s) a ejecutar. Se pueden agregar submódulos con /. Se permiten múltiples valores.',
      'os' => 'Descubrir solo dispositivos con el sistema operativo especificado',
      'type' => 'Descubrir solo dispositivos con el tipo especificado',
    ],
    'errors' => 
    [
      'none_up' => 'El dispositivo estaba caído, no se pudo descubrir.|Todos los dispositivos estaban caídos, no se pudo descubrir.',
      'none_actioned' => 'No se descubrió ningún dispositivo.',
    ],
    'actioned' => ':count dispositivos descubiertos en :time',
    'starting' => 'Iniciando descubrimiento:',
  ],
  'device:ping' => 
  [
    'description' => 'Hacer ping al dispositivo y registrar datos de respuesta',
    'arguments' => 
    [
      'device spec' => 'Dispositivo al que hacer ping: <ID del dispositivo>, <Hostname/IP>, all, fast ("fast" hará ping a todos los dispositivos y actualizará gráficas y estado)',
    ],
    'options' => 
    [
      'groups' => 'ID(s) de grupo al que hacer ping. Especifique múltiples veces para múltiples grupos. (solo válido con fast)',
    ],
    'errors' => 
    [
      'groups_without_fast' => 'La opción --groups (-g) solo es compatible con la especificación de dispositivo "fast".',
    ],
  ],
  'device:poll' => 
  [
    'description' => 'Sondear datos de dispositivo(s) según lo definido por el descubrimiento',
    'arguments' => 
    [
      'device spec' => 'Especificación del dispositivo a sondear: device_id, hostname, comodín (*), odd, even, all',
    ],
    'options' => 
    [
      'modules' => 'Especificar módulo único a ejecutar. Separar módulos con comas, los submódulos se pueden agregar con /',
      'no-data' => 'No actualizar almacenes de datos (RRD, InfluxDB, etc.)',
      'os' => 'Sondear solo dispositivos con el sistema operativo especificado',
      'type' => 'Sondear solo dispositivos con el tipo especificado',
    ],
    'errors' => 
    [
      'none_up' => 'El dispositivo estaba caído, no se pudo sondear.|Todos los dispositivos estaban caídos, no se pudo sondear.',
      'none_actioned' => 'No se sondeó ningún dispositivo.',
    ],
    'actioned' => ':count dispositivos sondeados en :time',
    'starting' => 'Iniciando ciclo de sondeo:',
  ],
  'device:remove' => 
  [
    'doesnt_exists' => 'No existe tal dispositivo: :device',
  ],
  'key:rotate' => 
  [
    'description' => 'Rotar APP_KEY: descifra todos los datos cifrados con la clave antigua y los almacena con la nueva clave en APP_KEY.',
    'arguments' => 
    [
      'old_key' => 'La APP_KEY antigua que es válida para los datos cifrados',
    ],
    'options' => 
    [
      'generate-new-key' => 'Si no tiene la nueva clave en .env, usa la APP_KEY de .env para descifrar datos y genera una nueva clave estableciéndola en .env',
      'forgot-key' => 'Si no tiene la clave antigua, debe eliminar todos los datos cifrados para poder continuar usando ciertas funciones de LibreNMS',
    ],
    'destroy' => '¿Destruir todos los datos de configuración cifrados?',
    'destroy_confirm' => '¡Solo destruya todos los datos cifrados si no puede encontrar la APP_KEY antigua!',
    'cleared-cache' => 'La configuración estaba en caché, se limpió la caché para asegurar que APP_KEY sea correcta. Por favor ejecute nuevamente lnms key:rotate',
    'backup_keys' => '¡Documente AMBAS claves! En caso de que algo salga mal, establezca la nueva clave en .env y use la clave antigua como argumento de este comando',
    'backup_key' => '¡Documente esta clave! Esta clave es requerida para acceder a los datos cifrados',
    'backups' => 'Este comando podría causar pérdida irreversible de datos e invalidará todas las sesiones de navegador. Asegúrese de tener copias de seguridad.',
    'confirm' => 'Tengo copias de seguridad y deseo continuar',
    'decrypt-failed' => 'Error al descifrar :item, omitiendo',
    'failed' => 'Error al descifrar elemento(s). Establezca la nueva clave como APP_KEY y ejecute esto nuevamente con la clave antigua como argumento.',
    'current_key' => 'APP_KEY actual: :key',
    'new_key' => 'Nueva APP_KEY: :key',
    'old_key' => 'APP_KEY antigua: :key',
    'save_key' => '¿Guardar nueva clave en .env?',
    'success' => '¡Claves rotadas exitosamente!',
    'validation-errors' => 
    [
      'not_in' => ':attribute no debe coincidir con la APP_KEY actual',
      'required' => 'Se requiere la clave antigua o --generate-new-key.',
    ],
  ],
  'lnms' => 
  [
    'validation-errors' => 
    [
      'optionValue' => ':option seleccionado no es válido. Debe ser uno de: :values',
    ],
  ],
  'maintenance:cleanup-database' => 
  [
    'description' => 'Limpieza de base de datos de elementos huérfanos.',
  ],
  'maintenance:cleanup-networks' => 
  [
    'delete' => 'Eliminando :count redes no utilizadas',
  ],
  'maintenance:fetch-ouis' => 
  [
    'description' => 'Obtener OUIs de MAC y almacenarlos en caché para mostrar nombres de fabricantes para direcciones MAC',
    'options' => 
    [
      'force' => 'Ignorar cualquier configuración o bloqueo que impida ejecutar el comando',
      'wait' => 'Esperar un tiempo aleatorio, usado por el planificador para evitar sobrecarga del servidor',
    ],
    'disabled' => 'Integración de MAC OUI deshabilitada (:setting)',
    'enable_question' => '¿Habilitar integración de MAC OUI y la obtención programada?',
    'recently_fetched' => 'La base de datos de MAC OUI fue obtenida recientemente, omitiendo actualización.',
    'waiting' => 'Esperando :minutes minuto antes de intentar actualización de MAC OUI|Esperando :minutes minutos antes de intentar actualización de MAC OUI',
    'starting' => 'Almacenando MAC OUI en la base de datos',
    'downloading' => 'Descargando',
    'processing' => 'Procesando CSV',
    'saving' => 'Guardando resultados',
    'success' => 'Mapeos OUI/Fabricante actualizados exitosamente. :count OUI modificado|Actualizados exitosamente. :count OUIs modificados',
    'error' => 'Error procesando MAC OUI:',
    'vendor_update' => 'Agregando OUI :oui para :vendor',
  ],
  'maintenance:rrd-step' => 
  [
    'description' => 'Convertir archivos RRD para que coincidan con el paso y latido configurados',
    'arguments' => 
    [
      'device' => 'Hostname, ID de dispositivo, o all',
    ],
    'options' => 
    [
      'confirm' => 'Confirmar que ha respaldado sus archivos rrd.',
    ],
    'errors' => 
    [
      'invalid' => 'Hostname o ID de dispositivo inválido especificado',
    ],
    'confirm_backup' => 'Antes de continuar, confirme que ha respaldado sus archivos rrd.',
    'mismatched_heartbeat' => ':file: Latido no coincide. :ds != :hb',
    'skipping' => 'Omitiendo :file, el paso ya es :step.',
    'converting' => 'Convirtiendo :file:',
    'summary' => 'Convertidos: :converted  Fallidos: :failed  Omitidos: :skipped',
  ],
  'maintenance:cleanup-syslog' => 
  [
    'description' => 'Limpiar entradas de syslog más antiguas que un número específico de días',
    'arguments' => 
    [
      'days' => 'Número de días a conservar entradas de syslog (predeterminado: valor configurado en syslog_purge)',
    ],
    'bad_days_input' => 'Los días deben ser numéricos',
    'bad_days_setting' => 'Limpieza de syslog deshabilitada debido a configuración inválida de syslog_purge',
    'delete' => 'Entradas de syslog anteriores a :days días eliminadas (:count filas)',
    'disabled' => 'Limpieza de syslog deshabilitada, días <= 0',
  ],
  'maintenance:discover-ssl-certificates' => 
  [
    'description' => 'Descubrir certificados SSL en dispositivos (puerto HTTPS 443)',
    'options' => 
    [
      'device' => 'Especificación de dispositivo: device_id, hostname, o all',
    ],
    'no_devices' => 'No se encontraron dispositivos',
    'summary' => 'Creados: :created, Actualizados: :updated, Fallidos: :failed',
  ],
  'maintenance:refresh-ssl-certificates' => 
  [
    'description' => 'Actualizar datos de certificados SSL almacenados',
    'options' => 
    [
      'id' => 'ID del certificado a actualizar (omitir para actualizar todos los habilitados)',
    ],
    'none' => 'No hay certificados habilitados para actualizar',
    'summary' => 'Actualizados: :refreshed, Fallidos: :failed',
  ],
  'plugin:disable' => 
  [
    'description' => 'Deshabilitar todos los plugins con el nombre dado',
    'arguments' => 
    [
      'plugin' => 'El nombre del plugin a deshabilitar o "all" para deshabilitar todos los plugins',
    ],
    'already_disabled' => 'El plugin ya está deshabilitado',
    'disabled' => ':count plugin deshabilitado|:count plugins deshabilitados',
    'failed' => 'Error al deshabilitar plugin(s)',
  ],
  'plugin:enable' => 
  [
    'description' => 'Habilitar el plugin más reciente con el nombre dado',
    'arguments' => 
    [
      'plugin' => 'El nombre del plugin a habilitar o "all" para habilitar todos los plugins',
    ],
    'already_enabled' => 'El plugin ya está habilitado',
    'enabled' => ':count plugin habilitado|:count plugins habilitados',
    'failed' => 'Error al habilitar plugin(s)',
  ],
  'port:tune' => 
  [
    'description' => 'Ajustar archivos RRD de puertos para limitar la tasa de transferencia máxima según ifSpeed',
    'arguments' => 
    [
      'device spec' => 'Especificación del dispositivo a ajustar: device_id, hostname, comodín (*), odd, even, all',
      'ifname' => 'ifName del puerto a coincidir, puede usar all o * como comodín',
    ],
    'device' => 'Dispositivo :device:',
    'port' => 'Ajustando puerto :port',
  ],
  'report:devices' => 
  [
    'description' => 'Imprimir datos de dispositivos',
    'columns' => 'Columnas de base de datos:',
    'synthetic' => 'Campos adicionales:',
    'counts' => 'Conteos de relaciones:',
    'arguments' => 
    [
      'device spec' => 'Especificación del dispositivo a sondear: device_id, hostname, comodín (*), odd, even, all',
    ],
    'options' => 
    [
      'list-fields' => 'Imprimir una lista de campos válidos',
      'fields' => 'Lista de campos separados por comas a mostrar. Opciones válidas: nombres de columnas de dispositivos de la base de datos, conteos de relaciones (ports_count) y/o displayName. No se usa para salida JSON.',
      'output' => 'Formato de salida para mostrar los datos :types',
      'no-header' => 'No agregar el encabezado',
      'relationships' => 'Lista de relaciones separadas por comas a incluir. Solo se usa para salida JSON.',
      'list-relationships' => 'Imprimir lista/descripción de relaciones',
      'all-relationships' => 'Incluir todas las relaciones. -r, --relationships tiene precedencia.',
      'devices-as-array' => 'Devolver la salida como array JSON en lugar de una entrada JSON por dispositivo por línea',
    ],
  ],
  'smokeping:generate' => 
  [
    'args-nonsense' => 'Use uno de --probes o --targets',
    'config-insufficient' => 'Para generar una configuración de smokeping, debe tener configurados "smokeping.probes", "fping" y "fping6"',
    'dns-fail' => 'no era resoluble y fue omitido de la configuración',
    'description' => 'Generar una configuración adecuada para usar con smokeping',
    'header-first' => 'Este archivo fue generado automáticamente por "lnms smokeping:generate',
    'header-second' => 'Los cambios locales pueden ser sobreescritos sin aviso ni copias de seguridad',
    'header-third' => 'Para más información vea https://docs.librenms.org/Extensions/Smokeping/"',
    'no-devices' => 'No se encontraron dispositivos elegibles - los dispositivos no deben estar deshabilitados.',
    'no-probes' => 'Se requiere al menos un probe.',
    'options' => 
    [
      'probes' => 'Generar lista de probes - usado para dividir la configuración de smokeping en múltiples archivos. Conflicta con "--targets"',
      'targets' => 'Generar la lista de objetivos - usado para dividir la configuración de smokeping en múltiples archivos. Conflicta con "--probes"',
      'no-header' => 'No agregar el comentario de encabezado al inicio del archivo generado',
      'no-dns' => 'Omitir búsquedas DNS',
      'single-process' => 'Usar solo un proceso para smokeping',
      'compat' => '[obsoleto] Imitar el comportamiento de gen_smokeping.php',
    ],
  ],
  'snmp:fetch' => 
  [
    'description' => 'Ejecutar consulta SNMP contra un dispositivo',
    'arguments' => 
    [
      'device spec' => 'Especificación del dispositivo a sondear: device_id, hostname, comodín (*), odd, even, all',
      'oid(s)' => 'Uno o más OIDs SNMP a obtener. Deben estar en formato MIB::oid o OID numérico',
    ],
    'failed' => '¡El comando SNMP falló!',
    'numeric' => 'Numérico',
    'oid' => 'OID',
    'options' => 
    [
      'output' => 'Especificar el formato de salida :formats',
      'numeric' => 'OIDs numéricos',
      'depth' => 'Profundidad para agrupar la tabla SNMP. Generalmente el mismo número que los elementos en el índice de la tabla',
    ],
    'not_found' => 'Dispositivo no encontrado',
    'textual' => 'Textual',
    'value' => 'Valor',
  ],
  'translation:generate' => 
  [
    'description' => 'Generar archivos de idioma JSON actualizados para uso en el frontend web',
  ],
  'user:add' => 
  [
    'description' => 'Agregar un usuario local; solo puede iniciar sesión con este usuario si la autenticación está configurada como mysql',
    'arguments' => 
    [
      'username' => 'El nombre de usuario con el que el usuario iniciará sesión',
    ],
    'options' => 
    [
      'descr' => 'Descripción del usuario',
      'email' => 'Correo electrónico para el usuario',
      'password' => 'Contraseña del usuario; si no se proporciona, se le pedirá que la ingrese',
      'full-name' => 'Nombre completo del usuario',
      'role' => 'Asignar al usuario el rol deseado :roles',
    ],
    'form' => 
    [
      'username' => 'Nombre de usuario',
      'password' => 'Contraseña',
      'roles' => 'Seleccionar rol(es) de usuario',
      'email' => 'Correo electrónico (opcional)',
      'full-name' => 'Nombre completo (opcional)',
      'descr' => 'Descripción (opcional)',
    ],
    'success' => 'Usuario agregado exitosamente: :username',
    'wrong-auth' => 'Advertencia! No podrá iniciar sesión con este usuario porque no está usando autenticación MySQL',
  ],
];
