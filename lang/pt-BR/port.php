<?php

return [
    'groups' => [
        'updated' => 'Grupos de :port atualizados',
        'none' => ':port nenhuma atualização solicitada',
    ],
    'filters' => [
        'status_up' => 'Mostrar Apenas Ativos',
        'admin_down' => 'Mostrar Administrativamente Inativos',
        'disabled' => 'Mostrar Desativados',
        'ignored' => 'Mostrar Ignorados',
    ],
    'graphs' => [
        'bits' => 'Bits',
        'upkts' => 'Pacotes Unicast',
        'nupkts' => 'Pacotes Não-Unicast',
        'errors' => 'Erros',
        'etherlike' => 'Etherlike',
    ],
    'mtu_label' => 'MTU :mtu',
    'tabs' => [
        'arp' => 'Tabela ARP',
        'fdb' => 'Tabela FDB',
        'links' => 'Vizinhos',
        'xdsl' => 'xDSL',
    ],
    'unknown_port' => 'Porta Desconhecida',
    'vlan_count' => 'VLANs: :count',
    'vlan_label' => 'VLAN: :label',
    'vrf_label' => 'VRF: :name',
    'xdsl' => [
        'sync_stat' => 'Sinc: :down/:up',
        'attainable_stat' => 'Máx: :down/:up',
        'attenuation_stat' => 'Aten: :down/:up',
        'snr_stat' => 'SNR: :down/:up',
        'sync' => 'Velocidade de Sincronização',
        'attainable' => 'Velocidade Atingível',
        'attenuation' => 'Atenuação',
        'snr' => 'Margem SNR',
        'power' => 'Potências de Saída',
    ],
];
