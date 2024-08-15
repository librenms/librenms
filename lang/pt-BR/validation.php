<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linhas de Idioma de Validação
    |--------------------------------------------------------------------------
    |
    | As linhas de idioma a seguir contêm as mensagens de erro padrão usadas
    | pela classe de validador. Algumas dessas regras têm várias versões,
    | como as regras de tamanho. Sinta-se à vontade para ajustar cada uma
    | dessas mensagens aqui.
    |
    */

    'accepted' => 'O campo :attribute deve ser aceito.',
    'accepted_if' => 'O campo :attribute deve ser aceito quando :other for :value.',
    'active_url' => 'O campo :attribute deve ser uma URL válida.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, números, traços e sublinhados.',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e números.',
    'array' => 'O campo :attribute deve ser um array.',
    'ascii' => 'O campo :attribute deve conter apenas caracteres alfanuméricos de um byte e símbolos.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O campo :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação do campo :attribute não coincide.',
    'current_password' => 'A senha está incorreta.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O campo :attribute deve corresponder ao formato :format.',
    'decimal' => 'O campo :attribute deve ter :decimal casas decimais.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other for :value.',
    'different' => 'O campo :attribute e :other devem ser diferentes.',
    'digits' => 'O campo :attribute deve ter :digits dígitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'O campo :attribute possui dimensões de imagem inválidas.',
    'distinct' => 'O campo :attribute possui um valor duplicado.',
    'doesnt_end_with' => 'O campo :attribute não deve terminar com um dos seguintes: :values.',
    'doesnt_start_with' => 'O campo :attribute não deve começar com um dos seguintes: :values.',
    'email' => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'ends_with' => 'O campo :attribute deve terminar com um dos seguintes: :values.',
    'enum' => 'O :attribute selecionado é inválido.',
    'exists' => 'O :attribute selecionado é inválido.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'filled' => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ser maior que :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value itens ou mais.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ser maior ou igual a :value caracteres.',
    ],
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'O :attribute selecionado é inválido.',
    'in_array' => 'O campo :attribute deve existir em :other.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'ip' => 'O campo :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O campo :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O campo :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O campo :attribute deve ser uma string JSON válida.',
    'lowercase' => 'O campo :attribute deve estar em letras minúsculas.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O campo :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ser menor que :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute não deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser menor ou igual a :value kilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute deve ser menor ou igual a :value caracteres.',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereço MAC válido.',
    'max' => [
        'array' => 'O campo :attribute não deve ter mais de :max itens.',
        'file' => 'O campo :attribute não deve ser maior que :max kilobytes.',
        'numeric' => 'O campo :attribute não deve ser maior que :max.',
        'string' => 'O campo :attribute não deve ser maior que :max caracteres.',
    ],
    'max_digits' => 'O campo :attribute não deve ter mais de :max dígitos.',
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
        'file' => 'O campo :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min dígitos.',
    'missing' => 'O campo :attribute deve estar ausente.',
    'missing_if' => 'O campo :attribute deve estar ausente quando :other for :value.',
    'missing_unless' => 'O campo :attribute deve estar ausente, a menos que :other seja :value.',
    'missing_with' => 'O campo :attribute deve estar ausente quando :values estiver presente.',
    'missing_with_all' => 'O campo :attribute deve estar ausente quando :values estiverem presentes.',
    'multiple_of' => 'O campo :attribute deve ser um múltiplo de :value.',
    'not_in' => 'O :attribute selecionado é inválido.',
    'not_regex' => 'O formato do campo :attribute é inválido.',
    'numeric' => 'O campo :attribute deve ser um número.',
    'password' => [
        'letters' => 'O campo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O campo :attribute deve conter pelo menos uma letra maiúscula e uma minúscula.',
        'numbers' => 'O campo :attribute deve conter pelo menos um número.',
        'symbols' => 'O campo :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'O :attribute fornecido apareceu em um vazamento de dados. Escolha um :attribute diferente.',
    ],
    'present' => 'O campo :attribute deve estar presente.',
    'prohibited' => 'O campo :attribute é proibido.',
    'prohibited_if' => 'O campo :attribute é proibido quando :other for :value.',
    'prohibited_unless' => 'O campo :attribute é proibido, a menos que :other esteja em :values.',
    'prohibits' => 'O campo :attribute proíbe :other de estar presente.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'required_array_keys' => 'O campo :attribute deve conter entradas para: :values.',
    'required_if' => 'O campo :attribute é obrigatório quando :other for :value.',
    'required_if_accepted' => 'O campo :attribute é obrigatório quando :other for aceito.',
    'required_unless' => 'O campo :attribute é obrigatório, a menos que :other esteja em :values.',
    'required_with' => 'O campo :attribute é obrigatório quando :values estiver presente.',
    'required_with_all' => 'O campo :attribute é obrigatório quando :values estiverem presentes.',
    'required_without' => 'O campo :attribute é obrigatório quando :values não estiver presente.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum dos :values estiver presente.',
    'same' => 'O campo :attribute e :other devem corresponder.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O campo :attribute deve ter :size kilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'starts_with' => 'O campo :attribute deve começar com um dos seguintes: :values.',
    'string' => 'O campo :attribute deve ser uma string.',
    'timezone' => 'O campo :attribute deve ser um fuso horário válido.',
    'unique' => 'O campo :attribute já foi usado.',
    'uploaded' => 'O campo :attribute falhou ao ser enviado.',
    'uppercase' => 'O campo :attribute deve estar em letras maiúsculas.',
    'url' => 'O campo :attribute deve ser uma URL válida.',
    'ulid' => 'O campo :attribute deve ser um ULID válido.',
    'uuid' => 'O campo :attribute deve ser um UUID válido.',

    // Específico do LibreNMS
    'alpha_space' => 'O campo :attribute pode conter apenas letras, números, sublinhados e espaços.',
    'ip_or_hostname' => 'O campo :attribute deve ser um endereço IP/rede ou nome de host válido.',
    'is_regex' => 'O campo :attribute não é uma expressão regular válida',
    'keys_in' => 'O campo :attribute contém chaves inválidas: :extra. Chaves válidas: :values',

    /*
    |--------------------------------------------------------------------------
    | Linhas de Idioma de Validação Personalizadas
    |--------------------------------------------------------------------------
    |
    | Aqui você pode especificar mensagens de validação personalizadas para atributos usando
    | a convenção "attribute.rule" para nomear as linhas. Isso facilita a
    | especificação de uma linha de idioma personalizada específica para uma determinada regra de atributo.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mensagem-personalizada',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos de Validação Personalizados
    |--------------------------------------------------------------------------
    |
    | As linhas de idioma a seguir são usadas para trocar nosso espaço reservado de atributo
    | por algo mais legível, como "Endereço de E-mail" em vez de "email". Isso simplesmente nos ajuda
    | a tornar nossa mensagem mais expressiva.
    |
    */

    'attributes' => [],

    'results' => [
        'autofix' => 'Tentar corrigir automaticamente',
        'fix' => 'Correção',
        'fixed' => 'Correção concluída, atualize para executar as validações novamente.',
        'fetch_failed' => 'Falha ao buscar resultados de validação',
        'backend_failed' => 'Falha ao carregar dados do backend, verifique o console para erro.',
        'invalid_fixer' => 'Fixador inválido',
        'show_all' => 'Mostrar tudo',
        'show_less' => 'Mostrar menos',
        'validate' => 'Validar',
        'validating' => 'Validando',
    ],
    'validations' => [
        'rrd' => [
            'CheckRrdVersion' => [
                'fail' => 'A versão do rrdtool especificada é mais recente do que a instalada. Config: :config_version Instalado: :installed_version',
                'fix' => 'Comente ou exclua $config[\'rrdtool_version\'] = \':version\'; do seu arquivo config.php',
                'ok' => 'Versão do rrdtool ok',
            ],
            'CheckRrdcachedConnectivity' => [
                'fail_socket' => ':socket não parece existir, teste de conectividade do rrdcached falhou',
                'fail_port' => 'Não é possível conectar ao servidor rrdcached na porta :port',
                'ok' => 'Conectado ao rrdcached',
            ],
            'CheckRrdDirPermissions' => [
                'fail_root' => 'Seu diretório RRD é de propriedade do root, considere mudar para usar um usuário não root',
                'fail_mode' => 'Seu diretório RRD não está configurado para 0775',
                'ok' => 'rrd_dir é gravável',
            ],
        ],
        'database' => [
            'CheckDatabaseTableNamesCase' => [
                'fail' => 'Você tem lower_case_table_names definido como 1 ou true na configuração do mysql.',
                'fix' => 'Defina lower_case_table_names=0 no seu arquivo de configuração mysql na seção [mysqld].',
                'ok' => 'lower_case_table_names está habilitado',
            ],
            'CheckDatabaseServerVersion' => [
                'fail' => 'A versão :server :min é a versão mínima suportada a partir de :date.',
                'fix' => 'Atualize :server para uma versão suportada, sugerida :suggested.',
                'ok' => 'O servidor SQL atende aos requisitos mínimos',
            ],
            'CheckMysqlEngine' => [
                'fail' => 'Algumas tabelas não estão usando o mecanismo InnoDB recomendado, isso pode causar problemas.',
                'tables' => 'Tabelas',
                'ok' => 'O mecanismo MySQL está ótimo',
            ],
            'CheckSqlServerTime' => [
                'fail' => "O tempo entre este servidor e o banco de dados mysql está incorreto\n Hora do Mysql :mysql_time\n Hora do PHP :php_time",
                'ok' => 'Os tempos do MySQL e PHP coincidem',
            ],
            'CheckSchemaVersion' => [
                'fail_outdated' => 'Seu banco de dados está desatualizado!',
                'fail_legacy_outdated' => 'Seu esquema de banco de dados (:current) é mais antigo do que o mais recente (:latest).',
                'fix_legacy_outdated' => 'Execute manualmente ./daily.sh e verifique se há erros.',
                'warn_extra_migrations' => 'Seu esquema de banco de dados tem migrações extras (:migrations). Se você acabou de mudar para a versão estável da versão diária, seu banco de dados está entre lançamentos e isso será resolvido com o próximo lançamento.',
                'warn_legacy_newer' => 'Seu esquema de banco de dados (:current) é mais recente do que o esperado (:latest). Se você acabou de mudar para a versão estável da versão diária, seu banco de dados está entre lançamentos e isso será resolvido com o próximo lançamento.',
                'ok' => 'O esquema do banco de dados está atualizado',
            ],
            'CheckSchemaCollation' => [
                'ok' => 'Collations do banco de dados e das colunas estão corretas',
            ],
        ],
        'distributedpoller' => [
            'CheckDistributedPollerEnabled' => [
                'ok' => 'A configuração de Polling Distribuído está habilitada globalmente',
                'not_enabled' => 'Você não habilitou distributed_poller',
                'not_enabled_globally' => 'Você não habilitou distributed_poller globalmente',
            ],
            'CheckMemcached' => [
                'not_configured_host' => 'Você não configurou distributed_poller_memcached_host',
                'not_configured_port' => 'Você não configurou distributed_poller_memcached_port',
                'could_not_connect' => 'Não foi possível conectar ao servidor memcached',
                'ok' => 'Conexão com memcached está ok',
            ],
            'CheckRrdcached' => [
                'fail' => 'Você não habilitou rrdcached',
            ],
        ],
        'poller' => [
            'CheckActivePoller' => [
                'fail' => 'O Poller não está em execução.  Nenhum poller foi executado nos últimos :interval segundos',
                'both_fail' => 'Tanto o Dispatcher Service quanto o Python Wrapper estiveram ativos recentemente, isso pode causar duplo polling',
                'ok' => 'Pollers ativos encontrados',
            ],
            'CheckDispatcherService' => [
                'fail' => 'Nenhum nó de dispatcher ativo encontrado',
                'ok' => 'Dispatcher Service está habilitado',
                'nodes_down' => 'Alguns nós de dispatcher não fizeram check-in recentemente',
                'not_detected' => 'Dispatcher Service não detectado',
                'warn' => 'O Dispatcher Service foi usado, mas não recentemente',
            ],
            'CheckLocking' => [
                'fail' => 'Problema no servidor de bloqueio: :message',
                'ok' => 'Os bloqueios estão funcionais',
            ],
            'CheckPythonWrapper' => [
                'fail' => 'Nenhum poller de wrapper python ativo encontrado',
                'no_pollers' => 'Nenhum poller de wrapper python encontrado',
                'cron_unread' => 'Não foi possível ler os arquivos cron',
                'ok' => 'O wrapper do poller Python está fazendo polling',
                'nodes_down' => 'Alguns nós de polling não fizeram check-in recentemente',
                'not_detected' => 'A entrada cron do wrapper Python não está presente',
            ],
            'CheckRedis' => [
                'bad_driver' => 'Usando :driver para bloqueio, você deve definir CACHE_DRIVER=redis',
                'ok' => 'Redis está funcional',
                'unavailable' => 'Redis está indisponível',
            ],
        ],
    ],
];
