<?php

return [
    'groups' => [
        'updated' => ':port: groups updated',
        'none' => ':port no update requested',
    ],
    'filters' => [
        'status_up' => 'Only Show Up',
        'admin_down' => 'Show Admin Down',
        'disabled' => 'Show Disabled',
        'ignored' => 'Show Ignored',
    ],
    'graphs' => [
        'bits' => 'Bits',
        'upkts' => 'Unicast Packets',
        'nupkts' => 'Non-Unicast Packets',
        'errors' => 'Errors',
        'etherlike' => 'Etherlike',
    ],
    'mtu_label' => 'MTU :mtu',
    'tabs' => [
        'arp' => 'ARP Table',
        'fdb' => 'FDB Table',
        'links' => 'Neighbors',
        'xdsl' => 'xDSL',
    ],
    'unknown_port' => 'Unknown Port',
    'vlan_count' => 'VLANs: :count',
    'vlan_label' => 'VLAN: :label',
    'vrf_label' => 'VRF: :name',
    'xdsl' => [
        'sync_stat' => 'Sync: :down/:up',
        'attainable_stat' => 'Max: :down/:up',
        'attenuation_stat' => 'Atten: :down/:up',
        'snr_stat' => 'SNR: :down/:up',
        'sync' => 'Sync Speed',
        'attainable' => 'Attainable Speed',
        'attenuation' => 'Attenuation',
        'snr' => 'SNR Margin',
        'power' => 'Output Powers',
    ],
];
