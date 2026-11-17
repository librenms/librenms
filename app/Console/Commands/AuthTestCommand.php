<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use LibreNMS\Authentication\LegacyAuth;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

class AuthTestCommand extends LnmsCommand
{
    protected $name = 'auth:test';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('username', InputArgument::REQUIRED);
    }

    public function handle(): int
    {
        $mechanism = LibrenmsConfig::get('auth_mechanism');
        $this->info('Authentication Method: ' . $mechanism);

        if ($this->output->isVerbose()) {
            LibrenmsConfig::set('auth_ad_debug', true);
            LibrenmsConfig::set('auth_ldap_debug', true);
        }

        if (! $this->input->isInteractive()) {
            $this->error('An interactive terminal is required to enter the password.');

            return self::FAILURE;
        }

        $username = $this->argument('username');
        $password = $this->secret('Password');
        if (! is_string($password) || $password === '') {
            $this->error('A password is required.');

            return self::FAILURE;
        }

        try {
            $success = $this->authenticateLegacy($username, $password);
        } catch (Throwable $e) {
            report($e);
            $this->error('Error: ' . $e::class . ': ' . $e->getMessage());
            $success = false;
        }

        if ($success) {
            $this->info('AUTH SUCCESS');

            return self::SUCCESS;
        }

        $this->error('AUTH FAILURE');

        return self::FAILURE;
    }

    private function authenticateLegacy(string $username, #[\SensitiveParameter] string $password): bool
    {
        $authorizer = LegacyAuth::get();
        if (method_exists($authorizer, 'bind')) {
            $authorizer->bind([]);
        }

        if (! $authorizer->authenticate(compact('username', 'password'))) {
            return false;
        }

        $userId = $authorizer->getUserid($username);
        $this->line("User ($userId):");
        $user = $authorizer->getUser($userId);
        unset($user['password'], $user['remember_token']);
        foreach ($user as $property => $value) {
            $this->line("  $property => $value");
        }

        if (method_exists($authorizer, 'getGroupList')) {
            $this->line('Groups: ' . implode('; ', $authorizer->getGroupList()));
        }
        $roles = $authorizer->getRoles($username);
        if ($roles !== false) {
            $this->line('Roles: ' . implode('; ', $roles));
        }

        return true;
    }
}
