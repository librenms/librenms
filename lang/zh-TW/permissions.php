<?php

return [
    'device' => [
        'title' => '裝置',
        'viewAll' => [
            'label' => '檢視所有裝置',
            'description' => '檢視所有裝置',
        ],
        'view' => [
            'label' => '檢視裝置詳細資料',
            'description' => '檢視使用者可存取的裝置',
        ],
        'create' => [
            'label' => '新增裝置',
            'description' => '將新裝置加入 LibreNMS',
        ],
        'update' => [
            'label' => '編輯裝置',
            'description' => '修改裝置設定',
        ],
        'delete' => [
            'label' => '刪除裝置',
            'description' => '從 LibreNMS 移除裝置',
        ],
        'debug' => [
            'label' => '除錯裝置',
            'description' => '對裝置執行 snmpwalk 及其他除錯指令',
        ],
        'showConfig' => [
            'label' => '顯示裝置組態',
            'description' => '顯示裝置組態',
        ],
        'updateNotes' => [
            'label' => '更新裝置備註',
            'description' => '更新裝置備註',
        ],
    ],
    'alert' => [
        'title' => '警報',
        'viewAll' => [
            'label' => '檢視所有警報',
            'description' => '檢視所有警報',
        ],
        'view' => [
            'label' => '檢視警報詳細資料',
            'description' => '檢視使用者可存取裝置的警報',
        ],
        'detail' => [
            'label' => '檢視警報詳細資料',
            'description' => '檢視警報詳細資訊',
        ],
        'update' => [
            'label' => '編輯警報',
            'description' => '確認或修改警報',
        ],
        'delete' => [
            'label' => '刪除警報',
            'description' => '刪除警報歷史記錄',
        ],
    ],
    'alert-rule' => [
        'title' => '警報規則',
        'viewAll' => [
            'label' => '檢視所有警報規則',
            'description' => '檢視所有警報規則',
        ],
        'view' => [
            'label' => '檢視警報規則',
            'description' => '檢視使用者可存取裝置的警報規則詳細資料',
        ],
        'create' => [
            'label' => '建立警報規則',
            'description' => '建立新的警報規則',
        ],
        'update' => [
            'label' => '編輯警報規則',
            'description' => '修改現有的警報規則',
        ],
        'delete' => [
            'label' => '刪除警報規則',
            'description' => '刪除警報規則',
        ],
    ],
    'alert-schedule' => [
        'title' => '警報排程',
        'view' => [
            'label' => '檢視警報排程',
            'description' => '檢視警報排程詳細資料',
        ],
        'create' => [
            'label' => '建立警報排程',
            'description' => '建立新的警報排程',
        ],
        'update' => [
            'label' => '編輯警報排程',
            'description' => '修改現有的警報排程',
        ],
        'delete' => [
            'label' => '刪除警報排程',
            'description' => '刪除警報排程',
        ],
    ],
    'alert-template' => [
        'title' => '警報範本',
        'view' => [
            'label' => '檢視警報範本',
            'description' => '檢視警報範本',
        ],
        'create' => [
            'label' => '建立警報範本',
            'description' => '建立新的警報範本',
        ],
        'update' => [
            'label' => '編輯警報範本',
            'description' => '修改現有的警報範本',
        ],
        'delete' => [
            'label' => '刪除警報範本',
            'description' => '刪除警報範本',
        ],
    ],
    'alert-transport' => [
        'title' => '警報傳輸',
        'view' => [
            'label' => '檢視警報傳輸',
            'description' => '檢視警報傳輸',
        ],
        'create' => [
            'label' => '建立警報傳輸',
            'description' => '建立新的警報傳輸',
        ],
        'update' => [
            'label' => '編輯警報傳輸',
            'description' => '修改現有的警報傳輸',
        ],
        'delete' => [
            'label' => '刪除警報傳輸',
            'description' => '刪除警報傳輸',
        ],
    ],
    'api' => [
        'title' => 'API 存取',
        'access' => [
            'label' => 'API 存取',
            'description' => '存取 LibreNMS REST API',
        ],
    ],
    'application' => [
        'title' => '應用程式',
        'update' => [
            'label' => '更新應用程式',
            'description' => '更新應用程式資料',
        ],
    ],
    'auth-log' => [
        'title' => '驗證記錄',
        'view' => [
            'label' => '檢視驗證記錄',
            'description' => '檢視驗證記錄',
        ],
    ],
    'bill' => [
        'title' => '帳單',
        'viewAll' => [
            'label' => '檢視所有帳單',
            'description' => '檢視所有帳務記錄',
        ],
        'view' => [
            'label' => '檢視帳單詳細資料',
            'description' => '檢視使用者可存取帳單的帳務詳細資料與圖表',
        ],
        'create' => [
            'label' => '建立帳單',
            'description' => '建立新的帳務記錄',
        ],
        'update' => [
            'label' => '編輯帳單',
            'description' => '修改帳務設定',
        ],
        'delete' => [
            'label' => '刪除帳單',
            'description' => '移除帳務記錄',
        ],
    ],
    'component' => [
        'title' => '元件',
        'update' => [
            'label' => '更新元件',
            'description' => '更新元件資料',
        ],
    ],
    'custom-map' => [
        'title' => '地圖',
        'viewAll' => [
            'label' => '檢視所有地圖',
            'description' => '檢視所有網路地圖',
        ],
        'view' => [
            'label' => '檢視地圖',
            'description' => '檢視包含使用者可存取裝置的網路地圖',
        ],
        'create' => [
            'label' => '建立地圖',
            'description' => '建立新的網路地圖',
        ],
        'update' => [
            'label' => '編輯地圖',
            'description' => '修改現有的網路地圖',
        ],
        'delete' => [
            'label' => '刪除地圖',
            'description' => '刪除網路地圖',
        ],
    ],
    'dashboard' => [
        'title' => '資訊看板',
        'copy' => [
            'label' => '複製資訊看板',
            'description' => '複製其他使用者的資訊看板',
        ],
    ],
    'device-group' => [
        'title' => '裝置群組',
        'viewAll' => [
            'label' => '檢視所有裝置群組',
            'description' => '檢視所有裝置群組',
        ],
        'view' => [
            'label' => '檢視裝置群組',
            'description' => '檢視包含使用者可存取裝置的裝置群組',
        ],
        'create' => [
            'label' => '建立裝置群組',
            'description' => '建立新的裝置群組',
        ],
        'update' => [
            'label' => '編輯裝置群組',
            'description' => '修改現有的裝置群組',
        ],
        'delete' => [
            'label' => '刪除裝置群組',
            'description' => '刪除裝置群組',
        ],
    ],
    'link' => [
        'title' => '連結',
        'viewAll' => [
            'label' => '檢視所有連結',
            'description' => '檢視網路連結資訊',
        ],
    ],
    'location' => [
        'title' => '位置',
        'viewAll' => [
            'label' => '檢視所有位置',
            'description' => '檢視所有位置',
        ],
        'view' => [
            'label' => '檢視位置',
            'description' => '檢視與使用者可存取裝置相關的位置',
        ],
        'create' => [
            'label' => '建立位置',
            'description' => '建立新的位置',
        ],
        'update' => [
            'label' => '編輯位置',
            'description' => '修改現有的位置',
        ],
        'delete' => [
            'label' => '刪除位置',
            'description' => '刪除位置',
        ],
    ],
    'mempool' => [
        'title' => '記憶體集區',
        'update' => [
            'label' => '更新記憶體集區',
            'description' => '更新記憶體集區資料',
        ],
    ],
    'notification' => [
        'title' => '通知',
        'create' => [
            'label' => '建立通知',
            'description' => '建立新的通知',
        ],
        'update' => [
            'label' => '編輯通知',
            'description' => '修改現有的通知',
        ],
    ],
    'oxidized' => [
        'title' => 'Oxidized',
        'view' => [
            'label' => '檢視 Oxidized',
            'description' => '檢視裝置組態備份',
        ],
        'refresh' => [
            'label' => '重新整理 Oxidized',
            'description' => '觸發重新擷取裝置的組態',
        ],
        'search' => [
            'label' => '搜尋 Oxidized',
            'description' => '搜尋 Oxidized 組態備份',
        ],
    ],
    'peering-db' => [
        'title' => 'PeeringDB',
        'view' => [
            'label' => '檢視 PeeringDB',
            'description' => '檢視 PeeringDB 資訊',
        ],
    ],
    'plugin' => [
        'title' => '外掛程式',
        'admin' => [
            'label' => '外掛程式管理',
            'description' => '管理外掛程式設定與狀態',
        ],
    ],
    'poller' => [
        'title' => '輪詢器',
        'view' => [
            'label' => '檢視輪詢器',
            'description' => '檢視輪詢器資訊與狀態',
        ],
        'update' => [
            'label' => '編輯輪詢器',
            'description' => '修改輪詢器設定',
        ],
        'delete' => [
            'label' => '刪除輪詢器',
            'description' => '從 LibreNMS 移除輪詢器',
        ],
    ],
    'poller-group' => [
        'title' => '輪詢器群組',
        'create' => [
            'label' => '建立輪詢器群組',
            'description' => '建立新的輪詢器群組',
        ],
        'update' => [
            'label' => '編輯輪詢器群組',
            'description' => '修改現有的輪詢器群組',
        ],
        'delete' => [
            'label' => '刪除輪詢器群組',
            'description' => '刪除輪詢器群組',
        ],
    ],
    'port' => [
        'title' => '連接埠',
        'viewAll' => [
            'label' => '檢視所有連接埠',
            'description' => '檢視所有連接埠',
        ],
        'view' => [
            'label' => '檢視連接埠詳細資料',
            'description' => '檢視使用者可存取裝置或連接埠的連接埠',
        ],
        'update' => [
            'label' => '編輯連接埠',
            'description' => '修改連接埠描述與設定',
        ],
        'delete' => [
            'label' => '刪除連接埠',
            'description' => '永久刪除連接埠及其資料',
        ],
    ],
    'port-group' => [
        'title' => '連接埠群組',
        'viewAll' => [
            'label' => '檢視所有連接埠群組',
            'description' => '檢視所有連接埠群組',
        ],
        'view' => [
            'label' => '檢視連接埠群組',
            'description' => '檢視包含使用者可存取連接埠的連接埠群組',
        ],
        'create' => [
            'label' => '建立連接埠群組',
            'description' => '建立新的連接埠群組',
        ],
        'update' => [
            'label' => '編輯連接埠群組',
            'description' => '修改現有的連接埠群組',
        ],
        'delete' => [
            'label' => '刪除連接埠群組',
            'description' => '刪除連接埠群組',
        ],
    ],
    'processor' => [
        'title' => '處理器',
        'viewAll' => [
            'label' => '檢視所有處理器',
            'description' => '檢視所有處理器',
        ],
        'view' => [
            'label' => '檢視處理器',
            'description' => '檢視使用者可存取裝置的處理器',
        ],
        'update' => [
            'label' => '更新處理器',
            'description' => '更新處理器資料',
        ],
    ],
    'reporting' => [
        'title' => '報表',
        'update' => [
            'label' => '更新報表',
            'description' => '更新報表設定',
        ],
    ],
    'role' => [
        'title' => '角色',
        'update' => [
            'label' => '編輯角色',
            'description' => '修改角色權限與設定',
        ],
    ],
    'routing' => [
        'title' => '路由',
        'viewAll' => [
            'label' => '檢視所有路由',
            'description' => '檢視所有路由資訊',
        ],
        'view' => [
            'label' => '檢視路由',
            'description' => '檢視特定路由詳細資料',
        ],
        'update' => [
            'label' => '更新路由',
            'description' => '更新路由資料',
        ],
    ],
    'service' => [
        'title' => '服務',
        'viewAll' => [
            'label' => '檢視所有服務',
            'description' => '檢視所有服務',
        ],
        'view' => [
            'label' => '檢視服務',
            'description' => '檢視使用者可存取裝置的服務',
        ],
        'create' => [
            'label' => '新增服務',
            'description' => '將新服務加入裝置',
        ],
        'update' => [
            'label' => '編輯服務',
            'description' => '修改服務檢查設定',
        ],
        'delete' => [
            'label' => '刪除服務',
            'description' => '從裝置移除服務',
        ],
    ],
    'service-template' => [
        'title' => '服務範本',
        'view' => [
            'label' => '檢視服務範本',
            'description' => '檢視服務範本',
        ],
        'create' => [
            'label' => '建立服務範本',
            'description' => '建立新的服務範本',
        ],
        'update' => [
            'label' => '編輯服務範本',
            'description' => '修改現有的服務範本',
        ],
        'delete' => [
            'label' => '刪除服務範本',
            'description' => '刪除服務範本',
        ],
    ],
    'settings' => [
        'title' => '設定',
        'view' => [
            'label' => '檢視設定',
            'description' => '檢視 LibreNMS 全域設定',
        ],
        'update' => [
            'label' => '編輯設定',
            'description' => '修改 LibreNMS 全域設定',
        ],
    ],
    'syslog' => [
        'title' => 'Syslog',
        'delete' => [
            'label' => '刪除 syslog',
            'description' => '刪除 syslog 歷史記錄',
        ],
    ],
    'user' => [
        'title' => '使用者',
        'view' => [
            'label' => '檢視使用者',
            'description' => '檢視使用者帳號詳細資料',
        ],
        'create' => [
            'label' => '建立使用者',
            'description' => '建立新的使用者帳號',
        ],
        'update' => [
            'label' => '編輯使用者',
            'description' => '修改使用者帳號、角色與權限',
        ],
        'delete' => [
            'label' => '刪除使用者',
            'description' => '刪除使用者帳號',
        ],
        'manage' => [
            'label' => '管理權限',
            'description' => '管理使用者權限',
        ],
        'updatePassword' => [
            'label' => '更新密碼',
            'description' => '更新使用者密碼',
        ],
    ],
    'vlan' => [
        'title' => 'VLAN',
        'viewAll' => [
            'label' => '檢視所有 VLAN',
            'description' => '檢視所有 VLAN 資訊',
        ],
    ],
    'vminfo' => [
        'title' => '虛擬機器',
        'viewAll' => [
            'label' => '檢視所有虛擬機器',
            'description' => '檢視所有虛擬機器資訊',
        ],
        'view' => [
            'label' => '檢視虛擬機器',
            'description' => '檢視使用者可存取裝置的虛擬機器詳細資料',
        ],
        'update' => [
            'label' => '更新虛擬機器',
            'description' => '更新虛擬機器資料',
        ],
    ],
    'wireless-sensor' => [
        'title' => '無線感測器',
        'update' => [
            'label' => '更新無線感測器',
            'description' => '更新無線感測器資料',
        ],
        'delete' => [
            'label' => '刪除無線感測器',
            'description' => '刪除無線感測器資料',
        ],
    ],
    'customoid' => [
        'title' => '自訂 OID',
        'view' => [
            'label' => '檢視自訂 OID',
            'description' => '檢視自訂 OID 資料',
        ],
        'create' => [
            'label' => '建立自訂 OID',
            'description' => '建立新的自訂 OID',
        ],
        'update' => [
            'label' => '編輯自訂 OID',
            'description' => '修改現有的自訂 OID',
        ],
        'delete' => [
            'label' => '刪除自訂 OID',
            'description' => '刪除自訂 OID',
        ],
    ],
    'rbac' => [
        'title' => '角色與權限',
        'beta_warning_title' => '測試版功能',
        'beta_warning_message' => '這是測試版功能。權限可能尚未正確套用。如遇到任何問題，請回報。',
        'manage_users' => '管理使用者',
        'manage_roles' => '管理角色',
        'add_role' => '新增角色',
        'create_role' => '建立角色',
        'create_new_role' => '建立新角色',
        'edit_role' => '編輯角色',
        'delete_role' => '刪除角色',
        'role_name' => '角色名稱',
        'permissions' => '權限',
        'actions' => '動作',
        'all_permissions' => '所有權限',
        'view_all_permissions' => '檢視所有權限',
        'view_permissions' => '檢視權限',
        'no_permissions' => '未指派任何權限',
        'confirm_delete' => '確定要刪除此角色嗎？',
        'role_name_placeholder' => '例如：network-engineer',
        'search_permissions' => '搜尋權限...',
        'select_all' => '全選',
        'clear_all' => '全部清除',
        'save_role' => '儲存角色',
        'update_role' => '更新角色',
        'created' => '角色 :name 已成功建立',
        'updated' => '角色 :name 已成功更新',
        'deleted' => '角色 :name 已成功刪除',
        'role_name_regex' => '角色名稱只能包含小寫字母與連字號 (-)。',
    ],
    'permissions' => [
        'user_permissons' => ':name 的權限',
        'bill_access' => '帳單存取 (:count)',
        'device_access' => '裝置存取 (:count)',
        'device_group_access' => '裝置群組存取 (:count)',
        'port_access' => '連接埠存取 (:count)',
        'bill_all' => '所有帳單',
        'device_all' => '所有裝置',
        'device_group_all' => '所有裝置群組',
        'port_all' => '所有連接埠',
        'none_configured' => '未設定',
    ],
];
