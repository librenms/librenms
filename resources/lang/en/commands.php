<?php

return [
    'clean:files' => [
        'description' => 'Clean modified and untracked files, helpful to remove files after testing a PR or to just clean up',
        'options' => [
            'vendor' => 'Clean the vendor folder in addition to standard directories',
        ],
        'confirm' => 'Are you sure you want to delete all modified and untracked files?',
        'done' => 'Files have been cleaned',
    ],
    'test:pull-request' => [
        'description' => 'Apply or remove a GitHub pull request so you can test it locally',
        'arguments' => [
            'pull-request' => 'The pull request number, PRs can be found here :url',
        ],
        'options' => [
            'remove' => 'Remove the pull request via reverse patch',
        ],
        'success' => [
            'apply' => 'Pull request :number applied',
            'remove' => 'Pull request :number removed',
        ],
        'download_failed' => 'Could not download from GitHub or invalid PR number.',
        'failed' => [
            'apply' => 'An error occurred applying PR :number',
            'remove' => 'An error occurred removing PR :number.  You may need to clean your LibreNMS install with lnms clean:files',
        ],
        'already-applied' => 'Patch already applied? If you want to apply a new version, remove the old first with -r'
    ],
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
        'password-request' => "Please enter the user's password",
        'success' => 'Successfully added user: :username',
        'wrong-auth' => 'Warning! You will not be able to log in with this user because you are not using MySQL auth',
    ],
];
