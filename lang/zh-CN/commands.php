<?php

return [
    'user:add' => [
        'description' => '新增一个本机使用者，只有在设定验证使用 mysql 时才可以使用此使用者账号登入',
        'arguments' => [
            'username' => '使用者用来登入的名称',
        ],
        'options' => [
            'descr' => '使用者描述',
            'email' => '使用者的邮件',
            'password' => '使用者的密码，如果没有提供，您将会收到提示',
            'full-name' => '使用者的全名',
            'role' => '将使用者指派至角色 :roles',
        ],
        'password-request' => '请输入使用者的密码',
        'success' => '已成功新增使用者: :username',
        'wrong-auth' => '警告，您将无法以这个使用者登入，因为您没有使用 MySQL 验证',
    ],
];
