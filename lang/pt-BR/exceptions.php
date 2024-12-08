<?php

return [
    'database_connect' => [
        'title' => 'Erro ao conectar ao banco de dados',
    ],
    'database_inconsistent' => [
        'title' => 'Banco de dados inconsistente',
        'header' => 'Inconsistências no banco de dados encontradas durante um erro de banco de dados, por favor, corrija para continuar.',
    ],
    'dusk_unsafe' => [
        'title' => 'É inseguro executar Dusk em produção',
        'message' => 'Execute ":command" para remover Dusk ou, se você for um desenvolvedor, defina o APP_ENV apropriado',
    ],
    'file_write_failed' => [
        'title' => 'Erro: Não foi possível escrever no arquivo',
        'message' => 'Falha ao escrever no arquivo (:file). Por favor, verifique as permissões e SELinux/AppArmor, se aplicável.',
    ],
    'host_exists' => [
        'hostname_exists' => 'Dispositivo :hostname já existe',
        'ip_exists' => 'Não é possível adicionar :hostname, já temos o dispositivo :existing com este IP :ip',
        'sysname_exists' => 'Já temos o dispositivo :hostname devido a sysName duplicado: :sysname',
    ],
    'host_unreachable' => [
        'unpingable' => 'Não foi possível pingar :hostname (:ip)',
        'unsnmpable' => 'Não foi possível conectar a :hostname, por favor, verifique os detalhes SNMP e a acessibilidade SNMP',
        'unresolvable' => 'O nome do host não resolveu para IP',
        'no_reply_community' => 'SNMP :version: Sem resposta com a comunidade :credentials',
        'no_reply_credentials' => 'SNMP :version: Sem resposta com as credenciais :credentials',
    ],
    'ldap_missing' => [
        'title' => 'Suporte PHP LDAP ausente',
        'message' => 'PHP não suporta LDAP, por favor, instale ou habilite a extensão PHP LDAP',
    ],
    'maximum_execution_time_exceeded' => [
        'title' => 'Tempo máximo de execução de :seconds segundo excedido|Tempo máximo de execução de :seconds segundos excedido',
        'message' => 'O carregamento da página excedeu o tempo máximo de execução configurado no PHP. Aumente o max_execution_time no seu php.ini ou melhore o hardware do servidor',
    ],
    'unserializable_route_cache' => [
        'title' => 'Erro causado por incompatibilidade de versão do PHP',
        'message' => 'A versão do PHP que o seu servidor web está executando (:web_version) não corresponde à versão do CLI (:cli_version)',
    ],
    'snmp_version_unsupported' => [
        'message' => 'Versão SNMP não suportada ":snmpver", deve ser v1, v2c ou v3',
    ],
];
