<?php

return [
  'database_connect' => 
  [
    'title' => 'Error al conectar a la base de datos',
  ],
  'database_inconsistent' => 
  [
    'title' => 'Base de datos inconsistente',
    'header' => 'Se encontraron inconsistencias en la base de datos durante un error. Por favor corrija para continuar.',
  ],
  'dusk_unsafe' => 
  [
    'title' => 'No es seguro ejecutar Dusk en producción',
    'message' => 'Ejecute ":command" para eliminar Dusk o, si es desarrollador, configure el APP_ENV apropiado',
  ],
  'file_write_failed' => 
  [
    'title' => 'Error: No se pudo escribir en el archivo',
    'message' => 'Error al escribir en el archivo (:file). Verifique los permisos y SELinux/AppArmor si aplica.',
  ],
  'host_exists' => 
  [
    'hostname_exists' => 'El dispositivo :hostname ya existe',
    'ip_exists' => 'No se puede agregar :hostname, ya existe el dispositivo :existing con esta IP :ip',
    'sysname_exists' => 'Ya existe el dispositivo :hostname por sysName duplicado: :sysname',
  ],
  'host_name_empty' => 'El nombre de host está vacío',
  'host_unreachable' => 
  [
    'unpingable' => 'No se pudo hacer ping a :hostname (:ip)',
    'unsnmpable' => 'No se pudo conectar a :hostname, verifique los datos SNMP y la accesibilidad SNMP',
    'unresolvable' => 'El nombre de host no pudo resolverse a una IP',
    'no_reply_community' => 'SNMP :version: Sin respuesta con comunidad :credentials',
    'no_reply_credentials' => 'SNMP :version: Sin respuesta con credenciales :credentials',
  ],
  'ldap_missing' => 
  [
    'title' => 'Soporte PHP LDAP faltante',
    'message' => 'PHP no soporta LDAP, instale o habilite la extensión PHP LDAP',
  ],
  'maximum_execution_time_exceeded' => 
  [
    'title' => 'Tiempo máximo de ejecución de :seconds segundo excedido|Tiempo máximo de ejecución de :seconds segundos excedido',
    'message' => 'La carga de la página superó el tiempo máximo de ejecución configurado en PHP. Aumente max_execution_time en su php.ini o mejore el hardware del servidor',
  ],
  'unserializable_route_cache' => 
  [
    'title' => 'Error causado por incompatibilidad de versión de PHP',
    'message' => 'La versión de PHP de su servidor web (:web_version) no coincide con la versión CLI (:cli_version)',
  ],
  'snmp_version_unsupported' => 
  [
    'message' => 'Versión SNMP no soportada ":snmpver", debe ser v1, v2c o v3',
  ],
];
