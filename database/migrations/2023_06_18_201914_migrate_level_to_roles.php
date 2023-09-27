<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\BouncerFacade as Bouncer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RolesSeeder)->run(); // make sure roles have been created.

        User::all()->each(function (User $user) {
            $role = match ($user->getAttribute('level')) {
                1 => 'user',
                5 => 'global-read',
                10 => 'admin',
                default => null,
            };

            if ($role) {
                Bouncer::assign($role)->to($user);
            }
        });

        try {
            Bouncer::refresh(); // clear cache
        } catch (Exception $e) {
            // if this fails, there was no cache anyway
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

        User::whereIs('admin', 'global-read', 'user')->get()->each(function (User $user) {
            $user->setAttribute('level', $this->getLevel($user));
            $user->save();
        });

        try {
            Bouncer::refresh(); // clear cache
        } catch (Exception $e) {
            // if this fails, there was no cache anyway
        }
    }

    private function getLevel(User $user): int
    {
        if ($user->isA('admin')) {
            return 10;
        }

        if ($user->isA('global-read')) {
            return 7;
        }

        if ($user->isA('user')) {
            return 1;
        }

        return 0;
    }
};
