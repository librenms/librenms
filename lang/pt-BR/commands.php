<?php

return [
    'config:clear' => [
        'description' => 'Limpa o cache de configuração. Isso permitirá que quaisquer alterações feitas desde o último carregamento completo da configuração sejam refletidas na configuração atual.',
    ],
    'config:get' => [
        'description' => 'Obter valor de configuração',
        'arguments' => [
            'setting' => 'configuração para obter valor na notação de ponto (exemplo: snmp.community.0)',
        ],
        'options' => [
            'dump' => 'Dump de toda a configuração como json',
        ],
    ],
    'config:set' => [
        'description' => 'Definir valor de configuração (ou desfazer)',
        'arguments' => [
            'setting' => 'configuração para definir na notação de ponto (exemplo: snmp.community.0) Para anexar a um array, sufixar com .+',
            'value' => 'valor a ser definido, desfaz a configuração se omitido',
        ],
        'options' => [
            'ignore-checks' => 'Ignorar todas as verificações de segurança',
        ],
        'confirm' => 'Redefinir :setting para o padrão?',
        'forget_from' => 'Esquecer :path de :parent?',
        'errors' => [
            'append' => 'Não é possível anexar a configuração não-array',
            'failed' => 'Falha ao definir :setting',
            'invalid' => 'Esta não é uma configuração válida. Por favor, verifique sua entrada',
            'invalid_os' => 'SO especificado (:os) não existe',
            'nodb' => 'Banco de dados não conectado',
            'no-validation' => 'Não é possível definir :setting, falta definição de validação.',
        ],
    ],
    'db:seed' => [
        'existing_config' => 'O banco de dados contém configurações existentes. Continuar?',
    ],
    'dev:check' => [
        'description' => 'Verificações de código do LibreNMS. Executar sem opções executa todas as verificações',
        'arguments' => [
            'check' => 'Executar a verificação especificada :checks',
        ],
        'options' => [
            'commands' => 'Imprimir apenas os comandos que seriam executados, sem verificações',
            'db' => 'Executar testes unitários que requerem conexão com o banco de dados',
            'fail-fast' => 'Parar verificações ao encontrar qualquer falha',
            'full' => 'Executar verificações completas ignorando a filtragem de arquivos alterados',
            'module' => 'Módulo específico para executar testes. Implica unit, --db, --snmpsim',
            'os' => 'SO específico para executar testes. Pode ser uma expressão regular ou uma lista separada por vírgulas. Implica unit, --db, --snmpsim',
            'os-modules-only' => 'Pular teste de detecção de SO ao especificar um SO específico. Acelera o tempo de teste ao verificar alterações não relacionadas à detecção.',
            'quiet' => 'Ocultar saída a menos que haja um erro',
            'snmpsim' => 'Usar snmpsim para testes unitários',
        ],
    ],
    'dev:simulate' => [
        'description' => 'Simular dispositivos usando dados de teste',
        'arguments' => [
            'file' => 'O nome do arquivo (apenas o nome base) do arquivo snmprec para atualizar ou adicionar ao LibreNMS. Se o arquivo não for especificado, nenhum dispositivo será adicionado ou atualizado.',
        ],
        'options' => [
            'multiple' => 'Usar nome da comunidade para o nome do host em vez de snmpsim',
            'remove' => 'Remover o dispositivo após parar',
        ],
        'added' => 'Dispositivo :hostname (:id) adicionado',
        'exit' => 'Ctrl-C para parar',
        'removed' => 'Dispositivo :id removido',
        'updated' => 'Dispositivo :hostname (:id) atualizado',
        'setup' => 'Configurando snmpsim venv em :dir',
    ],
    'device:add' => [
        'description' => 'Adicionar um novo dispositivo',
        'arguments' => [
            'device spec' => 'Nome do host ou IP para adicionar',
        ],
        'options' => [
            'v1' => 'Usar SNMP v1',
            'v2c' => 'Usar SNMP v2c',
            'v3' => 'Usar SNMP v3',
            'display-name' => "Uma string para exibir como nome deste dispositivo, padrão é o nome do host.\nPode ser um modelo simples usando substituições: {{ \$hostname }}, {{ \$sysName }}, {{ \$sysName_fallback }}, {{ \$ip }}",
            'force' => 'Apenas adicione o dispositivo, não faça verificações de segurança',
            'group' => 'Grupo de coletores de dados (para coletor de dados distribuído)',
            'ping-fallback' => 'Adicionar o dispositivo apenas para ping se não responder ao SNMP',
            'port-association-mode' => 'Define como as portas são mapeadas. ifName é sugerido para Linux/Unix',
            'community' => 'Comunidade SNMP v1 ou v2',
            'transport' => 'Transporte para conectar ao dispositivo',
            'port' => 'Porta de transporte SNMP',
            'security-name' => 'Nome de usuário de segurança SNMPv3',
            'auth-password' => 'Senha de autenticação SNMPv3',
            'auth-protocol' => 'Protocolo de autenticação SNMPv3',
            'privacy-protocol' => 'Protocolo de privacidade SNMPv3',
            'privacy-password' => 'Senha de privacidade SNMPv3',
            'ping-only' => 'Adicionar um dispositivo apenas para ping',
            'os' => 'Apenas ping: especificar SO',
            'hardware' => 'Apenas ping: especificar hardware',
            'sysName' => 'Apenas ping: especificar sysName',
        ],
        'validation-errors' => [
            'port.between' => 'A porta deve ser entre 1-65535',
            'poller-group.in' => 'O grupo de coletores de dados não existe',
        ],
        'messages' => [
            'save_failed' => 'Falha ao salvar dispositivo :hostname',
            'try_force' => 'Você pode tentar com a opção --force para pular verificações de segurança',
            'added' => 'Dispositivo adicionado :hostname (:device_id)',
        ],
    ],
    'device:ping' => [
        'description' => 'Pingar dispositivo e registrar dados para resposta',
        'arguments' => [
            'device spec' => 'Dispositivo para pingar um dos: <ID do Dispositivo>, <Nome do Host/IP>, todos',
        ],
    ],
    'device:poll' => [
        'description' => 'Obter dados do dispositivo(s) conforme definido pela descoberta',
        'arguments' => [
            'device spec' => 'Especificação do dispositivo para coleta de dados: device_id, nome do host, curinga (*), ímpar, par, todos',
        ],
        'options' => [
            'modules' => 'Especificar módulo único a ser executado. Módulos separados por vírgulas, submódulos podem ser adicionados com /',
            'no-data' => 'Não atualizar datastores (RRD, InfluxDB, etc)',
        ],
        'errors' => [
            'db_connect' => 'Falha ao conectar ao banco de dados. Verifique se o serviço de banco de dados está em execução e as configurações de conexão.',
            'db_auth' => 'Falha ao conectar ao banco de dados. Verifique as credenciais: :error',
            'no_devices' => 'Nenhum dispositivo encontrado correspondendo à especificação fornecida.',
            'none_up' => 'Dispositivo estava fora do ar, não foi possível fazer a coleta dos dados.|Todos os dispositivos estavam fora do ar, não foi possível fazer a coleta dos dados.',
            'none_polled' => 'Nenhum dispositivo teve dados coletados.',
        ],
        'polled' => 'Fez a coleta de dados em :count dispositivos em :time',
    ],
    'key:rotate' => [
        'description' => 'Rotacionar APP_KEY, isso descriptografa todos os dados criptografados com a chave antiga fornecida e os armazena com a nova chave em APP_KEY.',
        'arguments' => [
            'old_key' => 'A antiga APP_KEY que é válida para dados criptografados',
        ],
        'options' => [
            'generate-new-key' => 'Se você não tiver a nova chave definida em .env, use a APP_KEY de .env para descriptografar os dados, gerar uma nova chave e defini-la em .env',
            'forgot-key' => 'Se você não tiver a chave antiga, deve excluir todos os dados criptografados para poder continuar a usar certos recursos do LibreNMS',
        ],
        'destroy' => 'Destruir todos os dados de configuração criptografados?',
        'destroy_confirm' => 'Destrua todos os dados criptografados apenas se não conseguir encontrar a antiga APP_KEY!',
        'cleared-cache' => 'A configuração estava em cache, cache foi limpo para garantir que APP_KEY está correta. Por favor, execute novamente lnms key:rotate',
        'backup_keys' => 'SALVE AMBAS as chaves! Caso algo dê errado, defina a nova chave em .env e use a chave antiga como argumento para este comando',
        'backup_key' => 'SALVE esta chave! Esta chave é necessária para acessar dados criptografados',
        'backups' => 'Este comando pode causar perda irreversível de dados e invalidará todas as sessões do navegador. Certifique-se de ter backups.',
        'confirm' => 'Tenho backups e quero continuar',
        'decrypt-failed' => 'Falha ao descriptografar :item, ignorando',
        'failed' => 'Falha ao descriptografar item(ns). Defina a nova chave como APP_KEY e execute novamente com a chave antiga como argumento.',
        'current_key' => 'APP_KEY atual: :key',
        'new_key' => 'Nova APP_KEY: :key',
        'old_key' => 'Antiga APP_KEY: :key',
        'save_key' => 'Salvar nova chave em .env?',
        'success' => 'Chaves rotacionadas com sucesso!',
        'validation-errors' => [
            'not_in' => ':attribute não deve coincidir com a APP_KEY atual',
            'required' => 'Ou a chave antiga ou --generate-new-key é necessária.',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => 'A opção selecionada :option é inválida. Deve ser um dos: :values',
        ],
    ],
    'maintenance:fetch-ouis' => [
        'description' => 'Buscar MAC OUIs e armazená-los em cache para exibir nomes de fornecedores',
        'options' => [
            'force' => 'Ignorar quaisquer configurações ou bloqueios que impeçam a execução do comando',
            'wait' => 'Aguardar um tempo aleatório, usado pelo agendador para evitar sobrecarga do servidor',
        ],
        'disabled' => 'Integração Mac OUI desativada (:setting)',
        'enable_question' => 'Habilitar integração Mac OUI e busca agendada?',
        'recently_fetched' => 'Banco de dados MAC OUI buscado recentemente, pulando atualização.',
        'waiting' => 'Aguardando :minutes minuto antes de tentar atualizar o MAC OUI|Aguardando :minutes minutos antes de tentar atualizar o MAC OUI',
        'starting' => 'Armazenando Mac OUI no banco de dados',
        'downloading' => 'Baixando',
        'processing' => 'Processando CSV',
        'saving' => 'Salvando resultados',
        'success' => 'Mapeamentos OUI/Fabricante atualizados com sucesso. :count OUI modificado|Atualizado com sucesso. :count OUI modificados',
        'error' => 'Erro ao processar Mac OUI:',
        'vendor_update' => 'Adicionando OUI :oui para :vendor',
    ],
    'plugin:disable' => [
        'description' => 'Desativar todos os plugins com o nome fornecido',
        'arguments' => [
            'plugin' => 'O nome do plugin a ser desativado ou "all" para desativar todos os plugins',
        ],
        'already_disabled' => 'Plugin já desativado',
        'disabled' => ':count plugin desativado|:count plugins desativados',
        'failed' => 'Falha ao desativar plugin(s)',
    ],
    'plugin:enable' => [
        'description' => 'Habilitar o plugin mais recente com o nome fornecido',
        'arguments' => [
            'plugin' => 'O nome do plugin a ser habilitado ou "todos" para desativar todos os plugins',
        ],
        'already_enabled' => 'Plugin já habilitado',
        'enabled' => ':count plugin habilitado|:count plugins habilitados',
        'failed' => 'Falha ao habilitar plugin(s)',
    ],
    'report:devices' => [
        'description' => 'Imprimir dados dos dispositivos',
        'columns' => 'Colunas do banco de dados:',
        'synthetic' => 'Campos adicionais:',
        'counts' => 'Contagens de relacionamento:',
        'arguments' => [
            'device spec' => 'Especificação do dispositivo para coleta de dados: device_id, nome do host, curinga (*), ímpar, par, todos',
        ],
        'options' => [
            'list-fields' => 'Imprimir uma lista de campos válidos',
            'fields' => 'Uma lista de campos separados por vírgulas para exibir. Opções válidas: nomes de colunas do dispositivo no banco de dados, contagens de relacionamento (ports_count) e/ou displayName',
            'output' => 'Formato de saída para exibir os dados :types',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => 'Use um dos --probes e --targets',
        'config-insufficient' => 'Para gerar uma configuração do smokeping, você deve ter definido "smokeping.probes", "fping" e "fping6" em sua configuração',
        'dns-fail' => 'não foi resolvível e foi omitido da configuração',
        'description' => 'Gerar uma configuração adequada para uso com smokeping',
        'header-first' => 'Este arquivo foi gerado automaticamente por "lnms smokeping:generate',
        'header-second' => 'Alterações locais podem ser sobrescritas sem aviso ou backups sendo feitos',
        'header-third' => 'Para mais informações, veja https://docs.librenms.org/Extensions/Smokeping/"',
        'no-devices' => 'Nenhum dispositivo elegível encontrado - dispositivos não devem estar desativados.',
        'no-probes' => 'Pelo menos um probe é necessário.',
        'options' => [
            'probes' => 'Gerar lista de probes - usado para dividir a configuração do smokeping em vários arquivos. Conflita com "--targets"',
            'targets' => 'Gerar a lista de alvos - usado para dividir a configuração do smokeping em vários arquivos. Conflita com "--probes"',
            'no-header' => 'Não adicionar o comentário padrão ao início do arquivo gerado',
            'no-dns' => 'Pular consultas DNS',
            'single-process' => 'Usar apenas um único processo para smokeping',
            'compat' => '[obsoleto] Imitar o comportamento do gen_smokeping.php',
        ],
    ],
    'snmp:fetch' => [
        'description' => 'Executar consulta SNMP em um dispositivo',
        'arguments' => [
            'device spec' => 'Especificação do dispositivo para coleta de dados: device_id, nome do host, curinga (*), ímpar, par, todos',
            'oid(s)' => 'Um ou mais OID SNMP para buscar. Deve ser MIB::oid ou um oid numérico',
        ],
        'failed' => 'Comando SNMP falhou!',
        'numeric' => 'Numérico',
        'oid' => 'OID',
        'options' => [
            'output' => 'Especificar o formato de saída :formats',
            'numeric' => 'OIDs Numéricos',
            'depth' => 'Profundidade para agrupar a tabela snmp. Geralmente o mesmo número de itens no índice da tabela',
        ],
        'not_found' => 'Dispositivo não encontrado',
        'textual' => 'Textual',
        'value' => 'Valor',
    ],
    'translation:generate' => [
        'description' => 'Gerar arquivos json de idioma atualizados para uso na interface web',
    ],
    'user:add' => [
        'description' => 'Adicionar um usuário local, você só pode fazer login com este usuário se a autenticação estiver definida como mysql',
        'arguments' => [
            'username' => 'O nome de usuário com o qual o usuário fará login',
        ],
        'options' => [
            'descr' => 'Descrição do usuário',
            'email' => 'Email a ser usado para o usuário',
            'password' => 'Senha para o usuário, se não fornecida, você será solicitado',
            'full-name' => 'Nome completo do usuário',
            'role' => 'Definir o usuário para o papel desejado :roles',
        ],
        'password-request' => 'Por favor, insira a senha do usuário',
        'success' => 'Usuário adicionado com sucesso: :username',
        'wrong-auth' => 'Aviso! Você não poderá fazer login com este usuário porque não está usando autenticação MySQL',
    ],
    'maintenance:database-cleanup' => [
        'description' => 'Limpeza do banco de dados para remover itens órfãos.',
    ],
];
