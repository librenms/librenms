<?php


$language_data = array(
    'LANG_NAME'      => 'IOS',
    'COMMENT_SINGLE' => array(1 => '!'),
    'CASE_KEYWORDS'  => GESHI_CAPS_LOWER,
    'OOLANG'         => false,
    'NUMBERS'        => GESHI_NUMBER_OCT_PREFIX | GESHI_NUMBER_HEX_PREFIX,
    'KEYWORDS'       => array(
        1 => array(
            'no',
            'shutdown',
        ),
        // 2 => array(
        // 'router', 'interface', 'service', 'config-register', 'upgrade', 'version', 'hostname', 'boot-start-marker', 'boot', 'boot-end-marker', 'enable', 'aaa', 'clock', 'ip',
        // 'logging', 'access-list', 'route-map', 'snmp-server', 'mpls', 'speed', 'media-type', 'negotiation', 'timestamps', 'prefix-list', 'network', 'mask', 'unsuppress-map',
        // 'neighbor', 'remote-as', 'ebgp-multihop', 'update-source', 'description', 'peer-group', 'policy-map', 'class-map', 'class', 'match', 'access-group', 'bandwidth', 'username',
        // 'password', 'send-community', 'next-hop-self', 'route-reflector-client', 'ldp', 'discovery', 'advertise-labels', 'label', 'protocol', 'login', 'debug', 'log', 'duplex', 'router-id',
        // 'authentication', 'mode', 'maximum-paths', 'address-family', 'set', 'local-preference', 'community', 'trap-source', 'location', 'host', 'tacacs-server', 'session-id',
        // 'flow-export', 'destination', 'source', 'in', 'out', 'permit', 'deny', 'control-plane', 'line', 'con' ,'aux', 'vty', 'access-class', 'ntp', 'server', 'end', 'source-interface',
        // 'key', 'chain', 'key-string', 'redundancy', 'match-any', 'queue-limit', 'encapsulation', 'pvc', 'vbr-nrt', 'address', 'bundle-enable', 'atm', 'sonet', 'clns', 'route-cache',
        // 'default-information', 'redistribute', 'log-adjacency-changes', 'metric', 'spf-interval', 'prc-interval', 'lsp-refresh-interval', 'max-lsp-lifetime', 'set-overload-bit',
        // 'on-startup', 'wait-for-bgp', 'system', 'flash', 'timezone', 'subnet-zero', 'cef', 'flow-cache', 'timeout', 'active', 'domain', 'lookup', 'dhcp', 'use', 'vrf', 'hello', 'interval',
        // 'priority', 'ilmi-keepalive', 'buffered', 'debugging', 'fpd', 'secret', 'accounting', 'exec', 'group', 'local', 'recurring', 'source-route', 'call', 'rsvp-sync', 'scripting',
        // 'mtu', 'passive-interface', 'area' , 'distribute-list', 'metric-style', 'is-type', 'originate', 'activate', 'both', 'auto-summary', 'synchronization', 'aggregate-address', 'le', 'ge',
        // 'bgp-community', 'route', 'exit-address-family', 'standard', 'file', 'verify', 'domain-name', 'domain-lookup', 'route-target', 'export', 'import', 'map', 'rd', 'mfib', 'vtp', 'mls',
        // 'hardware-switching', 'replication-mode', 'ingress', 'flow', 'error', 'action', 'slb', 'purge', 'share-global', 'routing', 'traffic-eng', 'tunnels', 'propagate-ttl', 'switchport', 'vlan',
        // 'portfast', 'counters', 'max', 'age', 'ethernet', 'evc', 'uni', 'count', 'oam', 'lmi', 'gmt', 'netflow', 'pseudowire-class', 'spanning-tree', 'name', 'circuit-type'
        // ),
        // 3 => array(
        // 'isis', 'ospf', 'eigrp', 'rip', 'igrp', 'bgp', 'ipv4', 'unicast', 'multicast', 'ipv6', 'connected', 'static', 'subnets', 'tcl'
        // ),
        // 4 => array(
        // 'point-to-point', 'aal5snap', 'rj45', 'auto', 'full', 'half', 'precedence', 'percent', 'datetime', 'msec', 'locatime', 'summer-time', 'md5', 'wait-for-bgp', 'wide',
        // 'level-1', 'level-2', 'log-neighbor-changes', 'directed-request', 'password-encryption', 'common', 'origin-as', 'bgp-nexthop', 'random-detect', 'localtime', 'sso', 'stm-1',
        // 'dot1q', 'isl', 'new-model', 'always', 'summary-only', 'freeze', 'global', 'forwarded', 'access', 'trunk', 'edge', 'transparent'
        // ),
    ),

    'REGEXPS'        => array(
        1  => array(
            GESHI_SEARCH  => '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})',
            GESHI_REPLACE => '\\1',
            GESHI_BEFORE  => '',
        ),
        2  => array(
            GESHI_SEARCH  => '(255\.\d{1,3}\.\d{1,3}\.\d{1,3})',
            GESHI_REPLACE => '\\1',
            GESHI_BEFORE  => '',
        ),
        3  => array(
            GESHI_SEARCH  => '(source|interface|update-source|router-id) ([A-Za-z0-9\/\:\-\.]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        4  => array(
            GESHI_SEARCH  => '(neighbor) ([\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}]+|[a-zA-Z0-9\-\_]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        5  => array(
            GESHI_SEARCH  => '(distribute-map|access-group|policy-map|class-map\ match-any|ip\ access-list\ extended|match\ community|community-list\ standard|community-list\ expanded|ip\ access-list\ standard|router\ bgp|remote-as|key\ chain|service-policy\ input|service-policy\ output|class|login\ authentication|authentication\ key-chain|username|import\ map|export\ map|domain-name|hostname|route-map|access-class|ip\ vrf\ forwarding|ip\ vrf|vtp\ domain|name|pseudowire-class|pw-class|prefix-list|vrf) ([A-Za-z0-9\-\_\.]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        6  => array(
            GESHI_SEARCH  => '(password|key-string|key) ([0-9]) (.+)',
            GESHI_REPLACE => '\\2 \\3',
            GESHI_BEFORE  => '\\1 ',
        ),
        7  => array(
            GESHI_SEARCH  => '(enable) ([a-z]+) ([0-9]) (.+)',
            GESHI_REPLACE => '\\3 \\4',
            GESHI_BEFORE  => '\\1 \\2 ',
        ),
        8  => array(
            GESHI_SEARCH  => '(description|location|contact|remark) (.+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        9  => array(
            GESHI_SEARCH  => '([0-9\.\_\*]+\:[0-9\.\_\*]+)',
            GESHI_REPLACE => '\\1',
        ),
        10 => array(
            GESHI_SEARCH  => '(boot) ([a-z]+) (.+)',
            GESHI_REPLACE => '\\3',
            GESHI_BEFORE  => '\\1 \\2 ',
        ),
        11 => array(
            GESHI_SEARCH  => '(net) ([0-9a-z\.]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        12 => array(
            GESHI_SEARCH  => '(access-list|RO|RW) ([0-9]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        13 => array(
            GESHI_SEARCH  => '(vlan) ([0-9]+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),
        14 => array(
            GESHI_SEARCH  => '(encapsulation|speed|duplex|mtu|metric|media-type|negotiation|transport\ input|bgp-community|set\ as-path\ prepend|maximum-prefix|version|local-preference|continue|redistribute|cluster-id|vtp\ mode|label\ protocol|spanning-tree\ mode) (.+)',
            GESHI_REPLACE => '\\2',
            GESHI_BEFORE  => '\\1 ',
        ),

    ),

    'STYLES'         => array(
        'REGEXPS'     => array(
            0  => 'color: #ff0000;',
            1  => 'color: #0000cc;',
            // x.x.x.x
            2  => 'color: #000099; font-style: italic',
            // 255.x.x.x
            3  => 'color: #000000; font-weight: bold; font-style: italic;',
            // interface xxx
            4  => 'color: #ff0000;',
            // neighbor x.x.x.x
            5  => 'color: #000099;',
            // variable names
            6  => 'color: #cc0000;',
            7  => 'color: #cc0000;',
            // passwords
            8  => 'color: #555555;',
            // description
            9  => 'color: #990099;',
            // communities
            10 => 'color: #cc0000; font-style: italic;',
            // no/shut
            11 => 'color: #000099;',
            // net numbers
            12 => 'color: #000099;',
            // acls
            13 => 'color: #000099;',
            // acls
            14 => 'color: #990099;',
            // warnings
        ),
        'KEYWORDS'    => array(
            1 => 'color: #cc0000; font-weight: bold;',
            // no/shut
            2 => 'color: #000000;',
            // commands
            3 => 'color: #000000; font-weight: bold;',
            // proto/service
            4 => 'color: #000000;',
            // options
            5 => 'color: #ff0000;',
        ),
        'COMMENTS'    => array(1 => 'color: #808080; font-style: italic;'),
        'ESCAPE_CHAR' => array(0 => 'color: #000099; font-weight: bold;'),
        'BRACKETS'    => array(0 => 'color: #66cc66;'),
        'STRINGS'     => array(0 => 'color: #ff0000;'),
        'NUMBERS'     => array(0 => 'color: #cc0000;'),
        'METHODS'     => array(0 => 'color: #006600;'),
        'SYMBOLS'     => array(0 => 'color: #66cc66;'),
        'SCRIPT'      => array(
            0 => '',
            1 => '',
            2 => '',
            3 => '',
        ),
    ),
);
