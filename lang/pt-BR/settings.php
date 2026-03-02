<?php

return [
    'title' => 'Configurações',
    'readonly' => 'Definido em config.php, remova de config.php para habilitar.',
    'groups' => [
        'alerting' => 'Alertas',
        'api' => 'API',
        'auth' => 'Autenticação',
        'authorization' => 'Autorização',
        'external' => 'Externo',
        'global' => 'Global',
        'os' => 'Sistema Operacional',
        'discovery' => 'Descoberta',
        'graphing' => 'Gráficos',
        'poller' => 'Coletor de Dados',
        'system' => 'Sistema',
        'webui' => 'Interface Web',
    ],
    'sections' => [
        'alerting' => [
            'general' => ['name' => 'Configurações de Alertas'],
            'email' => ['name' => 'Opções de E-mail'],
            'rules' => ['name' => 'Configurações Padrão de Regras de Alerta'],
        ],
        'api' => [
            'cors' => ['name' => 'CORS'],
        ],
        'auth' => [
            'general' => ['name' => 'Configurações de Autenticação'],
            'ad' => ['name' => 'Configurações do Active Directory'],
            'ldap' => ['name' => 'Configurações de LDAP'],
            'radius' => ['name' => 'Configurações do Radius'],
            'socialite' => ['name' => 'Configurações do Socialite'],
            'http' => ['name' => 'Configurações de Autenticação HTTP'],
        ],
        'authorization' => [
            'device-group' => ['name' => 'Configurações de Grupo de Dispositivos'],
        ],
        'discovery' => [
            'general' => ['name' => 'Configurações de Descoberta'],
            'route' => ['name' => 'Módulo de Descoberta de Rotas'],
            'discovery_modules' => ['name' => 'Módulos de Descoberta'],
            'ports' => ['name' => 'Módulo de Portas'],
            'storage' => ['name' => 'Módulo de Armazenamento'],
            'networks' => ['name' => 'Redes'],
        ],
        'external' => [
            'binaries' => ['name' => 'Localização dos Binários'],
            'location' => ['name' => 'Configurações de Localização'],
            'graylog' => ['name' => 'Integração com Graylog'],
            'oxidized' => ['name' => 'Integração com Oxidized'],
            'mac_oui' => ['name' => 'Integração com Mac OUI Lookup'],
            'peeringdb' => ['name' => 'Integração com PeeringDB'],
            'nfsen' => ['name' => 'Integração com NfSen'],
            'unix-agent' => ['name' => 'Integração com Unix-Agent'],
            'smokeping' => ['name' => 'Integração com Smokeping'],
            'snmptrapd' => ['name' => 'Integração com SNMP Traps'],
        ],
        'poller' => [
            'availability' => ['name' => 'Disponibilidade do Dispositivo'],
            'distributed' => ['name' => 'Coleta de Dados Distribuída'],
            'graphite' => ['name' => 'Datastore: Graphite'],
            'influxdb' => ['name' => 'Datastore: InfluxDB'],
            'influxdbv2' => ['name' => 'Datastore: InfluxDBv2'],
            'kafka' => ['name' => 'Datastore: Kafka'],
            'opentsdb' => ['name' => 'Datastore: OpenTSDB'],
            'ping' => ['name' => 'Ping'],
            'prometheus' => ['name' => 'Datastore: Prometheus'],
            'rrdtool' => ['name' => 'Datastore: RRDTool'],
            'snmp' => ['name' => 'SNMP'],
            'dispatcherservice' => ['name' => 'Serviço de Dispatcher'],
            'poller_modules' => ['name' => 'Módulos de Coleta de Dados'],
        ],
        'system' => [
            'cleanup' => ['name' => 'Limpeza'],
            'proxy' => ['name' => 'Proxy'],
            'updates' => ['name' => 'Atualizações'],
            'scheduledtasks' => ['name' => 'Tarefas Agendadas'],
            'server' => ['name' => 'Servidor'],
            'reporting' => ['name' => 'Relatórios'],
        ],
        'webui' => [
            'availability-map' => ['name' => 'Configurações do Mapa de Disponibilidade'],
            'custom-map' => ['name' => 'Configurações de Mapas Customizados'],
            'graph' => ['name' => 'Configurações de Gráficos'],
            'dashboard' => ['name' => 'Configurações do Painel'],
            'port-descr' => ['name' => 'Análise de Descrição de Interfaces'],
            'search' => ['name' => 'Configurações de Pesquisa'],
            'style' => ['name' => 'Estilo'],
            'device' => ['name' => 'Configurações do Dispositivo'],
            'worldmap' => ['name' => 'Configurações do Mapa'],
        ],
    ],
    'settings' => [
        'active_directory' => [
            'users_purge' => [
                'description' => 'Manter usuários inativos por',
                'help' => 'Usuários serão deletados do LibreNMS após esse período de tempo sem fazer login. 0 significa nunca e os usuários serão recriados se o usuário fizer login novamente.',
            ],
        ],
        'addhost_alwayscheckip' => [
            'description' => 'Verificar IP duplicado ao adicionar dispositivos',
            'help' => 'Se um host for adicionado como um endereço IP, ele será verificado para garantir que o IP não esteja presente. Se o IP estiver presente, o host não será adicionado. Se o host for adicionado por nome, essa verificação não é realizada. Se a configuração for verdadeira, os nomes dos hosts serão resolvidos e a verificação também será realizada. Isso ajuda a prevenir hosts duplicados acidentalmente.',
        ],
        'alert_rule' => [
            'acknowledged_alerts' => [
                'description' => 'Alertas Reconhecidos',
                'help' => 'Enviar alertas quando um alerta for reconhecido',
            ],
            'severity' => [
                'description' => 'Gravidade',
                'help' => 'Nível de severidade para um alerta',
            ],
            'max_alerts' => [
                'description' => 'Máximo de Alertas',
                'help' => 'Quantidade de alertas a serem enviados',
            ],
            'delay' => [
                'description' => 'Atraso',
                'help' => 'Atraso antes de um alerta ser enviado',
            ],
            'interval' => [
                'description' => 'Intervalo',
                'help' => 'Intervalo a ser verificado para este alerta',
            ],
            'mute_alerts' => [
                'description' => 'Silenciar Alertas',
                'help' => 'O alerta deve ser visto apenas na interface web',
            ],
            'invert_rule_match' => [
                'description' => 'Inverter Correspondência de Regra',
                'help' => 'Alertar apenas se a regra não corresponder',
            ],
            'recovery_alerts' => [
                'description' => 'Alertas de Recuperação',
                'help' => 'Notificar se o alerta se recuperar',
            ],
            'acknowledgement_alerts' => [
                'description' => 'Alertas de Reconhecimento',
                'help' => 'Notificar se o alerta for reconhecido',
            ],
            'invert_map' => [
                'description' => 'Todos os dispositivos exceto os da lista',
                'help' => 'Alertar apenas para dispositivos que não estão listados',
            ],
        ],
        'alert' => [
            'ack_until_clear' => [
                'description' => 'Reconhecer até que o alerta seja limpo',
                'help' => 'Reconhecer até que o alerta seja limpo',
            ],
            'admins' => [
                'description' => 'Emitir alertas para Administradores (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'default_copy' => [
                'description' => 'Copiar todos os alertas de e-mail para o contato padrão (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'default_if_none' => [
                'description' => 'não pode definir na webui? (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'default_mail' => [
                'description' => 'Contato Padrão (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'default_only' => [
                'description' => 'Enviar alertas apenas para o contato padrão (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'disable' => [
                'description' => 'Desativar Alertas',
                'help' => 'Para a geração de alertas',
            ],
            'acknowledged' => [
                'description' => 'Enviar alertas reconhecidos',
                'help' => 'Notificar se o alerta foi reconhecido',
            ],
            'fixed-contacts' => [
                'description' => 'Desativar alterações de contato para alertas ativos',
                'help' => 'Se VERDADEIRO, quaisquer alterações no sysContact ou e-mails dos usuários não serão honradas enquanto o alerta estiver ativo',
            ],
            'globals' => [
                'description' => 'Emitir alertas para usuários apenas leitura (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'syscontact' => [
                'description' => 'Emitir alertas para sysContact (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
            'transports' => [
                'mail' => [
                    'description' => 'Ativar Alertas por E-mail',
                    'help' => 'Transporte de alertas por e-mail',
                ],
            ],
            'tolerance_window' => [
                'description' => 'Janela de tolerância para o cron',
                'help' => 'Janela de tolerância em segundos',
            ],
            'users' => [
                'description' => 'Emitir alertas para usuários normais (obsoleto)',
                'help' => 'Obsoleto, use o transporte de alerta por e-mail em vez disso.',
            ],
        ],
        'alert_log_purge' => [
            'description' => 'Entradas de log de alerta mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'discovery_on_reboot' => [
            'description' => 'Descoberta ao Reiniciar',
            'help' => 'Realizar descoberta em um dispositivo reiniciado',
        ],
        'allow_duplicate_sysName' => [
            'description' => 'Permitir sysName Duplicado',
            'help' => 'Por padrão, sysNames duplicados são desabilitados para prevenir que um dispositivo com múltiplas interfaces seja adicionado várias vezes',
        ],
        'allow_unauth_graphs' => [
            'description' => 'Permitir acesso aos gráficos sem autenticação',
            'help' => 'Permite que qualquer pessoa acesse os gráficos sem fazer login no sistema',
        ],
        'allow_unauth_graphs_cidr' => [
            'description' => 'Permitir acesso aos gráficos para as redes informadas',
            'help' => 'Permitir acesso aos gráficos sem autenticação somente para as redes informadas (não se aplica quando gráficos sem autenticação está habilitado)',
        ],
        'api' => [
            'cors' => [
                'allowheaders' => [
                    'description' => 'Permitir Cabeçalhos',
                    'help' => 'Define o cabeçalho de resposta Access-Control-Allow-Headers',
                ],
                'allowcredentials' => [
                    'description' => 'Permitir Credenciais',
                    'help' => 'Define o cabeçalho Access-Control-Allow-Credentials',
                ],
                'allowmethods' => [
                    'description' => 'Métodos Permitidos',
                    'help' => 'Corresponde ao método da solicitação.',
                ],
                'enabled' => [
                    'description' => 'Habilitar suporte a CORS para a API',
                    'help' => 'Permite carregar recursos da API a partir de um cliente web',
                ],
                'exposeheaders' => [
                    'description' => 'Expor Cabeçalhos',
                    'help' => 'Define o cabeçalho de resposta Access-Control-Expose-Headers',
                ],
                'maxage' => [
                    'description' => 'Idade Máxima',
                    'help' => 'Define o cabeçalho de resposta Access-Control-Max-Age',
                ],
                'origin' => [
                    'description' => 'Permitir Origens de Solicitação',
                    'help' => 'Corresponde à origem da solicitação. Curingas podem ser usados, por exemplo, *.meudominio.com',
                ],
            ],
        ],
        'apps' => [
            'powerdns-recursor' => [
                'api-key' => [
                    'description' => 'Chave API para PowerDNS Recursor',
                    'help' => 'Chave API para o aplicativo PowerDNS Recursor ao conectar-se diretamente',
                ],
                'https' => [
                    'description' => 'PowerDNS Recursor usa HTTPS?',
                    'help' => 'Usar HTTPS em vez de HTTP para o aplicativo PowerDNS Recursor ao conectar-se diretamente',
                ],
                'port' => [
                    'description' => 'Porta do PowerDNS Recursor',
                    'help' => 'Porta TCP a ser usada para o aplicativo PowerDNS Recursor ao conectar-se diretamente',
                ],
            ],
        ],
        'astext' => [
            'description' => 'Chave para armazenar cache de descrições de sistemas autônomos',
        ],
        'auth' => [
            'allow_get_login' => [
                'description' => 'Permitir Login via GET (Inseguro)',
                'help' => 'Permitir login colocando variáveis de nome de usuário e senha na solicitação GET da URL, útil para sistemas de exibição onde você não pode fazer login interativamente. Isso é considerado inseguro porque a senha será exibida nos logs e logins não são limitados por taxa, o que pode abrir brechas para ataques de força bruta.',
            ],
            'socialite' => [
                'redirect' => [
                    'description' => 'Redirecionar Página de Login',
                    'help' => 'A página de login deve redirecionar imediatamente para o primeiro provedor definido.<br><br>DICAS: Você pode evitar isso adicionando ?redirect=0 na URL',
                ],
                'register' => [
                    'description' => 'Permitir Registro via Provedor',
                ],
                'configs' => [
                    'description' => 'Configurações do Provedor',
                ],
                'scopes' => [
                    'description' => 'Escopos que devem ser incluídos na solicitação de autenticação',
                    'help' => 'Veja https://laravel.com/docs/10.x/socialite#access-scopes',
                ],
            ],
        ],
        'auth_ad_base_dn' => [
            'description' => 'Base DN',
            'help' => 'grupos e usuários devem estar sob este dn. Exemplo: dc=exemplo,dc=com',
        ],
        'auth_ad_check_certificates' => [
            'description' => 'Verificar Certificado',
            'help' => 'Verificar certificados quanto à validade. Alguns servidores usam certificados autoassinados, e, se desativar esta opção, permitirá o uso destes.',
        ],
        'auth_ad_debug' => [
            'description' => 'Depuração',
            'help' => 'Exibir mensagens de erro detalhadas. Não deixe isso ativado em produção, pois pode ocorrer vazamento de dados.',
        ],
        'auth_ad_domain' => [
            'description' => 'Domínio do Active Directory',
            'help' => 'Exemplo de Domínio do Active Directory: exemplo.com',
        ],
        'auth_ad_group_filter' => [
            'description' => 'Filtro de Grupo LDAP',
            'help' => 'Filtro LDAP do Active Directory para selecionar grupos',
        ],
        'auth_ad_groups' => [
            'description' => 'Grupos de Acesso',
            'help' => 'Definir grupos que têm acesso e respectivos níveis',
        ],
        'auth_ad_require_groupmembership' => [
            'description' => 'Exigir Associação a um Grupo',
            'help' => 'Permitir login apenas se os usuários fizerem parte de um grupo definido',
        ],
        'auth_ad_user_filter' => [
            'description' => 'Filtro de Usuário LDAP',
            'help' => 'Filtro LDAP do Active Directory para selecionar usuários',
        ],
        'auth_ad_url' => [
            'description' => 'Servidor(es) do Active Directory',
            'help' => 'Definir servidor(es), separados por espaço. Prefixar com ldaps:// para SSL. Exemplo: ldaps://dc1.exemplo.com ldaps://dc2.exemplo.com',
        ],
        'auth_ldap_attr' => [
            'uid' => [
                'description' => 'Atributo para verificar nome de usuário',
                'help' => 'Atributo usado para identificar usuários pelo nome de usuário',
            ],
        ],
        'auth_ldap_binddn' => [
            'description' => 'Bind DN (substitui o nome de usuário de bind)',
            'help' => 'DN completo do usuário de bind',
        ],
        'auth_ldap_bindpassword' => [
            'description' => 'Senha de Bind',
            'help' => 'Senha para o usuário de bind',
        ],
        'auth_ldap_binduser' => [
            'description' => 'Nome de Usuário de Bind',
            'help' => 'Usado para consultar o servidor LDAP quando nenhum usuário está logado (alertas, API, etc)',
        ],
        'auth_ad_binddn' => [
            'description' => 'Bind DN (substitui o nome de usuário de bind)',
            'help' => 'DN completo do usuário de bind',
        ],
        'auth_ad_bindpassword' => [
            'description' => 'Senha de Bind',
            'help' => 'Senha para o usuário de bind',
        ],
        'auth_ad_binduser' => [
            'description' => 'Nome de Usuário de Bind',
            'help' => 'Usado para consultar o servidor AD quando nenhum usuário está logado (alertas, API, etc)',
        ],
        'auth_ad_starttls' => [
            'description' => 'Usar STARTTLS',
            'help' => 'Usar STARTTLS para proteger a conexão. Alternativa ao LDAPS.',
            'options' => [
                'disabled' => 'Desativado',
                'optional' => 'Opcional',
                'required' => 'Obrigatório',
            ],
        ],
        'auth_ldap_cache_ttl' => [
            'description' => 'Expiração do Cache LDAP',
            'help' => 'Armazena temporariamente os resultados das consultas LDAP. Melhora as velocidades, mas os dados podem estar desatualizados.',
        ],
        'auth_ldap_debug' => [
            'description' => 'Depuração',
            'help' => 'Exibe informações de depuração. Pode expor informações privadas, não deixe habilitado.',
        ],
        'auth_ldap_cacertfile' => [
            'description' => 'Substituir CA Cert TLS do Sistema',
            'help' => 'Usar CA Cert fornecido para LDAPS.',
        ],
        'auth_ldap_ignorecert' => [
            'description' => 'Não exigir Certificado válido',
            'help' => 'Não exigir um Certificado TLS válido para LDAPS.',
        ],
        'auth_ldap_emailattr' => [
            'description' => 'Atributo de E-mail',
        ],
        'auth_ldap_group' => [
            'description' => 'DN para Grupo de Acesso',
            'help' => 'Nome distinto para um grupo que concede acesso normal. Exemplo: cn=nome_grupo,ou=grupos,dc=exemplo,dc=com',
        ],
        'auth_ldap_groupbase' => [
            'description' => 'DN Base de Grupo',
            'help' => 'Nome distinto para pesquisar grupos. Exemplo: ou=grupo,dc=exemplo,dc=com',
        ],
        'auth_ldap_groupmemberattr' => [
            'description' => 'Atributo para Membro do Grupo',
        ],
        'auth_ldap_groupmembertype' => [
            'description' => 'Encontrar membros de grupo por',
            'options' => [
                'username' => 'Nome de Usuário',
                'fulldn' => 'DN Completo (usando prefixo e sufixo)',
                'puredn' => 'Pesquisa de DN (pesquisa usando atributo uid)',
            ],
        ],
        'auth_ldap_groups' => [
            'description' => 'Grupos de Acesso',
            'help' => 'Definir grupos que têm acesso e seus respectivos níveis',
        ],
        'auth_ldap_require_groupmembership' => [
            'description' => 'Verificar associação ao grupo LDAP',
            'help' => 'Executar (ou pular) ldap_compare quando o provedor permitir (ou não) para a ação Comparar.',
        ],
        'auth_ldap_port' => [
            'description' => 'Porta LDAP',
            'help' => 'Porta de conexão aos servidores. Para LDAP deve ser 389, para LDAPS deve ser 636',
        ],
        'auth_ldap_prefix' => [
            'description' => 'Prefixo de usuário',
            'help' => 'Usado para transformar um nome de usuário em um nome distinto',
        ],
        'auth_ldap_server' => [
            'description' => 'Servidor(es) LDAP',
            'help' => 'Definir servidor(es), separados por espaço. Prefixar com ldaps:// para SSL',
        ],
        'auth_ldap_starttls' => [
            'description' => 'Usar STARTTLS',
            'help' => 'Usar STARTTLS para proteger a conexão. Alternativa ao LDAPS.',
            'options' => [
                'disabled' => 'Desativado',
                'optional' => 'Opcional',
                'required' => 'Obrigatório',
            ],
        ],
        'auth_ldap_suffix' => [
            'description' => 'Sufixo de Usuário',
            'help' => 'Usado para transformar um nome de usuário em um nome distinto',
        ],
        'auth_ldap_timeout' => [
            'description' => 'Tempo de espera da conexão',
            'help' => 'Se um ou mais servidores não responderem, tempos de espera mais altos causarão lentidão no acesso. Muito baixo pode causar falhas de conexão em alguns casos',
        ],
        'auth_ldap_uid_attribute' => [
            'description' => 'Atributo único de ID',
            'help' => 'Atributo LDAP para identificar usuários, deve ser numérico',
        ],
        'auth_ldap_userdn' => [
            'description' => 'Usar DN completo do usuário',
            'help' => 'Usa o DN completo do usuário como valor do atributo de membro em um grupo, em vez de membro: nome de usuário usando o prefixo e sufixo. (membro: uid=nome_de_usuário,ou=grupos,dc=dominio,dc=com)',
        ],
        'auth_ldap_userlist_filter' => [
            'description' => 'Filtro de Usuário LDAP Personalizado',
            'help' => 'Filtro LDAP personalizado para limitar o número de respostas se você tiver um diretório LDAP com milhares de usuários.',
        ],
        'auth_ldap_wildcard_ou' => [
            'description' => 'Wildcard OU do usuário',
            'help' => 'Pesquisar usuário correspondente ao nome de usuário independentemente do OU definido no sufixo do usuário. Útil se seus usuários estiverem em diferentes OUs. Nome de usuário de bind, se definido, ainda usa o sufixo do usuário',
        ],
        'auth_ldap_version' => [
            'description' => 'Versão do LDAP',
            'help' => 'Versão do LDAP para usar na comunicação com o servidor. Normalmente, deve ser v3',
            'options' => [
                '2' => '2',
                '3' => '3',
            ],
        ],
        'auth_mechanism' => [
            'description' => 'Método de Autenticação (Cuidado!)',
            'help' => 'Método de autenticação dos usuários. Cuidado, você poderá ficar impossibilitado de fazer login no sistema. Pode ser revertido isso para mysql configurando $config["auth_mechanism"] = "mysql"; em seu config.php',
            'options' => [
                'mysql' => 'MySQL (padrão)',
                'active_directory' => 'Active Directory',
                'ldap' => 'LDAP',
                'radius' => 'Radius',
                'http-auth' => 'Autenticação HTTP',
                'ad-authorization' => 'AD Autenticado Externamente',
                'ldap-authorization' => 'LDAP Autenticado Externamente',
                'sso' => 'Single Sign On',
            ],
        ],
        'auth_remember' => [
            'description' => 'Duração do Lembrar-me',
            'help' => 'Número de dias para manter um usuário logado ao marcar a caixa de seleção Lembrar-me no login',
        ],
        'authlog_purge' => [
            'description' => 'Entradas de log de autenticação mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'peering_descr' => [
            'description' => 'Tipos de Portas de Peering',
            'help' => 'Portas do(s) tipo(s) de descrição listados serão exibidas no menu de portas de peering. Veja a documentação de Análise de Descrição de Interface para mais informações.',
        ],
        'transit_descr' => [
            'description' => 'Tipos de Portas de Trânsito',
            'help' => 'Portas do(s) tipo(s) de descrição listados serão exibidas no menu de portas de trânsito. Veja a documentação de Análise de Descrição de Interface para mais informações',
        ],
        'core_descr' => [
            'description' => 'Tipos de Portas Principais',
            'help' => 'Portas do(s) tipo(s) de descrição listados serão exibidas no menu de portas principais. Veja a documentação de Análise de Descrição de Interface para mais informações',
        ],
        'custom_map' => [
            'background_type' => [
                'description' => 'Tipo de Plano de Fundo',
                'help' => 'Tipo de plano de fundo padrão para novos mapas. Requer um conjunto de dados de plano de fundo',
            ],
            'background_data' => [
                'color' => [
                    'description' => 'Cor de Fundo',
                    'help' => 'Cor inicial para o plano de fundo do mapa',
                ],
                'lat' => [
                    'description' => 'Latitude do Mapa de Fundo',
                    'help' => 'Latitude inicial para o mapa geográfico de fundo',
                ],
                'lng' => [
                    'description' => 'Longitude do Mapa de Fundo',
                    'help' => 'Longitude inicial para o mapa geográfico de fundo',
                ],
                'layer' => [
                    'description' => 'Camada do Mapa de Fundo',
                    'help' => 'Camada inicial do mapa para o mapa geográfico de fundo',
                ],
                'zoom' => [
                    'description' => 'Zoom do Mapa de Fundo',
                    'help' => 'Zoom inicial para o mapa geográfico de fundo',
                ],
            ],
            'edge_font_color' => [
                'description' => 'Cor do Texto',
                'help' => 'Cor padrão da fonte para as etiquetas das conexões',
            ],
            'edge_font_face' => [
                'description' => 'Fonte',
                'help' => 'Fonte padrão para as etiquetas dos links',
            ],
            'edge_font_size' => [
                'description' => 'Tamanho do Texto do Link',
                'help' => 'Tamanho padrão da fonte para as etiquetas dos links',
            ],
            'edge_seperation' => [
                'description' => 'Separação dos Links',
                'help' => 'Separação padrão das conexões para novos mapas',
            ],
            'height' => [
                'description' => 'Altura do Mapa',
                'help' => 'Altura padrão para novos mapas',
            ],
            'node_align' => [
                'description' => 'Alinhamento dos Nós',
                'help' => 'Alinhamento padrão dos nós para novos mapas',
            ],
            'node_background' => [
                'description' => 'Cor de Fundo dos Nós',
                'help' => 'Cor de fundo padrão para as etiquetas dos nós',
            ],
            'node_border' => [
                'description' => 'Borda dos Nós',
                'help' => 'Cor padrão das bordas dos nós',
            ],
            'node_font_color' => [
                'description' => 'Cor do Texto dos Nós',
                'help' => 'Cor padrão da fonte das etiquetas dos nós',
            ],
            'node_font_face' => [
                'description' => 'Fonte dos Nós',
                'help' => 'Fonte padrão para as etiquetas dos nós',
            ],
            'node_font_size' => [
                'description' => 'Tamanho do Texto dos Nós',
                'help' => 'Tamanho padrão da fonte das etiquetas dos nós',
            ],
            'node_size' => [
                'description' => 'Tamanho dos Nós',
                'help' => 'Tamanho padrão dos nós',
            ],
            'node_type' => [
                'description' => 'Tipo de Exibição dos Nós',
                'help' => 'Tipo de exibição padrão para os nós',
            ],
            'reverse_arrows' => [
                'description' => 'Setas de Conexão Reversas',
                'help' => 'Direção padrão das setas. Em direção ao centro (padrão) ou em direção às extremidades',
            ],
            'width' => [
                'description' => 'Largura do Mapa',
                'help' => 'Largura padrão para novos mapas',
            ],
        ],
        'customers_descr' => [
            'description' => 'Tipos de Portas de Clientes',
            'help' => 'Portas do(s) tipo(s) de descrição listados serão exibidas no menu de portas de clientes. Veja a documentação de Análise de Descrição de Interface para mais informações.',
        ],
        'base_url' => [
            'description' => 'URL Específica',
            'help' => 'Isso deve *apenas* ser definido se você quiser *forçar* um determinado nome de host/porta. Isso impedirá que a interface web seja utilizável a partir de qualquer outro nome de host',
        ],
        'discovery_modules' => [
            'arp-table' => [
                'description' => 'Tabela ARP',
            ],
            'applications' => [
                'description' => 'Aplicativos',
            ],
            'bgp-peers' => [
                'description' => 'Pares BGP',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'slas' => [
                'description' => 'Rastreamento de Acordo de Nível de Serviço',
            ],
            'cisco-pw' => [
                'description' => 'Cisco PW',
            ],
            'cisco-vrf-lite' => [
                'description' => 'Cisco VRF Lite',
            ],
            'discovery-arp' => [
                'description' => 'Descoberta ARP',
            ],
            'discovery-protocols' => [
                'description' => 'Protocolos de Descoberta',
            ],
            'entity-physical' => [
                'description' => 'Entidade Física',
            ],
            'entity-state' => [
                'description' => 'Estado da Entidade',
            ],
            'fdb-table' => [
                'description' => 'Tabela FDB',
            ],
            'hr-device' => [
                'description' => 'Dispositivo HR',
            ],
            'ipv4-addresses' => [
                'description' => 'Endereços IPv4',
            ],
            'ipv6-addresses' => [
                'description' => 'Endereços IPv6',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'junose-atm-vp' => [
                'description' => 'Junose ATM VP',
            ],
            'loadbalancers' => [
                'description' => 'Balanceadores de Carga',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mempools' => [
                'description' => 'Pools de Memória',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'os' => [
                'description' => 'Sistema Operacional',
            ],
            'ports' => [
                'description' => 'Portas',
            ],
            'ports-stack' => [
                'description' => 'Pilha de Portas',
            ],
            'processors' => [
                'description' => 'Processadores',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'route' => [
                'description' => 'Rota',
            ],
            'sensors' => [
                'description' => 'Sensores',
            ],

            'services' => [
                'description' => 'Serviços',
            ],
            'storage' => [
                'description' => 'Armazenamento',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'vlans' => [
                'description' => 'VLANs',
            ],
            'vminfo' => [
                'description' => 'Informações de VM do hypervisor',
            ],
            'vrf' => [
                'description' => 'VRF',
            ],
            'wireless' => [
                'description' => 'Wireless',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => 'Suprimentos para Impressora',
            ],
        ],
        'distributed_poller' => [
            'description' => 'Habilitar Coleta de Dados Distribuída (requer configuração adicional)',
            'help' => 'Habilitar coleta de dados distribuída em todo o sistema. Isso é destinado ao compartilhamento de carga, não à coleta remota. Leia a documentação para verificar as etapas de habilitação: https://docs.librenms.org/Extensions/Distributed-Poller/',
        ],
        'default_poller_group' => [
            'description' => 'Grupo de Coleta de Dados Padrão',
            'help' => 'O grupo de coleta padrão que todos os coletores devem pesquisar, se nenhum estiver definido em config.php',
        ],
        'distributed_poller_memcached_host' => [
            'description' => 'Host Memcached',
            'help' => 'O nome do host ou IP para o servidor memcached. Isso é necessário para o poller_wrapper.py e bloqueio de daily.sh.',
        ],
        'distributed_poller_memcached_port' => [
            'description' => 'Porta Memcached',
            'help' => 'A porta para o servidor memcached. O padrão é 11211',
        ],
        'email_auto_tls' => [
            'description' => 'Suporte a Auto TLS',
            'help' => 'Tenta usar TLS antes de recorrer a não criptografado',
        ],
        'email_attach_graphs' => [
            'description' => 'Anexar gráficos',
            'help' => 'Isso gerará um gráfico quando o alerta for acionado e o anexará ao e-mail.',
        ],
        'email_backend' => [
            'description' => 'Método de envio de e-mails',
            'help' => 'O backend a ser usado para enviar e-mails, podendo ser mail, sendmail ou SMTP',
            'options' => [
                'mail' => 'mail',
                'sendmail' => 'sendmail',
                'smtp' => 'SMTP',
            ],
        ],
        'email_from' => [
            'description' => 'Endereço de e-mail do remetente',
            'help' => 'Endereço de e-mail usado para enviar e-mails (remetente)',
        ],
        'email_html' => [
            'description' => 'Usar e-mails em HTML',
            'help' => 'Enviar e-mails em HTML',
        ],
        'email_sendmail_path' => [
            'description' => 'Caminho para o binário sendmail',
        ],
        'email_smtp_auth' => [
            'description' => 'Autenticação SMTP',
            'help' => 'Habilitar isso se o seu servidor SMTP exigir autenticação',
        ],
        'email_smtp_host' => [
            'description' => 'Servidor SMTP',
            'help' => 'IP ou nome DNS do servidor SMTP para enviar e-mails',
        ],
        'email_smtp_password' => [
            'description' => 'Senha de autenticação SMTP',
        ],
        'email_smtp_port' => [
            'description' => 'Configuração de porta SMTP',
        ],
        'email_smtp_secure' => [
            'description' => 'Criptografia',
            'options' => [
                '' => 'Desativado',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ],
        'email_smtp_timeout' => [
            'description' => 'Configuração de tempo de espera SMTP',
        ],
        'email_smtp_username' => [
            'description' => 'Nome de usuário de autenticação SMTP',
        ],
        'email_user' => [
            'description' => 'Nome do remetente',
            'help' => 'Nome usado como parte do endereço do remetente',
        ],
        'eventlog_purge' => [
            'description' => 'Entradas de log de eventos mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'favicon' => [
            'description' => 'Favicon',
            'help' => 'Substitui o favicon padrão.',
        ],
        'fping' => [
            'description' => 'Caminho para fping',
        ],
        'fping6' => [
            'description' => 'Caminho para fping6',
        ],
        'fping_options' => [
            'count' => [
                'description' => 'Contagem de fping',
                'help' => 'O número de pings a serem enviados ao verificar se um host está ativo ou inativo via ICMP',
            ],
            'interval' => [
                'description' => 'Intervalo de fping',
                'help' => 'A quantidade de milissegundos a esperar entre pings',
            ],
            'timeout' => [
                'description' => 'Tempo de espera de fping',
                'help' => 'A quantidade de milissegundos a esperar por uma resposta de eco antes de desistir',
            ],
        ],
        'geoloc' => [
            'api_key' => [
                'description' => 'Chave API do Mecanismo de Mapeamento',
                'help' => 'Chave API de Geocodificação (Obrigatório para funcionar)',
            ],
            'dns' => [
                'description' => 'Usar Registro de Localização DNS',
                'help' => 'Usar Registro LOC do Servidor DNS para obter coordenadas geográficas para o Nome do Host',
            ],
            'engine' => [
                'description' => 'Mecanismo de Mapeamento',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapquest' => 'MapQuest',
                    'bing' => 'Bing Maps',
                    'esri' => 'ESRI ArcGIS',
                ],
            ],
            'latlng' => [
                'description' => 'Tentar Geocodificar Localizações',
                'help' => 'Tentar pesquisar latitude e longitude via API de geocodificação durante a pesquisa',
            ],
            'layer' => [
                'description' => 'Camada Inicial do Mapa',
                'help' => 'Camada inicial do mapa a ser exibida. *Nem todas as camadas estão disponíveis para todos os mecanismos de mapeamento.',
                'options' => [
                    'Streets' => 'Ruas',
                    'Sattelite' => 'Satélite',
                    'Topography' => 'Topografia',
                ],
            ],
        ],
        'graphite' => [
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o Graphite',
            ],
            'host' => [
                'description' => 'Servidor',
                'help' => 'O IP ou nome do host do servidor Graphite para enviar dados',
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'A porta a ser usada para conectar ao servidor Graphite',
            ],
            'prefix' => [
                'description' => 'Prefixo (Opcional)',
                'help' => 'Adicionará o prefixo ao início de todas as métricas. Deve ser alfanumérico separado por pontos',
            ],
        ],
        'graphing' => [
            'availability' => [
                'description' => 'Duração',
                'help' => 'Calcular disponibilidade do dispositivo para as durações listadas. (Durações são definidas em segundos)',
            ],
            'availability_consider_maintenance' => [
                'description' => 'Manutenção programada não afeta disponibilidade',
                'help' => 'Desativa a criação de interrupções e a diminuição da disponibilidade para dispositivos em modo de manutenção.',
            ],
        ],
        'graphs' => [
            'port_speed_zoom' => [
                'description' => 'Ampliar gráficos de porta para a velocidade da porta',
                'help' => 'Ampliar gráficos de porta para que o máximo seja sempre a velocidade da porta, gráficos de porta desativados ampliam para o tráfego',
            ],
        ],
        'graylog' => [
            'base_uri' => [
                'description' => 'URI Base',
                'help' => 'Substitui o URI base no caso de você ter modificado o padrão do Graylog.',
            ],
            'device-page' => [
                'loglevel' => [
                    'description' => 'Nível de Log da Visão Geral do Dispositivo',
                    'help' => 'Define o nível máximo de log exibido na página de visão geral do dispositivo.',
                ],
                'rowCount' => [
                    'description' => 'Contagem de Linhas da Visão Geral do Dispositivo',
                    'help' => 'Define o número de linhas exibidas na página de visão geral do dispositivo.',
                ],
            ],
            'password' => [
                'description' => 'Senha',
                'help' => 'Senha para acessar a API do Graylog.',
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'A porta usada para acessar a API do Graylog. Se não for fornecida, será 80 para http e 443 para https.',
            ],
            'server' => [
                'description' => 'Servidor',
                'help' => 'O IP ou nome do host do endpoint da API do servidor Graylog.',
            ],
            'timezone' => [
                'description' => 'Fuso Horário de Exibição',
                'help' => 'Os horários do Graylog são armazenados em GMT, essa configuração mudará o fuso horário exibido. O valor deve ser um fuso horário PHP válido.',
            ],
            'username' => [
                'description' => 'Nome de Usuário',
                'help' => 'Nome de usuário para acessar a API do Graylog.',
            ],
            'version' => [
                'description' => 'Versão',
                'help' => 'Isso é usado para criar automaticamente o URI base para a API do Graylog. Se você tiver modificado o URI da API do padrão, defina isso para "other" e especifique seu URI base.',
            ],
            'query' => [
                'field' => [
                    'description' => 'Campo de consulta da API',
                    'help' => 'Altera o campo padrão para consultar a API do Graylog.',
                ],
            ],
        ],
        'html' => [
            'device' => [
                'primary_link' => [
                    'description' => 'Link Principal do Menu Suspenso',
                    'help' => 'Define o link principal no menu suspenso do dispositivo',
                ],
            ],
        ],
        'http_auth_header' => [
            'description' => 'Nome do campo contendo o nome de usuário',
            'help' => 'Pode ser um campo ENV ou HTTP-header como REMOTE_USER, PHP_AUTH_USER ou uma variante personalizada',
        ],
        'http_auth_guest' => [
            'description' => 'Http Auth guest user',
            'help' => 'If set, allows all http users to authenticate and assigns unknown users to give local username',
        ],
        'http_proxy' => [
            'description' => 'Proxy HTTP',
            'help' => 'Defina isso como fallback se a variável de ambiente http_proxy não estiver disponível.',
        ],
        'https_proxy' => [
            'description' => 'Proxy HTTPS',
            'help' => 'Defina isso como fallback se a variável de ambiente https_proxy não estiver disponível.',
        ],
        'ignore_mount' => [
            'description' => 'Pontos de Montagem a serem ignorados',
            'help' => 'Não monitorar o uso de disco desses Pontos de Montagem',
        ],
        'ignore_mount_network' => [
            'description' => 'Ignorar Pontos de Montagem de Rede',
            'help' => 'Não monitorar o uso de disco de Pontos de Montagem de Rede',
        ],
        'ignore_mount_optical' => [
            'description' => 'Ignorar Drives Ópticos',
            'help' => 'Não monitorar o uso de disco de drives ópticos',
        ],
        'ignore_mount_removable' => [
            'description' => 'Ignorar Drives Removíveis',
            'help' => 'Não monitorar o uso de disco de dispositivos removíveis',
        ],
        'ignore_mount_regexp' => [
            'description' => 'Pontos de montagem correspondentes à regex a serem ignorados',
            'help' => 'Não monitorar o uso de disco de Pontos de Montagem que correspondem a pelo menos uma dessas Expressões Regulares',
        ],
        'ignore_mount_string' => [
            'description' => 'Pontos de montagem contendo String a serem ignorados',
            'help' => 'Não monitorar o uso de disco de Pontos de Montagem que contêm pelo menos uma dessas Strings',
        ],
        'influxdb' => [
            'db' => [
                'description' => 'Banco de Dados',
                'help' => 'Nome do banco de dados InfluxDB para armazenar métricas',
            ],
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o InfluxDB',
            ],
            'host' => [
                'description' => 'Servidor',
                'help' => 'O IP ou nome do host do servidor InfluxDB para enviar dados',
            ],
            'password' => [
                'description' => 'Senha',
                'help' => 'Senha para conectar ao InfluxDB, se necessário',
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'A porta a ser usada para conectar ao servidor InfluxDB',
            ],
            'timeout' => [
                'description' => 'Tempo de espera',
                'help' => 'Quanto tempo esperar pelo servidor InfluxDB, 0 significa tempo de espera padrão',
            ],
            'transport' => [
                'description' => 'Transporte',
                'help' => 'A porta a ser usada para conectar ao servidor InfluxDB',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                    'udp' => 'UDP',
                ],
            ],
            'username' => [
                'description' => 'Nome de Usuário',
                'help' => 'Nome de usuário para conectar ao InfluxDB, se necessário',
            ],
            'batch_size' => [
                'description' => 'Batch Size',
                'help' => 'Number of metrics to send in a single batch, 0 means no batching',
            ],
            'measurements' => [
                'description' => 'Measurements',
                'help' => 'List of measurements to send to InfluxDB, leave empty to send all',
            ],
            'verifySSL' => [
                'description' => 'Verificar SSL',
                'help' => 'Verificar se o certificado SSL é válido e confiável',
            ],
            'debug' => [
                'description' => 'Debug',
                'help' => 'To enable or disable verbose output to CLI',
            ],
        ],
        'influxdbv2' => [
            'bucket' => [
                'description' => 'Bucket',
                'help' => 'Nome do Bucket do InfluxDB para armazenar métricas',
            ],
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o InfluxDB usando a API InfluxDBv2',
            ],
            'host' => [
                'description' => 'Servidor',
                'help' => 'O IP ou nome do host do servidor InfluxDB para enviar dados',
            ],
            'token' => [
                'description' => 'Token',
                'help' => 'Token para conectar ao InfluxDB, se necessário',
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'A porta a ser usada para conectar ao servidor InfluxDB',
            ],
            'transport' => [
                'description' => 'Transporte',
                'help' => 'A porta a ser usada para conectar ao servidor InfluxDB',
                'options' => [
                    'http' => 'HTTP',
                    'https' => 'HTTPS',
                ],
            ],
            'organization' => [
                'description' => 'Organização',
                'help' => 'A organização que contém o bucket no servidor InfluxDB',
            ],
            'allow_redirects' => [
                'description' => 'Permitir Redirecionamentos',
                'help' => 'Permitir redirecionamento do servidor InfluxDB',
            ],
            'debug' => [
                'description' => 'Depuração',
                'help' => 'Ativa ou desativa a saída detalhada via CLI',
            ],
            'log_file' => [
                'description' => 'Arquivo de Logs',
                'help' => 'Define outro arquivo de log, se desejado, para depuração',
            ],
            'groups-exclude' => [
                'description' => 'Grupos de dispositivos excluídos',
                'help' => 'Grupos de dispositivos excluídos do envio de dados para InfluxDBv2',
            ],
            'timeout' => [
                'description' => 'Tempo Limite',
                'help' => 'Tempo limite em segundos',
            ],
            'verify' => [
                'description' => 'Verificar',
                'help' => 'Verifica o certificado',
            ],
            'batch_size' => [
                'description' => 'Tamanho do Lote',
                'help' => 'Quantidade de métricas a serem agrupadas antes de serem enviadas',
            ],
            'max_retry' => [
                'description' => 'Número máximo de tentativas',
                'help' => 'Quantidade de tentativas a serem realizadas',
            ],
        ],
        'kafka' => [
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o Kafka usando o idealo/php-rdkafka-ffi',
            ],
            'groups-exclude' => [
                'description' => 'Identificador de Grupos de dispositivos excluídos',
                'help' => 'Identificador de Grupos de dispositivos excluídos do envio de dados para o Kafka.',
            ],
            'measurement-exclude' => [
                'description' => 'Medições excluídas',
                'help' => 'Módulos de descoberta a serem excluídos do envio para o kafka.',
            ],
            'debug' => [
                'description' => 'Debug',
                'help' => 'Habilita logs detalhados sobre o processo interno de armazenamento do kafka',
            ],
            'security' => [
                'debug' => [
                    'description' => 'Debug de Segurança',
                    'help' => 'Mostrar informações mais detalhadas sobre comunicação de segurança com brokers Kafka',
                ],
            ],
            'broker' => [
                'list' => [
                    'description' => 'Lista de servidores Kafka Brokers no formato host!:porta',
                    'help' => 'Lista de brokers kafka no formato host!:porta. https://github.com/confluentinc/librdkafka/blob/master/CONFIGURATION.md',
                ],
            ],
            'idempotence' => [
                'description' => 'Idempotência',
                'help' => 'Quando definido como verdadeiro, o produtor garantirá que as mensagens sejam produzidas com sucesso exatamente uma vez e na ordem de produção original',
            ],
            'topic' => [
                'description' => 'Tópico',
                'help' => 'As categorias usadas para organizar mensagens',
            ],
            'ssl' => [
                'enable' => [
                    'description' => 'Habilitar SSL',
                    'help' => 'Habilita suporte SSL no Kafka',
                ],
                'protocol' => [
                    'description' => 'Protocolo SSL',
                    'help' => 'Protocolo usado para comunicar com brokers',
                ],
                'ca' => [
                    'location' => [
                        'description' => 'Localização da Autoridade Certificadora SSL',
                        'help' => 'Caminho do arquivo ou diretório para certificado(s) CA para verificar a chave do broker.',
                    ],
                ],
                'certificate' => [
                    'location' => [
                        'description' => 'Localização do Certificado SSL',
                        'help' => 'Caminho para a chave pública do cliente (PEM) usada para autenticação.',
                    ],
                ],
                'key' => [
                    'location' => [
                        'description' => 'Localização da Chave do Certificado SSL',
                        'help' => 'Caminho para a chave privada do cliente (PEM) usada para autenticação.',
                    ],
                    'password' => [
                        'description' => 'Senha da Chave do Certificado SSL',
                        'help' => 'Frase secreta da chave privada (para ser usada com kafka.ssl.key.location).',
                    ],
                ],
                'keystore' => [
                    'location' => [
                        'description' => 'Localização do Certificado Keystore SSL',
                        'help' => 'Caminho para o keystore do cliente (PKCS#12) usado para autenticação.',
                    ],
                    'password' => [
                        'description' => 'Senha da Chave Keystore SSL',
                        'help' => 'Senha do keystore do cliente (PKCS#12).',
                    ],
                ],
            ],
            'flush' => [
                'timeout' => [
                    'description' => 'Timeout de Flush do Kafka',
                    'help' => 'Kafka aguarda este timeout para descarregar mensagens na fila',
                ],
            ],
            'buffer' => [
                'max' => [
                    'message' => [
                        'description' => 'Número máximo de mensagens no buffer do Kafka mantidas na memória do poller',
                        'help' => 'Número máximo permitido de mensagens no buffer do Kafka mantidas na memória do poller',
                    ],
                ],
            ],
            'batch' => [
                'max' => [
                    'message' => [
                        'description' => 'Número máximo de mensagens em lote do Kafka enviadas a cada chamada para os servidores kafka',
                        'help' => 'Número máximo de mensagens em lote do Kafka enviadas a cada chamada para os servidores kafka',
                    ],
                ],
            ],
            'linger' => [
                'ms' => [
                    'description' => 'Tempo de espera do Kafka em ms para acumular mensagens na memória do poller antes de enviar o lote',
                    'help' => 'Tempo de espera do Kafka em ms para acumular mensagens na memória do poller antes de enviar o lote',
                ],
            ],
            'request' => [
                'required' => [
                    'acks' => [
                        'description' => 'Confirmações obrigatórias de requisição do Kafka',
                        'help' => 'Confirmações obrigatórias de requisição do Kafka',
                    ],
                ],
            ],
        ],
        'ipmitool' => [
            'description' => 'Caminho para ipmtool',
        ],
        'login_message' => [
            'description' => 'Mensagem de Login',
            'help' => 'Exibido na página de login',
        ],
        'mac_oui' => [
            'enabled' => [
                'description' => 'Habilitar pesquisa de MAC OUI',
                'help' => 'Habilitar pesquisa de fornecedor do dispositivo (OUI) (dados são baixados pelo script daily.sh)',
            ],
        ],
        'mono_font' => [
            'description' => 'Fonte Monoespaçada',
        ],
        'mtr' => [
            'description' => 'Caminho para mtr',
        ],
        'mydomain' => [
            'description' => 'Domínio Primário',
            'help' => 'Este domínio é usado para descoberta automática de rede e outros processos. O LibreNMS tentará anexá-lo a nomes de host não qualificados.',
        ],
        'network_map_show_on_worldmap' => [
            'description' => 'Exibir links de rede no mapa',
            'help' => 'Mostrar os links de rede entre as diferentes localizações no mapa (tipo weathermap)',
        ],
        'network_map_worldmap_show_disabled_alerts' => [
            'description' => 'Exibir dispositivos com alertas desativados',
            'help' => 'Exibe os dispositivos que possuam alertas desativados no mapa de rede',
        ],
        'network_map_worldmap_link_type' => [
            'description' => 'Fonte do mapa de rede',
            'help' => 'Selecione a fonte de dados para os links do mapa de rede',
        ],
        'nfsen_enable' => [
            'description' => 'Habilitar NfSen',
            'help' => 'Habilitar Integração com NfSen',
        ],
        'nfsen_rrds' => [
            'description' => 'Diretórios RRD do NfSen',
            'help' => 'Esse valor especifica onde seus arquivos RRD do NFSen estão localizados.',
        ],
        'nfsen_subdirlayout' => [
            'description' => 'Definir layout do subdiretório do NfSen',
            'help' => 'Isso deve corresponder ao layout do subdiretório que você definiu no NfSen. 1 é o padrão.',
        ],
        'nfsen_last_max' => [
            'description' => 'Último Máximo',
        ],
        'nfsen_top_max' => [
            'description' => 'Máximo Top',
            'help' => 'Valor máximo topN para estatísticas',
        ],
        'nfsen_top_N' => [
            'description' => 'Top N',
        ],
        'nfsen_top_default' => [
            'description' => 'Top N Padrão',
        ],
        'nfsen_stats_default' => [
            'description' => 'Estatística Padrão',
        ],
        'nfsen_order_default' => [
            'description' => 'Ordem Padrão',
        ],
        'nfsen_last_default' => [
            'description' => 'Último Padrão',
        ],
        'nfsen_lasts' => [
            'description' => 'Opções Padrão de Último',
        ],
        'nfsen_base' => [
            'description' => 'Diretório Base do NFSen',
            'help' => 'Usado para localizar gráficos específicos do dispositivo',
        ],
        'nfsen_split_char' => [
            'description' => 'Caractere de Divisão',
            'help' => 'Este valor nos diz o que substituir os pontos completos `.` nos nomes dos hosts dos dispositivos. Normalmente: `_`',
        ],
        'nfsen_suffix' => [
            'description' => 'Sufixo do Nome do Arquivo',
            'help' => 'Isso é muito importante, pois os nomes dos dispositivos no NfSen são limitados a 21 caracteres. Isso significa que nomes de domínio completos para dispositivos podem ser muito problemáticos para encaixar, então, geralmente, esse pedaço é removido.',
        ],
        'no_proxy' => [
            'description' => 'Exceções de Proxy',
            'help' => 'Defina isso como fallback se a variável de ambiente no_proxy não estiver disponível. Lista separada por vírgulas de IPs, hosts ou domínios a serem ignorados.',
        ],
        'opentsdb' => [
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o OpenTSDB',
            ],
            'host' => [
                'description' => 'Servidor',
                'help' => 'O IP ou nome do host do servidor OpenTSDB para enviar dados',
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'A porta a ser usada para conectar ao servidor OpenTSDB',
            ],
        ],
        'own_hostname' => [
            'description' => 'Nome de host do LibreNMS',
            'help' => 'Deve ser definido como o nome do host/ip ao qual o servidor librenms é adicionado',
        ],
        'oxidized' => [
            'default_group' => [
                'description' => 'Definir o grupo padrão retornado',
            ],
            'ignore_groups' => [
                'description' => 'Não fazer backup desses grupos Oxidized',
                'help' => 'Grupos (definidos via Mapeamento de Variáveis) excluídos de serem enviados para o Oxidized',
            ],
            'enabled' => [
                'description' => 'Habilitar suporte ao Oxidized',
            ],
            'features' => [
                'versioning' => [
                    'description' => 'Habilitar acesso a versionamento de configurações',
                    'help' => 'Habilitar versionamento de configurações do Oxidized (requer backend git)',
                ],
            ],
            'group_support' => [
                'description' => 'Habilitar o retorno de grupos para o Oxidized',
            ],
            'ignore_os' => [
                'description' => 'Não fazer backup desses sistemas operacionais',
                'help' => 'Não fazer backup dos sistemas operacionais listados com o Oxidized. Deve corresponder ao nome do Sistema Operacional no LibreNMS (todos em minúsculas e sem espaços). Permite apenas sistemas operacionais existentes.',
            ],
            'ignore_types' => [
                'description' => 'Não fazer backup desses tipos de dispositivos',
                'help' => 'Não fazer backup dos tipos de dispositivos listados com o Oxidized. Permite apenas tipos existentes.',
            ],
            'reload_nodes' => [
                'description' => 'Recarregar lista de nós do Oxidized cada vez que um dispositivo é adicionado',
            ],
            'maps' => [
                'description' => 'Mapeamento de Variáveis',
                'help' => 'Usado para definir grupo ou outras variáveis ou mapear nomes de sistemas operacionais que diferem.',
            ],
            'url' => [
                'description' => 'URL para a API do Oxidized',
                'help' => 'URL da API do Oxidized (Por exemplo: http://127.0.0.1:8888)',
            ],
        ],
        'password' => [
            'min_length' => [
                'description' => 'Comprimento Mínimo da Senha',
                'help' => 'Senhas mais curtas do que o comprimento dado serão rejeitadas',
            ],
        ],
        'peeringdb' => [
            'enabled' => [
                'description' => 'Habilitar pesquisa PeeringDB',
                'help' => 'Habilitar pesquisa PeeringDB (dados são baixados pelo script daily.sh)',
            ],
        ],
        'permission' => [
            'device_group' => [
                'allow_dynamic' => [
                    'description' => 'Habilitar acesso de usuário via Grupos de Dispositivos Dinâmicos',
                ],
            ],
        ],
        'bad_if' => [
            'description' => 'Nomes de Interface Inadequados',
            'help' => 'IF-MIB da interface de rede:!:ifName que deve ser ignorado',
        ],
        'bad_if_regexp' => [
            'description' => 'Regex de Nome de Interface Inadequado',
            'help' => 'IF-MIB da interface de rede:!:ifName que deve ser ignorado usando expressões regulares',
        ],
        'bad_ifoperstatus' => [
            'description' => 'Status Operacional de Interface Inadequada',
            'help' => 'IF-MIB da interface de rede:!:ifOperStatus que deve ser ignorado',
        ],
        'bad_iftype' => [
            'description' => 'Tipos de Interface Inadequadas',
            'help' => 'IF-MIB da interface de rede:!:ifType que deve ser ignorado',
        ],
        'ping' => [
            'description' => 'Caminho para ping',
        ],
        'ping_rrd_step' => [
            'description' => 'Frequência de Ping',
            'help' => 'Com que frequência executar. Valor padrão para todos os nós. Aviso! Se você mudar isso, você deve fazer mudanças adicionais. Verifique os documentos de Fast Ping.',
        ],
        'poller_modules' => [
            'unix-agent' => [
                'description' => 'Agente Unix',
            ],
            'os' => [
                'description' => 'Sistema Operacional',
            ],
            'ipmi' => [
                'description' => 'IPMI',
            ],
            'qos' => [
                'description' => 'QoS',
            ],
            'sensors' => [
                'description' => 'Sensores',
            ],
            'processors' => [
                'description' => 'Processadores',
            ],
            'mempools' => [
                'description' => 'Pools de Memória',
            ],
            'storage' => [
                'description' => 'Armazenamento',
            ],
            'netstats' => [
                'description' => 'Netstats',
            ],
            'hr-mib' => [
                'description' => 'Mib HR',
            ],
            'ucd-mib' => [
                'description' => 'Mib Ucd',
            ],
            'ipSystemStats' => [
                'description' => 'ipSystemStats',
            ],
            'ports' => [
                'description' => 'Portas',
            ],
            'bgp-peers' => [
                'description' => 'Pares BGP',
            ],
            'junose-atm-vp' => [
                'description' => 'JunOS ATM VP',
            ],
            'ucd-diskio' => [
                'description' => 'UCD DiskIO',
            ],
            'wireless' => [
                'description' => 'Wireless',
            ],
            'ospf' => [
                'description' => 'OSPF',
            ],
            'ospfv3' => [
                'description' => 'OSPFv3',
            ],
            'isis' => [
                'description' => 'ISIS',
            ],
            'cisco-ipsec-flow-monitor' => [
                'description' => 'Monitor de Fluxo Cisco IPSec',
            ],
            'cisco-remote-access-monitor' => [
                'description' => 'Monitor de Acesso Remoto Cisco',
            ],
            'cisco-cef' => [
                'description' => 'Cisco CEF',
            ],
            'slas' => [
                'description' => 'Rastreamento de Acordo de Nível de Serviço',
            ],
            'cisco-mac-accounting' => [
                'description' => 'Cisco MAC Accounting',
            ],
            'cipsec-tunnels' => [
                'description' => 'Túneis Cipsec',
            ],
            'cisco-ace-loadbalancer' => [
                'description' => 'Balanceador de Carga Cisco ACE',
            ],
            'cisco-ace-serverfarms' => [
                'description' => 'Serverfarms Cisco ACE',
            ],
            'cisco-otv' => [
                'description' => 'Cisco OTV',
            ],
            'cisco-qfp' => [
                'description' => 'Cisco QFP',
            ],
            'cisco-vpdn' => [
                'description' => 'Cisco VPDN',
            ],
            'nac' => [
                'description' => 'NAC',
            ],
            'netscaler-vsvr' => [
                'description' => 'Netscaler VSVR',
            ],
            'aruba-controller' => [
                'description' => 'Controlador Aruba',
            ],
            'availability' => [
                'description' => 'Disponibilidade',
            ],
            'entity-physical' => [
                'description' => 'Entidade Física',
            ],
            'entity-state' => [
                'description' => 'Estado da Entidade',
            ],
            'applications' => [
                'description' => 'Aplicações',
            ],
            'stp' => [
                'description' => 'STP',
            ],
            'vminfo' => [
                'description' => 'Informações de VM do hypervisor',
            ],
            'ntp' => [
                'description' => 'NTP',
            ],
            'loadbalancers' => [
                'description' => 'Balanceadores de Carga',
            ],
            'mef' => [
                'description' => 'MEF',
            ],
            'mpls' => [
                'description' => 'MPLS',
            ],
            'xdsl' => [
                'description' => 'xDSL',
            ],
            'printer-supplies' => [
                'description' => 'Suprimentos para Impressora',
            ],
        ],
        'ports_fdb_purge' => [
            'description' => 'Entradas de Porta FDB mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'ports_nac_purge' => [
            'description' => 'Entradas de Porta NAC mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'ports_purge' => [
            'description' => 'Purgar portas deletadas',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'prometheus' => [
            'enable' => [
                'description' => 'Habilitar',
                'help' => 'Exporta métricas para o Prometheus Push Gateway',
            ],
            'url' => [
                'description' => 'URL',
                'help' => 'A URL do Prometheus Push Gateway para enviar dados',
            ],
            'Job' => [
                'description' => 'Tarefa',
                'help' => 'Etiqueta de tarefa para métricas exportadas',
            ],
            'attach_sysname' => [
                'description' => 'Anexar sysName do Dispositivo',
                'help' => 'Anexar informações de sysName ao Prometheus.',
            ],
            'prefix' => [
                'description' => 'Prefixo',
                'help' => 'Texto opcional para prefixar nomes de métricas exportadas',
            ],
        ],
        'public_status' => [
            'description' => 'Mostrar Status Publicamente',
            'help' => 'Mostra o status de alguns dispositivos na página de login sem autenticação.',
        ],
        'routes_max_number' => [
            'description' => 'Número máximo de rotas permitidas para descoberta',
            'help' => 'Nenhuma rota será descoberta se o tamanho da tabela de roteamento for maior que este número',
        ],
        'default_port_group' => [
            'description' => 'Grupo de Porta Padrão',
            'help' => 'Novas portas descobertas serão atribuídas a este grupo de porta.',
        ],
        'nets' => [
            'description' => 'Redes de Descoberta Automática',
            'help' => 'Redes a partir das quais dispositivos serão descobertos automaticamente.',
        ],
        'autodiscovery' => [
            'nets-exclude' => [
                'description' => 'Redes/IPs a serem ignorados',
                'help' => 'Redes/IPs que não serão descobertos automaticamente. Também exclui IPs das Redes de Descoberta Automática',
            ],
        ],
        'radius' => [
            'default_roles' => [
                'description' => 'Funções padrão do usuário',
                'help' => 'Define as funções que serão atribuídas ao usuário, a menos que o Radius envie atributos que especifiquem função(ões)',
            ],
            'enforce_roles' => [
                'description' => 'Impor funções no login',
                'help' => 'Se ativado, as funções serão definidas para as especificadas pelo atributo Filter-ID ou radius.default_roles no login. Caso contrário, elas serão definidas quando o usuário for criado e nunca mais serão alteradas depois disso.',
            ],
        ],
        'reporting' => [
            'error' => [
                'description' => 'Enviar Relatórios de Erros',
                'help' => 'Envia determinados erros para o LibreNMS para análise e correção',
            ],
            'usage' => [
                'description' => 'Enviar Relatórios de Uso',
                'help' => 'Envia relatório de uso e versão para o LibreNMS. Para excluir estatísticas anônimas, visite a página Sobre. Você pode visualizar estatísticas em https://stats.librenms.org',
            ],
            'dump_errors' => [
                'description' => 'Despejar erros de depuração (Poderá corromper sua instalação)',
                'help' => 'Despejar erros que normalmente são ocultados para que você, como desenvolvedor, possa encontrar e corrigir possíveis problemas.',
            ],
            'throttle' => [
                'description' => 'Limitar Relatórios de Erros',
                'help' => 'Relatórios serão enviados apenas a determinado quantidade de segundos. Sem isso, se você tiver um erro em um código comum, os relatórios podem sair fora do controle. Defina como 0 para desativar a limitação.',
            ],
        ],
        'route_purge' => [
            'description' => 'Entradas de Rota mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'rrd' => [
            'heartbeat' => [
                'description' => 'Alterar o valor de heartbeat do rrd (padrão 600)',
            ],
            'step' => [
                'description' => 'Alterar o valor de step do rrd (padrão 300)',
            ],
        ],
        'rrd_dir' => [
            'description' => 'Localização do RRD',
            'help' => 'Localização dos arquivos rrd. O padrão é rrd dentro do diretório LibreNMS. Alterar essa configuração não move os arquivos rrd.',
        ],
        'rrd_purge' => [
            'description' => 'Arquivos RRD mais antigos que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'rrd_rra' => [
            'description' => 'Configurações de Formato RRD',
            'help' => 'Isso não pode ser alterado sem excluir seus arquivos RRD existentes, embora se possa, conceitualmente, aumentar ou diminuir o tamanho de cada RRA se tiver problemas de desempenho ou se tiver um subsistema de I/O muito rápido sem preocupações de desempenho.',
        ],
        'rrdcached' => [
            'description' => 'Habilitar rrdcached (socket)',
            'help' => 'Habilita o rrdcached definindo a localização do socket rrdcached. Pode ser socket unix ou de rede (unix:/run/rrdcached.sock ou localhost:42217)',
        ],
        'rrdtool' => [
            'description' => 'Caminho para rrdtool',
        ],
        'rrdtool_tune' => [
            'description' => 'Ajustar todos os arquivos de porta rrd para usar valores máximos',
            'help' => 'Ajuste automático do valor máximo para arquivos de porta rrd',
        ],
        'rrdtool_version' => [
            'description' => 'Define a versão do rrdtool no seu servidor',
            'help' => 'Qualquer coisa acima de 1.5.5 suporta todos os recursos que o LibreNMS usa, não defina um valor maior do que a versão instalada',
        ],
        'schedule_type' => [
            'alerting' => [
                'description' => 'Alertar',
                'help' => 'Método de agendamento de tarefas de alerta. O modo legado usará cron se a entrada do crontab existir e o serviço de dispatcher se a opção de configuração legada service_billing_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'cron' => 'Cron (alerts.php)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
            'billing' => [
                'description' => 'Tarifação',
                'help' => 'Método de agendamento de tarefas de tarifação. O modo legado usará cron se a entrada do crontab existir e o serviço de dispatcher se a opção de configuração legada service_billing_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'cron' => 'Cron (poll-billing.php e billing-calculate.php)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
            'discovery' => [
                'description' => 'Descoberta',
                'help' => 'Método de agendamento de tarefas de descoberta. O modo legado usará cron se a entrada do crontab existir e o serviço de dispatcher se a opção de configuração legada service_discovery_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'cron' => 'Cron (lnms device:discover)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
            'ping' => [
                'description' => 'Fast Ping',
                'help' => 'Método de agendamento de tarefas de Fast Ping. O modo legado usará cron se a entrada do crontab existir e usará o serviço de dispatcher se a opção de configuração legada service_ping_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'disabled' => 'Desativado (Pings apenas durante coleta de dados)',
                    'cron' => 'Cron (ping.php)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
            'poller' => [
                'description' => 'Coletor de Dados',
                'help' => 'Método de agendamento de tarefas do coletor de dados. O modo legado usará cron se a entrada do crontab existir e usará o serviço de dispatcher se a opção de configuração legada service_poller_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'cron' => 'Cron (poller.php)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
            'services' => [
                'description' => 'Serviços',
                'help' => 'Método de agendamento de tarefas de serviços. O modo legado usará cron se a entrada do crontab existir e usará o serviço de dispatcher se a opção de configuração legada service_services_enabled estiver definida como verdadeiro.',
                'options' => [
                    'legacy' => 'Legado (Irrestrito)',
                    'cron' => 'Cron (check-services.php)',
                    'dispatcher' => 'Serviço de Dispatcher',
                ],
            ],
        ],
        'service_master_timeout' => [
            'description' => 'Tempo de Expiração do Dispatcher Mestre',
            'help' => 'O tempo antes da expiração do bloqueio mestre. Se o mestre ficar indisponível, levará esse tempo para outro nó assumir. No entanto, se levar mais tempo do que o limite para despachar o trabalho, haverá múltiplos mestres.',
        ],
        'service_poller_workers' => [
            'description' => 'Workers de Coleta de Dados',
            'help' => 'Quantidade de workers de coleta de dados a serem gerados. Valor padrão para todos os nós.',
        ],
        'service_poller_frequency' => [
            'description' => 'Frequência de Coleta de Dados (Aviso!)',
            'help' => 'Com que frequência coletar dados dos dispositivos. Valor padrão para todos os nós. Aviso! Alterar isso sem corrigir os arquivos rrd corromperá os gráficos. Veja a documentação para mais informações.',
        ],
        'service_poller_down_retry' => [
            'description' => 'Repetir Dispositivo Inativo',
            'help' => 'Se um dispositivo estiver inativo quando a coleta de dados for feita, este é o tempo de espera antes de tentar novamente. Valor padrão para todos os nós.',
        ],
        'service_discovery_workers' => [
            'description' => 'Workers de Descoberta',
            'help' => 'Quantidade de workers de descoberta a serem executados. Configurar muito alto pode causar sobrecarga. Valor padrão para todos os nós.',
        ],
        'service_discovery_frequency' => [
            'description' => 'Frequência de Descoberta',
            'help' => 'Com que frequência executar a descoberta de dispositivos. Valor padrão para todos os nós. O padrão é 4 vezes ao dia.',
        ],
        'service_services_workers' => [
            'description' => 'Workers de Serviços',
            'help' => 'Quantidade de workers de serviços. Valor padrão para todos os nós.',
        ],
        'service_services_frequency' => [
            'description' => 'Frequência dos Serviços',
            'help' => 'Com que frequência executar os serviços. Isso deve coincidir com a frequência de polling. Valor padrão para todos os nós.',
        ],
        'service_billing_frequency' => [
            'description' => 'Frequência de Tarifação',
            'help' => 'Com que frequência coletar dados de tarifação. Valor padrão para todos os nós.',
        ],
        'service_billing_calculate_frequency' => [
            'description' => 'Frequência de Cálculo de Tarifação',
            'help' => 'Com que frequência calcular a tarifação. Valor padrão para todos os nós.',
        ],
        'service_alerting_frequency' => [
            'description' => 'Frequência de Alertas',
            'help' => 'Com que frequência as regras de alerta são verificadas. Note que os dados são atualizados apenas com base na frequência da coleta de dados. Valor padrão para todos os nós.',
        ],
        'service_update_enabled' => [
            'description' => 'Manutenção Diária Habilitada',
            'help' => 'Executa o script de manutenção daily.sh e reinicia o serviço de dispatcher depois. Valor padrão para todos os nós.',
        ],
        'service_update_frequency' => [
            'description' => 'Frequência de Manutenção',
            'help' => 'Com que frequência executar a manutenção diária. O padrão é 1 dia. É altamente recomendável não alterar isso. Valor padrão para todos os nós.',
        ],
        'service_loglevel' => [
            'description' => 'Nível de Log',
            'help' => 'Nível de log do serviço de dispatcher. Valor padrão para todos os nós.',
        ],
        'service_watchdog_enabled' => [
            'description' => 'Watchdog Habilitado',
            'help' => 'O Watchdog monitora o arquivo de log e reinicia o serviço se ele não for atualizado. Valor padrão para todos os nós.',
        ],
        'service_watchdog_log' => [
            'description' => 'Arquivo de Log a ser Monitorado',
            'help' => 'O padrão é o arquivo de log do LibreNMS. Valor padrão para todos os nós.',
        ],
        'shorthost_target_length' => [
            'description' => 'Comprimento máximo do nome de host encurtado',
            'help' => 'Reduz o nome de host para o comprimento máximo, mas sempre completa as partes do subdomínio',
        ],
        'site_style' => [
            'description' => 'Tema Padrão',
            'options' => [
                'blue' => 'Azul',
                'dark' => 'Escuro',
                'light' => 'Claro',
                'mono' => 'Mono',
            ],
        ],
        'snmp' => [
            'transports' => [
                'description' => 'Transporte (prioridade)',
                'help' => 'Selecione os transportes habilitados e ordene-os conforme desejar que sejam tentados.',
            ],
            'version' => [
                'description' => 'Versão (prioridade)',
                'help' => 'Selecione as versões habilitadas e ordene-as conforme desejar que sejam tentadas.',
            ],
            'community' => [
                'description' => 'Comunidades (prioridade)',
                'help' => 'Insira as strings de comunidade para v1 e v2c e ordene-as conforme desejar que sejam tentadas',
            ],
            'max_oid' => [
                'description' => 'Máximo de OIDs',
                'help' => 'Máximo de OIDs por consulta. Pode ser sobrescrito em níveis de sistema operacional e dispositivo.',
            ],
            'max_repeaters' => [
                'description' => 'Máximo de Repetidores',
                'help' => 'Defina repetidores para usar em solicitações bulk SNMP',
            ],
            'oids' => [
                'no_bulk' => [
                    'description' => 'Desativar bulk SNMP para OIDs',
                    'help' => 'Desativa a operação bulk SNMP para certos OIDs. Geralmente, isso deve ser definido em um sistema operacional. O formato deve ser MIB::OID',
                ],
                'unordered' => [
                    'description' => 'Permitir respostas SNMP fora de ordem para OIDs',
                    'help' => 'Ignorar OIDs fora de ordem nas respostas SNMP para certos OIDs. OIDs fora de ordem podem resultar em um loop de OID durante um snmpwalk. Geralmente, isso deve ser definido em um sistema operacional. O formato deve ser MIB::OID',
                ],
            ],
            'port' => [
                'description' => 'Porta',
                'help' => 'Defina a porta TCP/UDP a ser usada para SNMP',
            ],
            'timeout' => [
                'description' => 'Tempo de Espera',
                'help' => 'Tempo de espera em segundos',
            ],
            'retries' => [
                'description' => 'Repetições',
                'help' => 'quantas vezes tentar novamente a consulta',
            ],
            'v3' => [
                'description' => 'Autenticação SNMP v3 (prioridade)',
                'help' => 'Configure as variáveis de autenticação v3 e ordene-as conforme desejar que sejam tentadas',
                'auth' => 'Autenticação',
                'crypto' => 'Criptografia',
                'fields' => [
                    'authalgo' => 'Algoritmo',
                    'authlevel' => 'Nível',
                    'authname' => 'Nome de Usuário',
                    'authpass' => 'Senha',
                    'cryptoalgo' => 'Algoritmo',
                    'cryptopass' => 'Senha',
                ],
                'level' => [
                    'noAuthNoPriv' => 'Sem Autenticação, Sem Privacidade',
                    'authNoPriv' => 'Autenticação, Sem Privacidade',
                    'authPriv' => 'Autenticação e Privacidade',
                ],
            ],
        ],
        'snmpbulkwalk' => [
            'description' => 'Caminho para snmpbulkwalk',
        ],
        'snmpget' => [
            'description' => 'Caminho para snmpget',
        ],
        'snmpgetnext' => [
            'description' => 'Caminho para snmpgetnext',
        ],
        'snmptranslate' => [
            'description' => 'Caminho para snmptranslate',
        ],
        'snmptraps' => [
            'eventlog' => [
                'description' => 'Criar registro de eventos para snmptraps',
                'help' => 'Independentemente da ação que pode ser mapeada para o trap',
            ],
            'eventlog_detailed' => [
                'description' => 'Habilitar logs detalhados',
                'help' => 'Adicionar todos os OIDs recebidos com o trap no registro de eventos',
            ],
        ],
        'snmpwalk' => [
            'description' => 'Caminho para snmpwalk',
        ],
        'syslog_filter' => [
            'description' => 'Filtrar mensagens syslog contendo',
        ],
        'syslog_purge' => [
            'description' => 'Entradas de Syslog mais antigas que',
            'help' => 'Limpeza feita pelo script daily.sh',
        ],
        'title_image' => [
            'description' => 'Imagem do Título',
            'help' => 'Substitui a imagem padrão do título.',
        ],
        'traceroute' => [
            'description' => 'Caminho para traceroute',
        ],
        'twofactor' => [
            'description' => 'Autenticação em Dois Fatores',
            'help' => 'Permitir que os usuários ativem e usem Senhas de Uso Único Baseadas em Tempo (TOTP) ou Baseadas em Contador (HOTP)',
        ],
        'twofactor_lock' => [
            'description' => 'Tempo de Retardo para Autenticação de Dois Fatores (segundos)',
            'help' => 'Tempo de bloqueio para esperar em segundos antes de permitir novas tentativas se a Autenticação em Dois Fatores falhar 3 vezes consecutivas - solicitará ao usuário que aguarde esse tempo. Defina como 0 para desativar, resultando em um bloqueio permanente da conta e uma mensagem ao usuário para contatar o administrador',
        ],
        'unix-agent' => [
            'connection-timeout' => [
                'description' => 'Tempo de espera da conexão do agente Unix',
            ],
            'port' => [
                'description' => 'Porta padrão do agente Unix',
                'help' => 'Porta padrão para o agente Unix (check_mk)',
            ],
            'read-timeout' => [
                'description' => 'Tempo de espera de leitura do agente Unix',
            ],
        ],
        'update' => [
            'description' => 'Habilitar atualizações em ./daily.sh',
        ],
        'update_channel' => [
            'description' => 'Canal de Atualização',
            'options' => [
                'master' => 'Diário',
                'release' => 'Mensal',
            ],
        ],
        'uptime_warning' => [
            'description' => 'Mostrar Dispositivo como aviso se Uptime abaixo de (segundos)',
            'help' => 'Mostra Dispositivo como aviso se o Uptime estiver abaixo desse valor. Padrão 24h',
        ],
        'virsh' => [
            'description' => 'Caminho para virsh',
        ],
        'webui' => [
            'availability_map_box_size' => [
                'description' => 'Largura da caixa de disponibilidade',
                'help' => 'Insira a largura desejada do bloco em pixels para o tamanho da caixa na visualização completa',
            ],
            'availability_map_compact' => [
                'description' => 'Visualização compacta do mapa de disponibilidade',
                'help' => 'Visualização do mapa de disponibilidade com pequenos indicadores',
            ],
            'availability_map_sort_status' => [
                'description' => 'Ordenar por status',
                'help' => 'Ordenar dispositivos e serviços por status',
            ],
            'availability_map_use_device_groups' => [
                'description' => 'Usar filtro de grupos de dispositivos',
                'help' => 'Habilitar uso do filtro de grupos de dispositivos',
            ],
            'default_dashboard_id' => [
                'description' => 'Dashboard padrão',
                'help' => 'Dashboard_id padrão global para todos os usuários que não têm seu próprio padrão definido',
            ],
            'dynamic_graphs' => [
                'description' => 'Habilitar gráficos dinâmicos',
                'help' => 'Habilitar gráficos dinâmicos; permite zoom e pan em gráficos',
            ],
            'global_search_result_limit' => [
                'description' => 'Definir o limite máximo de resultados de pesquisa',
                'help' => 'Limite global de resultados de pesquisa',
            ],
            'graph_stacked' => [
                'description' => 'Usar gráficos empilhados',
                'help' => 'Exibir gráficos empilhados em vez de gráficos invertidos',
            ],
            'graph_type' => [
                'description' => 'Definir o tipo de gráfico',
                'help' => 'Definir o tipo de gráfico padrão',
                'options' => [
                    'png' => 'PNG',
                    'svg' => 'SVG',
                ],
            ],
            'min_graph_height' => [
                'description' => 'Definir a altura mínima do gráfico',
                'help' => 'Altura mínima do gráfico (padrão: 300)',
            ],
            'graph_stat_percentile_disable' => [
                'description' => 'Desativar percentual para gráficos de estatísticas globalmente',
                'help' => 'Desativa a exibição dos valores e linhas percentuais para gráficos que exibem esses',
            ],
        ],
        'device_display_default' => [
            'description' => 'Nome de Exibição do Dispositivo',
            'help' => 'Define o nome de exibição padrão para todos os dispositivos (pode ser substituído por dispositivo). Nome do host/IP: Mostre apenas o nome do host ou IP com o qual o dispositivo foi adicionado. sysName: Mostre apenas o sysName do SNMP. Nome do host ou sysName: Mostre o nome do host, mas se for um IP, mostre o sysName.',
            'options' => [
                'hostname' => 'Nome do Host / IP',
                'sysName_fallback' => 'Nome do Host, fallback para sysName para IPs',
                'sysName' => 'sysName',
                'ip' => 'IP / Nome do Host',
            ],
        ],
        'device_location_map_open' => [
            'description' => 'Abrir Mapa de Localização',
            'help' => 'O Mapa de Localização é exibido por padrão',
        ],
        'device_location_map_show_devices' => [
            'description' => 'Exibir dispositivos no mapa de localização',
            'help' => 'Exibir todos os dispositivos no mapa de localização quando estiver visível',
        ],
        'device_location_map_show_device_dependencies' => [
            'description' => 'Exibir dependências dos dispositivos no mapa de localização',
            'help' => 'Exibir links entre dispositivos no mapa de localização com base nas dependências dos pais',
        ],
        'smokeping.integration' => [
            'description' => 'Habilitar',
            'help' => 'Habilitar integração com Smokeping',
        ],
        'smokeping.dir' => [
            'description' => 'Caminho para rrds',
            'help' => 'Caminho completo para os RRDs do Smokeping',
        ],
        'smokeping.pings' => [
            'description' => 'Pings',
            'help' => 'Número de pings configurados no Smokeping',
        ],
        'smokeping.url' => [
            'description' => 'URL para smokeping',
            'help' => 'URL completa para a GUI do smokeping',
        ],
    ],
    'twofactor' => [
        'description' => 'Habilitar Autenticação em Dois Fatores',
        'help' => 'Habilita a Autenticação em Dois Fatores. Você deve configurar cada conta para torná-la ativa.',
    ],
    'units' => [
        'days' => 'dias',
        'ms' => 'ms',
        'seconds' => 'segundos',
    ],
    'validate' => [
        'boolean' => ':value não é um booleano válido',
        'color' => ':value não é um código de cor hexadecimal válido',
        'email' => ':value não é um email válido',
        'float' => ':value não é um float',
        'integer' => ':value não é um inteiro',
        'password' => 'A senha está incorreta',
        'select' => ':value não é um valor permitido',
        'text' => ':value não é permitido',
        'array' => 'Formato inválido',
        'executable' => ':value não é um executável válido',
        'directory' => ':value não é um diretório válido',
    ],
    'nfdump' => [
        'description' => 'Caminho para nfdump',
    ],
];
