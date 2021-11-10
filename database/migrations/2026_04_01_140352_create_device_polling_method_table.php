<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_polling_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id');
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade');
            $table->string('method_type');
            $table->boolean('enabled')->default(true);
            $table->boolean('affects_availability')->default(false);
            $table->foreignId('secret_id')->nullable()->constrained('secrets')->nullOnDelete();
            $table->json('settings')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('last_check_successful')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'method_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_polling_methods');
    }
};
