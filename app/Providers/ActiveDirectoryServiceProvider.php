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
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\Laravel\Events\Import\Saved;

class ActiveDirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
                $connection = app(ActiveDirectoryConfig::class)->connection();
                config([
                    'auth.guards.web.provider' => 'active_directory',
                    'ldap.default' => 'active_directory',
                    'ldap.connections.active_directory' => $connection,
                    'ldap.logging.enabled' => (bool) LibrenmsConfig::get('auth_ad_debug'),
                ]);
                Container::addConnection(new Connection($connection), 'active_directory');
                Container::setDefaultConnection('active_directory');
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
