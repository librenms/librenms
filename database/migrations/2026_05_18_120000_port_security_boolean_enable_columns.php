<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (DB::table('port_security')->orderBy('id')->select(['id', 'port_security_enable', 'sticky_enable'])->lazy() as $row) {
            DB::table('port_security')->where('id', $row->id)->update([
                'port_security_enable' => $this->toInteger($row->port_security_enable),
                'sticky_enable' => $this->toInteger($row->sticky_enable),
            ]);
        }

        Schema::table('port_security', function (Blueprint $table): void {
            $table->boolean('port_security_enable')->nullable()->change();
            $table->boolean('sticky_enable')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('port_security', function (Blueprint $table): void {
            $table->string('port_security_enable', 5)->nullable()->change();
            $table->string('sticky_enable', 5)->nullable()->change();
        });

        foreach (DB::table('port_security')->orderBy('id')->select(['id', 'port_security_enable', 'sticky_enable'])->lazy() as $row) {
            DB::table('port_security')->where('id', $row->id)->update([
                'port_security_enable' => $this->toString($row->port_security_enable),
                'sticky_enable' => $this->toString($row->sticky_enable),
            ]);
        }
    }

    private function toInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    private function toString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value === 1 ? 'true' : 'false';
    }
};
