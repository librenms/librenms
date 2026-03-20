<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'ssl-certificate.create',
        'ssl-certificate.delete',
        'ssl-certificate.update',
        'ssl-certificate.view',
        'ssl-certificate.viewAny',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ssl_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->nullable()->index();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(443);
            $table->string('issuer')->nullable();
            $table->string('issuer_country', 8)->nullable();
            $table->string('issuer_organization')->nullable();
            $table->string('subject')->nullable();
            $table->longText('subject_alternative_names')->nullable();
            $table->string('serial_number', 64)->nullable();
            $table->string('serial_number_hex', 64)->nullable();
            $table->boolean('self_signed')->default(false);
            $table->string('signature_algorithm', 64)->nullable();
            $table->unsignedTinyInteger('certificate_version')->nullable();
            $table->string('key_usage')->nullable();
            $table->string('extended_key_usage')->nullable();
            $table->string('basic_constraints', 64)->nullable();
            $table->string('subject_key_identifier', 128)->nullable();
            $table->string('authority_key_identifier', 128)->nullable();
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            $table->integer('days_until_expiry')->nullable()->comment('Days until expiry; positive = remaining, negative = expired');
            $table->string('fingerprint', 64)->nullable();
            $table->text('pem')->nullable();
            $table->dateTime('last_checked_at')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('set null');
        });

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
        Schema::dropIfExists('ssl_certificates');
        DB::table('permissions')->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
    }
};
