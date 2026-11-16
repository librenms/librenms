<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LdapRecord\Container;
use LdapRecord\Laravel\Events\Auth\BindFailed;
use LdapRecord\Laravel\Events\Auth\Binding;
use LdapRecord\Laravel\Events\Auth\DiscoveredWithCredentials;
use LdapRecord\Laravel\Events\Auth\Rejected;
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

    public function handle(ActiveDirectoryConfig $config): int
    {
        $mechanism = LibrenmsConfig::get('auth_mechanism');
        $this->info('Authentication Method: ' . $mechanism);

        if ($this->output->isVerbose()) {
            LibrenmsConfig::set('auth_ad_debug', true);
            LibrenmsConfig::set('auth_ldap_debug', true);
            Container::getInstance()->setLogger(Log::channel(config('ldap.logging.channel')));
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
            $success = $mechanism === 'active_directory'
                ? $this->authenticateActiveDirectory($config->credentials(compact('username', 'password')))
                : $this->authenticateLegacy($username, $password);
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

    /**
     * @param  array{username?: string, password?: string, samaccountname?: string, userprincipalname?: string, fallback?: array{username: string, password: string, auth_type: string, enabled: int}}  $credentials
     */
    private function authenticateActiveDirectory(#[\SensitiveParameter] array $credentials): bool
    {
        $ldapUserFound = false;
        Event::listen(DiscoveredWithCredentials::class, function () use (&$ldapUserFound): void {
            $ldapUserFound = true;
            $this->line('LDAP user found.');
        });
        Event::listen(Binding::class, function (Binding $event): void {
            $this->line($event->eloquent?->exists
                ? 'SQL user found: user_id=' . $event->eloquent->getKey() . ', enabled=' . (int) $event->eloquent->getAttribute('enabled')
                : 'No existing SQL user matched; a new account will be imported after successful authentication.');
        });
        Event::listen(BindFailed::class, function (): void {
            $this->error('LDAP password bind failed. Check the password and directory account status.');
        });
        Event::listen(Rejected::class, function (): void {
            $this->error("LDAP password bind succeeded, but an access rule rejected the login. Check the SQL account's enabled flag and required AD group memberships.");
        });

        $success = Auth::once($credentials);
        if (! $ldapUserFound) {
            $this->warn('LDAP user was not found. Check the bind account, base DN, username, and user filter; review the application log for LDAP lookup errors.');
            if ($success) {
                $this->line('Authenticated using the local fallback account.');
            }
        }

        return $success;
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
