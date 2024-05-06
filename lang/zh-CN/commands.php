<?php

return [
    'config:get' => [
        'description' => '获取配置值',
        'arguments' => [
            'setting' => '以点号表示法获取值的设置（示例：snmp.community.0）',
        ],
        'options' => [
            'dump' => '以JSON格式输出整个配置',
        ],
    ],
    'config:set' => [
        'description' => '设置配置值（或删除）',
        'arguments' => [
            'setting' => '以点号表示法设置的设置（示例：snmp.community.0）。要附加到数组，请在末尾加上.+',
            'value' => '要设置的值，如果忽略此值则删除设置',
        ],
        'options' => [
            'ignore-checks' => '忽略所有安全检查',
        ],
        'confirm' => '重置:setting为默认值吗？',
        'forget_from' => '从:parent中忘记:path吗？',
        'errors' => [
            'append' => '无法向非数组设置追加',
            'failed' => '设置:setting失败',
            'invalid' => '这不是有效的设置。请检查您的输入',
            'invalid_os' => '指定的OS(:os)不存在',
            'nodb' => '数据库未连接',
            'no-validation' => '无法设置:setting，缺少验证定义。',
        ],
    ],
    'db:seed' => [
        'existing_config' => '数据库中存在现有设置。继续吗？',
    ],
    'dev:check' => [
        'description' => 'LibreNMS代码检查。不带选项运行时运行所有检查',
        'arguments' => [
            'check' => '运行指定的检查:checks',
        ],
        'options' => [
            'commands' => '仅打印将要运行的命令，不执行检查',
            'db' => '运行需要数据库连接的单元测试',
            'fail-fast' => '遇到任何失败时停止检查',
            'full' => '运行完整检查，忽略已更改文件过滤',
            'module' => '要运行测试的具体模块。意味着unit, --db, --snmpsim',
            'os' => '要运行测试的特定操作系统。可以是正则表达式或逗号分隔的列表。意味着unit, --db, --snmpsim',
            'os-modules-only' => '在指定特定操作系统时跳过OS检测测试。当检查非检测更改时，可以加快测试时间。',
            'quiet' => '除非有错误，否则隐藏输出',
            'snmpsim' => '用于单元测试的snmpsim',
        ],
    ],
    'dev:simulate' => [
        'description' => '使用测试数据模拟设备',
        'arguments' => [
            'file' => '要更新或添加到LibreNMS的snmprec文件的基本文件名。如果没有指定文件，则不会添加或更新设备。',
        ],
        'options' => [
            'multiple' => '使用社区名称作为主机名，而不是snmpsim',
            'remove' => '停止后删除设备',
        ],
        'added' => '设备:hostname (:id) 已添加',
        'exit' => '按Ctrl-C停止',
        'removed' => '设备:id 已移除',
        'updated' => '设备:hostname (:id) 已更新',
    ],
    'device:add' => [
        'description' => '添加新设备',
        'arguments' => [
            'device spec' => '要添加的主机名或IP',
        ],
        'options' => [
            'v1' => '使用SNMP v1',
            'v2c' => '使用SNMP v2c',
            'v3' => '使用SNMP v3',
            'display-name' => "用于显示此设备名称的字符串，默认为主机名。\n可以使用替换模板：{{ \$hostname }}, {{ \$sysName }}, {{ \$sysName_fallback }}, {{ \$ip }}",
            'force' => '直接添加设备，不进行任何安全性检查',
            'group' => '分布式轮询的轮询组',
            'ping-fallback' => '如果设备不响应SNMP，则将其添加为ping仅设备',
            'port-association-mode' => '设置端口映射方式。对于Linux/Unix建议使用ifName',
            'community' => 'SNMP v1或v2社区',
            'transport' => '连接到设备的传输',
            'port' => 'SNMP传输端口',
            'security-name' => 'SNMPv3安全用户名',
            'auth-password' => 'SNMPv3认证密码',
            'auth-protocol' => 'SNMPv3认证协议',
            'privacy-protocol' => 'SNMPv3隐私协议',
            'privacy-password' => 'SNMPv3隐私密码',
            'ping-only' => '添加ping仅设备',
            'os' => 'ping仅设备：指定操作系统',
            'hardware' => 'ping仅设备：指定硬件',
            'sysName' => 'ping仅设备：指定sysName',
        ],
        'validation-errors' => [
            'port.between' => '端口应为1-65535',
            'poller-group.in' => '给定的轮询组不存在',
        ],
        'messages' => [
            'save_failed' => '保存设备:hostname失败',
            'try_force' => '您可以尝试使用--force选项跳过安全性检查',
            'added' => '添加了设备:hostname (:device_id)',
        ],
    ],
    'device:ping' => [
        'description' => 'ping设备并记录响应数据',
        'arguments' => [
            'device spec' => '要ping的设备之一：<设备ID>，<主机名/IP>，all',
        ],
    ],
    'device:poll' => [
        'description' => '根据发现从设备(s)中获取数据',
        'arguments' => [
            'device spec' => '要轮询的设备规范：device_id，hostname，通配符(*)，奇数，偶数，all',
        ],
        'options' => [
            'modules' => '指定要运行的单个模块。用逗号分隔模块，可以添加子模块/',
            'no-data' => '不更新数据存储（RRD，InfluxDB等）',
        ],
        'errors' => [
            'db_connect' => '连接数据库失败。请检查数据库服务是否正在运行以及连接设置。',
            'db_auth' => '连接数据库失败。请检查凭据：:error',
            'no_devices' => '找不到匹配您给出的设备规范的设备。',
            'none_up' => '设备处于离线状态，无法轮询。|所有设备都处于离线状态，无法轮询。',
            'none_polled' => '没有设备被轮询。',
        ],
        'polled' => '在:time内轮询了:count台设备',
    ],
    'key:rotate' => [
        'description' => '旋转APP_KEY，此操作会使用给定的旧密钥解密所有加密数据，并使用新密钥存储在APP_KEY中。',
        'arguments' => [
            'old_key' => '适用于加密数据的有效旧APP_KEY',
        ],
        'options' => [
            'generate-new-key' => '如果您没有在.env中设置新密钥，请使用.env中的APP_KEY解密数据并生成新密钥并设置到.env中',
            'forgot-key' => '如果您没有旧密钥，您必须删除所有加密数据才能继续使用LibreNMS的某些功能',
        ],
        'destroy' => '是否销毁所有加密的配置数据？',
        'destroy_confirm' => '仅在找不到旧APP_KEY时才销毁所有加密数据！',
        'cleared-cache' => '配置已缓存，已清除缓存以确保APP_KEY正确。请重新运行lnms key:rotate',
        'backup_keys' => '请记录这两个密钥！一旦出现问题，请在.env中设置新密钥，并将旧密钥作为参数传递给此命令',
        'backup_key' => '请记录此密钥！此密钥用于访问加密数据',
        'backups' => '此命令可能导致数据不可逆丢失，并使所有浏览器会话失效。请确保您有备份。',
        'confirm' => '我有备份并希望继续',
        'decrypt-failed' => '未能解密:item，跳过',
        'failed' => '未能解密项目。设置新密钥为APP_KEY并再次运行此命令，同时将旧密钥作为参数',
        'current_key' => '当前APP_KEY: :key',
        'new_key' => '新APP_KEY: :key',
        'old_key' => '旧APP_KEY: :key',
        'save_key' => '是否将新密钥保存到.env？',
        'success' => '密钥旋转成功！',
        'validation-errors' => [
            'not_in' => ':attribute不能与当前APP_KEY匹配',
            'required' => '需要旧密钥或--generate-new-key选项之一',
        ],
    ],
    'lnms' => [
        'validation-errors' => [
            'optionValue' => '所选:option无效。应为以下之一: :values',
        ],
    ],
    'maintenance:fetch-ouis' => [
        'description' => '获取MAC OUI并将其缓存以显示MAC地址的供应商名称',
        'options' => [
            'force' => '忽略任何阻止命令运行的设置或锁定',
            'wait' => '等待随机时间，调度器使用此功能防止服务器负载',
        ],
        'disabled' => 'MAC OUI集成已禁用(:setting)',
        'enable_question' => '启用MAC OUI集成及定期获取？',
        'recently_fetched' => 'MAC OUI数据库最近已获取，跳过更新。',
        'waiting' => '等待:minutes分钟后尝试MAC OUI更新|等待:分钟分钟后尝试MAC OUI更新',
        'starting' => '正在存储Mac OUI至数据库',
        'downloading' => '下载中',
        'processing' => '处理CSV',
        'saving' => '保存结果',
        'success' => '成功更新OUI/供应商映射。:count个修改的OUI|成功更新。:count个修改的OUIs',
        'error' => '处理Mac OUI时出错:',
        'vendor_update' => '为:vendor添加OUI :oui',
    ],
    'plugin:disable' => [
        'description' => '禁用给定名称的所有插件',
        'arguments' => [
            'plugin' => '要禁用的插件名称，或"all"以禁用所有插件',
        ],
        'already_disabled' => '插件已被禁用',
        'disabled' => ':count个插件被禁用|:count个插件被禁用',
        'failed' => '未能禁用插件',
    ],
    'plugin:enable' => [
        'description' => '启用具有给定名称的最新插件',
        'arguments' => [
            'plugin' => '要启用的插件名称，或"all"以启用所有插件',
        ],
        'already_enabled' => '插件已启用',
        'enabled' => ':count个插件已启用|:count个插件已启用',
        'failed' => '未能启用插件',
    ],
    'report:devices' => [
        'description' => '输出设备数据',
        'columns' => '数据库列:',
        'synthetic' => '附加字段:',
        'counts' => '关系计数:',
        'arguments' => [
            'device spec' => '要查询的设备规格：device_id, hostname, 通配符(*), 奇数, 偶数, 全部',
        ],
        'options' => [
            'list-fields' => '打印有效字段列表',
            'fields' => '逗号分隔的要显示的字段列表。有效选项：设备数据库列名，关系计数(ports_count)，以及displayName',
            'output' => '用于显示数据的输出格式:types',
        ],
    ],
    'smokeping:generate' => [
        'args-nonsense' => '请使用 --probes 和 --targets 中的一个',
        'config-insufficient' => '为了生成smokeping配置文件，您必须在配置中设置 "smokeping.probes"、"fping" 和 "fping6"',
        'dns-fail' => '无法解析，已从配置中省略',
        'description' => '生成适用于smokeping的配置文件',
        'header-first' => '此文件由 "lnms smokeping:generate" 自动生成',
        'header-second' => '本地更改可能会在未通知或未备份的情况下被覆盖',
        'header-third' => '更多信息请参见 https://docs.librenms.org/Extensions/Smokeping/"',
        'no-devices' => '未找到符合条件的设备 - 设备不能被禁用。',
        'no-probes' => '至少需要一个探测器。',
        'options' => [
            'probes' => '生成探测器列表 - 用于将smokeping配置分割成多个文件。与 "--targets" 冲突',
            'targets' => '生成目标列表 - 用于将smokeping配置分割成多个文件。与 "--probes" 冲突',
            'no-header' => '不在生成的文件开头添加模板注释',
            'no-dns' => '跳过DNS查询',
            'single-process' => '仅使用单个进程运行smokeping',
            'compat' => '[已弃用] 模仿gen_smokeping.php的行为',
        ],
    ],
    'snmp:fetch' => [
        'description' => '针对设备运行SNMP查询',
        'arguments' => [
            'device spec' => '要轮询的设备规格：device_id, hostname, 通配符(*), 奇数, 偶数, 全部',
            'oid(s)' => '一个或多个要获取的SNMP OID。应为MIB::oid或数字oid',
        ],
        'failed' => 'SNMP命令执行失败！',
        'oid' => 'OID',
        'options' => [
            'output' => '指定输出格式 :formats',
            'numeric' => '数字OID',
            'depth' => 'SNMP表分组的深度。通常与表索引中的项目数量相同',
        ],
        'not_found' => '设备未找到',
        'value' => '值',
    ],
    'translation:generate' => [
        'description' => '生成更新后的json语言文件，供Web前端使用',
    ],
    'user:add' => [
        'description' => '添加本地用户，仅当auth设置为mysql时，您才能使用此用户登录',
        'arguments' => [
            'username' => '用户登录使用的用户名',
        ],
        'options' => [
            'descr' => '用户描述',
            'email' => '用户的电子邮件地址',
            'password' => '用户的密码，如果不提供，系统会提示输入',
            'full-name' => '用户的全名',
            'role' => '将用户设置为所需角色 :roles',
        ],
        'password-request' => '请输入用户的密码',
        'success' => '成功添加用户: :username',
        'wrong-auth' => '警告！由于您未使用MySQL身份验证，您将无法使用此用户登录',
    ],
];
