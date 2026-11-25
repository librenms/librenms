<?php

namespace App\Providers;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use App\Ldap\ActiveDirectoryGroups;
use App\Ldap\ActiveDirectoryUser;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Configuration\ConfigurationException;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\Laravel\Events\Import\Saved;
use LibreNMS\Exceptions\AuthenticationException;

class ActiveDirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
                config([
                    'auth.guards.web.provider' => 'active_directory',
                    'ldap.default' => 'active_directory',
                    'ldap.logging.enabled' => (bool) LibrenmsConfig::get('auth_ad_debug'),
                ]);
                Container::setDefaultConnection('active_directory');
                try {
                    if (config('ldap.connections.active_directory') === null) {
                        $connection = app(ActiveDirectoryConfig::class)->connection();
                        $ldap = new Connection($connection);
                        config(['ldap.connections.active_directory' => $connection]);
                        Container::addConnection($ldap, 'active_directory');
                    }
                } catch (AuthenticationException|ConfigurationException $exception) {
                    Container::removeConnection('active_directory');
                    Log::error('Invalid Active Directory connection configuration', ['exception' => $exception]);
                }
                if (LibrenmsConfig::get('auth_ad_debug')) {
                    Container::getInstance()->setLogger(Log::channel(config('ldap.logging.channel')));
                }
            }
        });
    }

    public function boot(): void
    {
        Event::listen(Saved::class, function (Saved $event): void {
            if ($event->object instanceof ActiveDirectoryUser && $event->eloquent instanceof User) {
                $event->eloquent->syncRoles(app(ActiveDirectoryGroups::class)->roles($event->object));
            }
        });
    }
}
