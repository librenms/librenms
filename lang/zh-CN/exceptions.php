<?php

return [
    'database_connect' => [
        'title' => '连接数据库错误',
    ],
    'database_inconsistent' => [
        'title' => '数据库不一致',
        'header' => '在数据库错误期间发现数据库不一致，请修复后继续。',
    ],
    'dusk_unsafe' => [
        'title' => '在生产环境中运行Dusk不安全',
        'message' => '运行":command"以移除Dusk，或者如果您是开发者，请设置适当的APP_ENV',
    ],
    'file_write_failed' => [
        'title' => '错误：无法写入文件',
        'message' => '无法写入文件(:file)。请检查权限以及适用的SELinux/AppArmor设置。',
    ],
    'host_exists' => [
        'hostname_exists' => '设备:hostname已存在',
        'ip_exists' => '无法添加:hostname，已有设备:existing使用此IP:ip',
        'sysname_exists' => '因重复sysName: :sysname，已有设备:hostname',
    ],
    'host_unreachable' => [
        'unpingable' => '无法ping通:hostname (:ip)',
        'unsnmpable' => '无法连接到:hostname，请检查SNMP详情及可达性',
        'unresolvable' => '主机名未解析为IP地址',
        'no_reply_community' => 'SNMP :version: 使用团体名:credentials无响应',
        'no_reply_credentials' => 'SNMP :version: 使用凭据:credentials无响应',
    ],
    'ldap_missing' => [
        'title' => '缺少PHP LDAP支持',
        'message' => 'PHP不支持LDAP，请安装或启用PHP LDAP扩展',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => '最大执行时间超过:seconds秒|最大执行时间超过:seconds秒',
        'message' => '页面加载超出了PHP中配置的最大执行时间。请在php.ini中增加max_execution_time或提升服务器硬件性能',
    ],
    'unserializable_route_cache' => [
        'title' => '由PHP版本不匹配导致的错误',
        'message' => '您的Web服务器运行的PHP版本(:web_version)与CLI版本(:cli_version)不匹配',
    ],
    'snmp_version_unsupported' => [
        'message' => '不支持的SNMP版本":snmpver"，必须是v1、v2c或v3',
    ],
];
