<?php

return [
    'user:add' => [
        'description' => 'Add a local user, you can only log in with this user if auth is set to mysql',
        'arguments' => [
            'username' => 'The username the user will log in with',
        ],
        'options' => [
            'descr' => 'User description',
            'email' => 'Email to use for the user',
            'password' => 'Password for the user, if not given, you will be prompted',
            'full-name' => 'Full name for the user',
            'role' => 'Set the user to the desired role :roles',
        ],
        'invalid-role' => 'Invalid user role',
        'password-request' => "Please enter the user's password",
        'success' => 'Successfully added user: :username',
        'user-exists' => 'The user :username already exists',
    ],
];
