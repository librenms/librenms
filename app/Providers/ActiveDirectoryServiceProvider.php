<?php

namespace App\Providers;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryConfig;
use App\Ldap\ActiveDirectoryGroups;
use App\Ldap\ActiveDirectoryUser;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Events\Import\Saved;

class ActiveDirectoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booting(function (): void {
            if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
                config([
                    'auth.guards.web.provider' => 'active_directory',
                    'ldap.default' => 'active_directory',
                    'ldap.connections.active_directory' => app(ActiveDirectoryConfig::class)->connection(),
                    'ldap.logging.enabled' => (bool) LibrenmsConfig::get('auth_ad_debug'),
                ]);
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
