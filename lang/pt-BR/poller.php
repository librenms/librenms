<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => 'Grupos Atribuídos',
                'help' => 'Este nó só agirá em dispositivos nestes grupos de coleta de dados.',
            ],
            'poller_enabled' => [
                'description' => 'Coletor de Dados Habilitado',
                'help' => 'Habilitar workers de coleta de dados neste nó.',
            ],
            'poller_workers' => [
                'description' => 'Workers de Coleta de Dados',
                'help' => 'Quantidade de workers de coleta de dados a serem gerados neste nó.',
            ],
            'poller_frequency' => [
                'description' => 'Frequência da Coleta de Dados (Aviso!)',
                'help' => 'Com que frequência coletar dados de dispositivos neste nó. Aviso! Alterar isso sem corrigir os arquivos rrd corromperá os gráficos. Leia a documentação para mais informações.',
            ],
            'poller_down_retry' => [
                'description' => 'Repetição para Dispositivo Offline',
                'help' => 'Se um dispositivo estiver offline quando a coleta for tentada neste nó, este é o tempo de espera antes de tentar novamente.',
            ],
            'discovery_enabled' => [
                'description' => 'Descoberta Habilitada',
                'help' => 'Habilitar workers de descoberta neste nó.',
            ],
            'discovery_workers' => [
                'description' => 'Workers de Descoberta',
                'help' => 'Quantidade de workers de descoberta a serem executados neste nó. Definir um valor muito alto pode causar sobrecarga.',
            ],
            'discovery_frequency' => [
                'description' => 'Frequência de Descoberta',
                'help' => 'Com que frequência executar a descoberta de dispositivos neste nó. O padrão é 4 vezes ao dia.',
            ],
            'services_enabled' => [
                'description' => 'Serviços Habilitados',
                'help' => 'Habilitar workers de serviços neste nó.',
            ],
            'services_workers' => [
                'description' => 'Workers de Serviços',
                'help' => 'Quantidade de workers de serviços neste nó.',
            ],
            'services_frequency' => [
                'description' => 'Frequência dos Serviços',
                'help' => 'Com que frequência executar serviços neste nó. Isso deve corresponder à frequência do coletor de dados.',
            ],
            'billing_enabled' => [
                'description' => 'Tarifação Habilitada',
                'help' => 'Habilitar workers de tarifação neste nó.',
            ],
            'billing_frequency' => [
                'description' => 'Frequência de Tarifação',
                'help' => 'Com que frequência coletar dados de tarifação neste nó.',
            ],
            'billing_calculate_frequency' => [
                'description' => 'Frequência de Cálculo de Tarifação',
                'help' => 'Com que frequência calcular o uso da tarifação neste nó.',
            ],
            'alerting_enabled' => [
                'description' => 'Alertas Habilitados',
                'help' => 'Habilitar o worker de alertas neste nó.',
            ],
            'alerting_frequency' => [
                'description' => 'Frequência dos Alertas',
                'help' => 'Com que frequência as regras de alerta são verificadas neste nó. Note que os dados são atualizados apenas com base na frequência do coletor de dados.',
            ],
            'ping_enabled' => [
                'description' => 'Fast Ping Habilitado',
                'help' => 'Fast Ping apenas pinga dispositivos para verificar se estão ativos ou inativos',
            ],
            'ping_frequency' => [
                'description' => 'Frequência do Ping',
                'help' => 'Com que frequência verificar o ping neste nó. Aviso! Se você mudar isso, deve fazer alterações adicionais. Verifique a documentação do Fast Ping.',
            ],
            'update_enabled' => [
                'description' => 'Manutenção Diária Habilitada',
                'help' => 'Executar o script de manutenção daily.sh e reiniciar o serviço de dispatcher posteriormente.',
            ],
            'update_frequency' => [
                'description' => 'Frequência da Manutenção',
                'help' => 'Com que frequência executar a manutenção diária neste nó. O padrão é 1 dia. É altamente recomendável não alterar isso.',
            ],
            'loglevel' => [
                'description' => 'Nível de Log',
                'help' => 'Nível de log do serviço de dispatcher.',
            ],
            'watchdog_enabled' => [
                'description' => 'Watchdog Habilitado',
                'help' => 'O Watchdog monitora o arquivo de log e reinicia o serviço se não tiver sido atualizado',
            ],
            'watchdog_log' => [
                'description' => 'Arquivo de Log a ser Monitorardo',
                'help' => 'O padrão é o arquivo de log do LibreNMS.',
            ],
        ],
        'units' => [
            'seconds' => 'Segundos',
            'workers' => 'Workers',
        ],
    ],
];
