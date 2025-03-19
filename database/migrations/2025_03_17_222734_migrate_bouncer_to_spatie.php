<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // migrate roles
        $bouncerRoles = DB::table('bouncer_roles')->get();

        foreach ($bouncerRoles as $bouncerRole) {
            DB::table('roles')->insertOrIgnore([
                'name' => $bouncerRole->name,
                'guard_name' => 'web',
                'created_at' => $bouncerRole->created_at,
                'updated_at' => $bouncerRole->updated_at,
            ]);
        }

        // Migrate user-role assignments
        $rolesByUserId = DB::table('assigned_roles')
            ->join('roles', 'assigned_roles.role_id', '=', 'roles.id')
            ->where('assigned_roles.entity_type', 'App\\Models\\User') // Adjust namespace if needed
            ->select(
                'assigned_roles.entity_id as user_id',
                'roles.name as role_name'
            )
            ->orderBy('roles.name')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userRoles) {
                return $userRoles->pluck('role_name')->toArray();
            });

        $newRoleIds = DB::table('roles')->pluck('id', 'name');

        foreach ($rolesByUserId as $user_id => $roles) {
            foreach ($roles as $role) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $newRoleIds[$role],
                    'model_type' => 'App\Models\User',
                    'model_id' => $user_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Truncate Spatie tables, don't do this in production
//        DB::statement('SET FOREIGN_KEY_CHECKS=0');
//        DB::table('role_has_permissions')->truncate();
//        DB::table('model_has_roles')->truncate();
//        DB::table('model_has_permissions')->truncate();
//        DB::table('roles')->truncate();
//        DB::table('permissions')->truncate();
//        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
