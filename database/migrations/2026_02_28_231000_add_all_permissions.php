<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        'alert.delete',
        'alert.detail',
        'alert-rule.create',
        'alert-rule.delete',
        'alert-rule.update',
        'alert-rule.view',
        'alert-rule.viewAny',
        'alert-schedule.create',
        'alert-schedule.delete',
        'alert-schedule.update',
        'alert-schedule.view',
        'alert-schedule.viewAny',
        'alert-template.create',
        'alert-template.delete',
        'alert-template.update',
        'alert-template.view',
        'alert-transport.create',
        'alert-transport.delete',
        'alert-transport.update',
        'alert-transport.view',
        'alert.update',
        'alert.view',
        'alert.viewAny',
        'api.access',
        'application.update',
        'auth-log.view',
        'bill.viewAny',
        'component.update',
        'custom-map.create',
        'custom-map.delete',
        'custom-map.update',
        'custom-map.view',
        'custom-map.viewAny',
        'customoid.create',
        'customoid.delete',
        'customoid.update',
        'customoid.view',
        'dashboard.copy',
        'device.create',
        'device.debug',
        'device.delete',
        'device-group.create',
        'device-group.delete',
        'device-group.update',
        'device-group.view',
        'device-group.viewAny',
        'device.showConfig',
        'device.update',
        'device.updateNotes',
        'device.view',
        'device.viewAny',
        'link.viewAny',
        'location.create',
        'location.delete',
        'location.update',
        'location.view',
        'location.viewAny',
        'mempool.update',
        'notification.create',
        'notification.update',
        'oxidized.refresh',
        'oxidized.search',
        'plugin.admin',
        'poller.delete',
        'poller-group.create',
        'poller-group.delete',
        'poller-group.update',
        'poller-group.viewAny',
        'poller.update',
        'poller.view',
        'poller.viewAny',
        'port.delete',
        'port-group.create',
        'port-group.delete',
        'port-group.update',
        'port-group.viewAny',
        'port.update',
        'port.view',
        'port.viewAny',
        'processor.update',
        'role.update',
        'role.viewAny',
        'routing.update',
        'routing.view',
        'routing.viewAny',
        'service.create',
        'service-template.create',
        'service-template.delete',
        'service-template.update',
        'service-template.view',
        'service-template.viewAny',
        'service.update',
        'service.view',
        'settings.update',
        'settings.viewAny',
        'syslog.delete',
        'user.create',
        'user.delete',
        'user.update',
        'user.view',
        'user.viewAny',
        'vlan.viewAny',
        'wireless-sensor.delete',
        'wireless-sensor.update',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $insertData = array_map(fn ($name) => [
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->permissions);

        DB::table('permissions')->insertOrIgnore($insertData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
    }
};
