<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create default roles, skip abilities as we ignore those later anyway
        DB::table('roles')->insert([
            ['name' => 'admin', 'title' => 'Admin'],
            ['name' => 'global-read', 'title' => 'Global Read'],
            ['name' => 'user', 'title' => 'User'],
        ]);

        $roles = array_column((array)(DB::table('roles')->select(['id', 'name'])->get()), 'id', 'name');

        foreach(DB::table('users')->select(['user_id', 'level'])->get() as $user) {
            $role = match ($user->level) {
                1 => 'user',
                5 => 'global-read',
                10 => 'admin',
                default => null,
            };

            if (isset($roles[$role])) {
                DB::table('assigned_roles')->insert([
                    'role_id' => $roles[$role],
                    'entity_id' => $user->user_id,
                    'entity_type' => 'App\Models\User',
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->tinyInteger('level')->default(0)->after('descr');
            });
        }

        $rolesByUserId = DB::table('assigned_roles')
            ->join('roles', 'assigned_roles.role_id', '=', 'roles.id')
            ->where('assigned_roles.entity_type', 'App\\Models\\User') // Adjust namespace if needed
            ->select(
                'assigned_roles.entity_id as user_id',
                DB::raw('GROUP_CONCAT(roles.name ORDER BY roles.name ASC SEPARATOR ", ") as roles')
            )
            ->groupBy('assigned_roles.entity_id')
            ->get()->pluck('roles', 'user_id')->map(function ($roles) {
                return explode(',', $roles);
            });

        foreach (DB::table('users')->select('user_id')->get() as $user) {
            DB::table('users')->where('user_id', $user->user_id)->update([
                'level' => $this->getLevel($rolesByUserId, $user->user_id),
            ]);
        }
    }

    private function getLevel(array $rolesByUserId, int $user_id): int
    {
        if (! isset($rolesByUserId[$user_id])) {
            return 0;
        }

        $userRoles = $rolesByUserId[$user_id];

        if (in_array('admin', $userRoles)) {
            return 10;
        }

        if (in_array('global-read', $userRoles)) {
            return 7;
        }

        if (in_array('user', $userRoles)) {
            return 1;
        }

        return 0;
    }
};
