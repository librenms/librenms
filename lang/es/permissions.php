<?php

return [
  'device' => 
  [
    'title' => 'Dispositivos',
    'viewAll' => 
    [
      'label' => 'Ver todos los dispositivos',
      'description' => 'Ver todos los dispositivos',
    ],
    'view' => 
    [
      'label' => 'Ver detalles del dispositivo',
      'description' => 'Ver dispositivos a los que el usuario tiene acceso',
    ],
    'create' => 
    [
      'label' => 'Agregar dispositivos',
      'description' => 'Agregar nuevos dispositivos a LibreNMS',
    ],
    'update' => 
    [
      'label' => 'Editar dispositivos',
      'description' => 'Modificar configuraciones de dispositivos',
    ],
    'delete' => 
    [
      'label' => 'Eliminar dispositivos',
      'description' => 'Eliminar dispositivos de LibreNMS',
    ],
    'debug' => 
    [
      'label' => 'Depurar dispositivos',
      'description' => 'Ejecutar snmpwalk y otros comandos de depuración en dispositivos',
    ],
    'showConfig' => 
    [
      'label' => 'Mostrar configuración del dispositivo',
      'description' => 'Mostrar la configuración del dispositivo',
    ],
    'updateNotes' => 
    [
      'label' => 'Actualizar notas del dispositivo',
      'description' => 'Actualizar notas del dispositivo',
    ],
  ],
  'alert' => 
  [
    'title' => 'Alertas',
    'viewAll' => 
    [
      'label' => 'Ver todas las alertas',
      'description' => 'Ver todas las alertas',
    ],
    'view' => 
    [
      'label' => 'Ver detalles de alerta',
      'description' => 'Ver alertas de dispositivos a los que el usuario tiene acceso',
    ],
    'detail' => 
    [
      'label' => 'Ver detalles de alerta',
      'description' => 'Ver información detallada de alertas',
    ],
    'update' => 
    [
      'label' => 'Editar alertas',
      'description' => 'Reconocer o modificar alertas',
    ],
    'delete' => 
    [
      'label' => 'Eliminar alertas',
      'description' => 'Eliminar historial de alertas',
    ],
  ],
  'alert-rule' => 
  [
    'title' => 'Reglas de alerta',
    'viewAll' => 
    [
      'label' => 'Ver todas las reglas de alerta',
      'description' => 'Ver todas las reglas de alerta',
    ],
    'view' => 
    [
      'label' => 'Ver regla de alerta',
      'description' => 'Ver detalles de reglas de alerta para dispositivos a los que el usuario tiene acceso',
    ],
    'create' => 
    [
      'label' => 'Crear reglas de alerta',
      'description' => 'Crear nuevas reglas de alerta',
    ],
    'update' => 
    [
      'label' => 'Editar reglas de alerta',
      'description' => 'Modificar reglas de alerta existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar reglas de alerta',
      'description' => 'Eliminar reglas de alerta',
    ],
  ],
  'alert-schedule' => 
  [
    'title' => 'Programas de alerta',
    'view' => 
    [
      'label' => 'Ver programa de alerta',
      'description' => 'Ver detalles del programa de alerta',
    ],
    'create' => 
    [
      'label' => 'Crear programas de alerta',
      'description' => 'Crear nuevos programas de alerta',
    ],
    'update' => 
    [
      'label' => 'Editar programas de alerta',
      'description' => 'Modificar programas de alerta existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar programas de alerta',
      'description' => 'Eliminar programas de alerta',
    ],
  ],
  'alert-template' => 
  [
    'title' => 'Plantillas de alerta',
    'view' => 
    [
      'label' => 'Ver plantillas de alerta',
      'description' => 'Ver plantillas de alerta',
    ],
    'create' => 
    [
      'label' => 'Crear plantillas de alerta',
      'description' => 'Crear nuevas plantillas de alerta',
    ],
    'update' => 
    [
      'label' => 'Editar plantillas de alerta',
      'description' => 'Modificar plantillas de alerta existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar plantillas de alerta',
      'description' => 'Eliminar plantillas de alerta',
    ],
  ],
  'alert-transport' => 
  [
    'title' => 'Transportes de alerta',
    'view' => 
    [
      'label' => 'Ver transportes de alerta',
      'description' => 'Ver transportes de alerta',
    ],
    'create' => 
    [
      'label' => 'Crear transportes de alerta',
      'description' => 'Crear nuevos transportes de alerta',
    ],
    'update' => 
    [
      'label' => 'Editar transportes de alerta',
      'description' => 'Modificar transportes de alerta existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar transportes de alerta',
      'description' => 'Eliminar transportes de alerta',
    ],
  ],
  'api' => 
  [
    'title' => 'Acceso API',
    'access' => 
    [
      'label' => 'Acceso API',
      'description' => 'Acceder a la API REST de LibreNMS',
    ],
  ],
  'application' => 
  [
    'title' => 'Aplicaciones',
    'update' => 
    [
      'label' => 'Actualizar aplicación',
      'description' => 'Actualizar datos de la aplicación',
    ],
  ],
  'auth-log' => 
  [
    'title' => 'Registros de autenticación',
    'view' => 
    [
      'label' => 'Ver registros de autenticación',
      'description' => 'Ver registros de autenticación',
    ],
  ],
  'bill' => 
  [
    'title' => 'Facturas',
    'viewAll' => 
    [
      'label' => 'Ver todas las facturas',
      'description' => 'Ver todos los registros de facturación',
    ],
    'view' => 
    [
      'label' => 'Ver detalles de factura',
      'description' => 'Ver detalles y gráficas de facturación',
    ],
    'create' => 
    [
      'label' => 'Crear facturas',
      'description' => 'Crear nuevos registros de facturación',
    ],
    'update' => 
    [
      'label' => 'Editar facturas',
      'description' => 'Modificar configuraciones de facturación',
    ],
    'delete' => 
    [
      'label' => 'Eliminar facturas',
      'description' => 'Eliminar registros de facturación',
    ],
  ],
  'component' => 
  [
    'title' => 'Componentes',
    'update' => 
    [
      'label' => 'Actualizar componente',
      'description' => 'Actualizar datos del componente',
    ],
  ],
  'custom-map' => 
  [
    'title' => 'Mapas',
    'viewAll' => 
    [
      'label' => 'Ver todos los mapas',
      'description' => 'Ver todos los mapas de red',
    ],
    'view' => 
    [
      'label' => 'Ver mapa',
      'description' => 'Ver mapas de red que contienen dispositivos accesibles',
    ],
    'create' => 
    [
      'label' => 'Crear mapas',
      'description' => 'Crear nuevos mapas de red',
    ],
    'update' => 
    [
      'label' => 'Editar mapas',
      'description' => 'Modificar mapas de red existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar mapas',
      'description' => 'Eliminar mapas de red',
    ],
  ],
  'dashboard' => 
  [
    'title' => 'Paneles',
    'copy' => 
    [
      'label' => 'Copiar panel',
      'description' => 'Copiar paneles de otros usuarios',
    ],
  ],
  'device-group' => 
  [
    'title' => 'Grupos de dispositivos',
    'viewAll' => 
    [
      'label' => 'Ver todos los grupos de dispositivos',
      'description' => 'Ver todos los grupos de dispositivos',
    ],
    'view' => 
    [
      'label' => 'Ver grupo de dispositivos',
      'description' => 'Ver grupos de dispositivos con dispositivos accesibles',
    ],
    'create' => 
    [
      'label' => 'Crear grupos de dispositivos',
      'description' => 'Crear nuevos grupos de dispositivos',
    ],
    'update' => 
    [
      'label' => 'Editar grupos de dispositivos',
      'description' => 'Modificar grupos de dispositivos existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar grupos de dispositivos',
      'description' => 'Eliminar grupos de dispositivos',
    ],
  ],
  'link' => 
  [
    'title' => 'Enlaces',
    'viewAll' => 
    [
      'label' => 'Ver todos los enlaces',
      'description' => 'Ver información de enlaces de red',
    ],
  ],
  'location' => 
  [
    'title' => 'Ubicaciones',
    'viewAll' => 
    [
      'label' => 'Ver todas las ubicaciones',
      'description' => 'Ver todas las ubicaciones',
    ],
    'view' => 
    [
      'label' => 'Ver ubicación',
      'description' => 'Ver ubicaciones relacionadas a dispositivos accesibles',
    ],
    'create' => 
    [
      'label' => 'Crear ubicaciones',
      'description' => 'Crear nuevas ubicaciones',
    ],
    'update' => 
    [
      'label' => 'Editar ubicaciones',
      'description' => 'Modificar ubicaciones existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar ubicaciones',
      'description' => 'Eliminar ubicaciones',
    ],
  ],
  'mempool' => 
  [
    'title' => 'Grupos de memoria',
    'update' => 
    [
      'label' => 'Actualizar grupo de memoria',
      'description' => 'Actualizar datos del grupo de memoria',
    ],
  ],
  'notification' => 
  [
    'title' => 'Notificaciones',
    'create' => 
    [
      'label' => 'Crear notificaciones',
      'description' => 'Crear nuevas notificaciones',
    ],
    'update' => 
    [
      'label' => 'Editar notificaciones',
      'description' => 'Modificar notificaciones existentes',
    ],
  ],
  'oxidized' => 
  [
    'title' => 'Oxidized',
    'view' => 
    [
      'label' => 'Ver Oxidized',
      'description' => 'Ver respaldos de configuración de dispositivos',
    ],
    'refresh' => 
    [
      'label' => 'Actualizar Oxidized',
      'description' => 'Activar re-obtención de configuración de un dispositivo',
    ],
    'search' => 
    [
      'label' => 'Buscar en Oxidized',
      'description' => 'Buscar en respaldos de configuración de Oxidized',
    ],
  ],
  'peering-db' => 
  [
    'title' => 'PeeringDB',
    'view' => 
    [
      'label' => 'Ver PeeringDB',
      'description' => 'Ver información de PeeringDB',
    ],
  ],
  'plugin' => 
  [
    'title' => 'Plugins',
    'admin' => 
    [
      'label' => 'Administración de plugins',
      'description' => 'Gestionar configuraciones y estado de plugins',
    ],
  ],
  'poller' => 
  [
    'title' => 'Pollers',
    'view' => 
    [
      'label' => 'Ver pollers',
      'description' => 'Ver información y estado de pollers',
    ],
    'update' => 
    [
      'label' => 'Editar pollers',
      'description' => 'Modificar configuraciones de pollers',
    ],
    'delete' => 
    [
      'label' => 'Eliminar pollers',
      'description' => 'Eliminar pollers de LibreNMS',
    ],
  ],
  'poller-group' => 
  [
    'title' => 'Grupos de pollers',
    'create' => 
    [
      'label' => 'Crear grupos de pollers',
      'description' => 'Crear nuevos grupos de pollers',
    ],
    'update' => 
    [
      'label' => 'Editar grupos de pollers',
      'description' => 'Modificar grupos de pollers existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar grupos de pollers',
      'description' => 'Eliminar grupos de pollers',
    ],
  ],
  'port' => 
  [
    'title' => 'Puertos',
    'viewAll' => 
    [
      'label' => 'Ver todos los puertos',
      'description' => 'Ver todos los puertos',
    ],
    'view' => 
    [
      'label' => 'Ver detalles del puerto',
      'description' => 'Ver puertos de dispositivos accesibles',
    ],
    'update' => 
    [
      'label' => 'Editar puertos',
      'description' => 'Modificar descripciones y configuraciones de puertos',
    ],
    'delete' => 
    [
      'label' => 'Eliminar puertos',
      'description' => 'Eliminar permanentemente puertos y sus datos',
    ],
  ],
  'port-group' => 
  [
    'title' => 'Grupos de puertos',
    'viewAll' => 
    [
      'label' => 'Ver todos los grupos de puertos',
      'description' => 'Ver todos los grupos de puertos',
    ],
    'view' => 
    [
      'label' => 'Ver grupo de puertos',
      'description' => 'Ver grupos de puertos con puertos accesibles',
    ],
    'create' => 
    [
      'label' => 'Crear grupos de puertos',
      'description' => 'Crear nuevos grupos de puertos',
    ],
    'update' => 
    [
      'label' => 'Editar grupos de puertos',
      'description' => 'Modificar grupos de puertos existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar grupos de puertos',
      'description' => 'Eliminar grupos de puertos',
    ],
  ],
  'processor' => 
  [
    'title' => 'Procesadores',
    'viewAll' => 
    [
      'label' => 'Ver todos los procesadores',
      'description' => 'Ver todos los procesadores',
    ],
    'view' => 
    [
      'label' => 'Ver procesador',
      'description' => 'Ver procesadores de dispositivos accesibles',
    ],
    'update' => 
    [
      'label' => 'Actualizar procesador',
      'description' => 'Actualizar datos del procesador',
    ],
  ],
  'reporting' => 
  [
    'title' => 'Reportes',
    'update' => 
    [
      'label' => 'Actualizar reportes',
      'description' => 'Actualizar configuraciones de reportes',
    ],
  ],
  'role' => 
  [
    'title' => 'Roles',
    'update' => 
    [
      'label' => 'Editar roles',
      'description' => 'Modificar permisos y configuraciones de roles',
    ],
  ],
  'routing' => 
  [
    'title' => 'Enrutamiento',
    'viewAll' => 
    [
      'label' => 'Ver toda la información de enrutamiento',
      'description' => 'Ver toda la información de enrutamiento',
    ],
    'view' => 
    [
      'label' => 'Ver enrutamiento',
      'description' => 'Ver detalles específicos de enrutamiento',
    ],
    'update' => 
    [
      'label' => 'Actualizar enrutamiento',
      'description' => 'Actualizar datos de enrutamiento',
    ],
  ],
  'service' => 
  [
    'title' => 'Servicios',
    'viewAll' => 
    [
      'label' => 'Ver todos los servicios',
      'description' => 'Ver todos los servicios',
    ],
    'view' => 
    [
      'label' => 'Ver servicios',
      'description' => 'Ver servicios de dispositivos accesibles',
    ],
    'create' => 
    [
      'label' => 'Agregar servicios',
      'description' => 'Agregar nuevos servicios a dispositivos',
    ],
    'update' => 
    [
      'label' => 'Editar servicios',
      'description' => 'Modificar configuraciones de verificación de servicios',
    ],
    'delete' => 
    [
      'label' => 'Eliminar servicios',
      'description' => 'Eliminar servicios de dispositivos',
    ],
  ],
  'service-template' => 
  [
    'title' => 'Plantillas de servicio',
    'view' => 
    [
      'label' => 'Ver plantillas de servicio',
      'description' => 'Ver plantillas de servicio',
    ],
    'create' => 
    [
      'label' => 'Crear plantillas de servicio',
      'description' => 'Crear nuevas plantillas de servicio',
    ],
    'update' => 
    [
      'label' => 'Editar plantillas de servicio',
      'description' => 'Modificar plantillas de servicio existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar plantillas de servicio',
      'description' => 'Eliminar plantillas de servicio',
    ],
  ],
  'settings' => 
  [
    'title' => 'Configuración',
    'view' => 
    [
      'label' => 'Ver configuración',
      'description' => 'Ver configuración global de LibreNMS',
    ],
    'update' => 
    [
      'label' => 'Editar configuración',
      'description' => 'Modificar configuración global de LibreNMS',
    ],
  ],
  'syslog' => 
  [
    'title' => 'Syslog',
    'delete' => 
    [
      'label' => 'Eliminar Syslog',
      'description' => 'Eliminar historial de syslog',
    ],
  ],
  'user' => 
  [
    'title' => 'Usuarios',
    'view' => 
    [
      'label' => 'Ver usuario',
      'description' => 'Ver detalles de cuenta de usuario',
    ],
    'create' => 
    [
      'label' => 'Crear usuarios',
      'description' => 'Crear nuevas cuentas de usuario',
    ],
    'update' => 
    [
      'label' => 'Editar usuarios',
      'description' => 'Modificar cuentas de usuario, roles y permisos',
    ],
    'delete' => 
    [
      'label' => 'Eliminar usuarios',
      'description' => 'Eliminar cuentas de usuario',
    ],
    'updatePassword' => 
    [
      'label' => 'Actualizar contraseña',
      'description' => 'Actualizar contraseña de usuario',
    ],
  ],
  'vlan' => 
  [
    'title' => 'VLANs',
    'viewAll' => 
    [
      'label' => 'Ver todas las VLANs',
      'description' => 'Ver toda la información de VLANs',
    ],
  ],
  'vminfo' => 
  [
    'title' => 'Máquinas virtuales',
    'viewAll' => 
    [
      'label' => 'Ver todas las máquinas virtuales',
      'description' => 'Ver toda la información de máquinas virtuales',
    ],
    'view' => 
    [
      'label' => 'Ver máquina virtual',
      'description' => 'Ver detalles de máquinas virtuales de dispositivos accesibles',
    ],
    'update' => 
    [
      'label' => 'Actualizar máquina virtual',
      'description' => 'Actualizar datos de máquina virtual',
    ],
  ],
  'wireless-sensor' => 
  [
    'title' => 'Sensores inalámbricos',
    'update' => 
    [
      'label' => 'Actualizar sensor inalámbrico',
      'description' => 'Actualizar datos de sensor inalámbrico',
    ],
    'delete' => 
    [
      'label' => 'Eliminar sensor inalámbrico',
      'description' => 'Eliminar datos de sensor inalámbrico',
    ],
  ],
  'customoid' => 
  [
    'title' => 'OIDs personalizados',
    'view' => 
    [
      'label' => 'Ver OIDs personalizados',
      'description' => 'Ver datos de OIDs personalizados',
    ],
    'create' => 
    [
      'label' => 'Crear OIDs personalizados',
      'description' => 'Crear nuevos OIDs personalizados',
    ],
    'update' => 
    [
      'label' => 'Editar OIDs personalizados',
      'description' => 'Modificar OIDs personalizados existentes',
    ],
    'delete' => 
    [
      'label' => 'Eliminar OIDs personalizados',
      'description' => 'Eliminar OIDs personalizados',
    ],
  ],
  'rbac' => 
  [
    'title' => 'Roles y permisos',
    'beta_warning_title' => 'Función beta',
    'beta_warning_message' => 'Esta es una función beta. Los permisos podrían no aplicarse correctamente aún. Por favor reporte cualquier problema que encuentre.',
    'manage_users' => 'Gestionar usuarios',
    'manage_roles' => 'Gestionar roles',
    'add_role' => 'Agregar rol',
    'create_role' => 'Crear rol',
    'create_new_role' => 'Crear nuevo rol',
    'edit_role' => 'Editar rol',
    'delete_role' => 'Eliminar rol',
    'role_name' => 'Nombre del rol',
    'permissions' => 'Permisos',
    'actions' => 'Acciones',
    'all_permissions' => 'Todos los permisos',
    'view_all_permissions' => 'Ver todos los permisos',
    'view_permissions' => 'Ver permisos',
    'no_permissions' => 'Sin permisos asignados',
    'confirm_delete' => '¿Está seguro que desea eliminar este rol?',
    'role_name_placeholder' => 'ej., ingeniero-red',
    'search_permissions' => 'Buscar permisos...',
    'select_all' => 'Seleccionar todo',
    'clear_all' => 'Limpiar todo',
    'save_role' => 'Guardar rol',
    'update_role' => 'Actualizar rol',
    'created' => 'Rol :name creado exitosamente',
    'updated' => 'Rol :name actualizado exitosamente',
    'deleted' => 'Rol :name eliminado exitosamente',
    'role_name_regex' => 'Los nombres de rol solo pueden contener letras minúsculas y guiones (-).',
  ],
];
