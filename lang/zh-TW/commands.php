<?php

return [
    'user:add' => [
        'description' => '新增一個本機使用者，只有在設定驗證使用 mysql 時才可以使用此使用者帳號登入',
        'arguments' => [
            'username' => '使用者用來登入的名稱',
        ],
        'options' => [
            'descr' => '使用者描述',
            'email' => '使用者的郵件',
            'password' => '使用者的密碼，如果沒有提供，您將會收到提示',
            'full-name' => '使用者的全名',
            'role' => '將使用者指派至角色 :roles',
        ],
        'password-request' => '請輸入使用者的密碼',
        'success' => '已成功新增使用者: :username',
        'wrong-auth' => '警告，您將無法以這個使用者登入，因為您沒有使用 MySQL 驗證',
    ],
];
