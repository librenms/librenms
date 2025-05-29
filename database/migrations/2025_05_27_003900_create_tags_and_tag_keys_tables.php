<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tag_keys', function (Blueprint $table) {
            $table->increments('tag_key_id');
            $table->string('key', 128)->unique();
            $table->enum('type', ['string', 'integer', 'email', 'url', 'timestamp'])->default('string');
            $table->boolean('visible')->default(true);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('tag_id');
            $table->string('object_type', 256);
            $table->unsignedInteger('object_id');
            $table->unsignedInteger('tag_key_id');
            $table->string('value', 256)->nullable();
            $table->timestamps();

            $table->index(['object_type', 'object_id']);
            $table->index('tag_key_id');
            $table->foreign('tag_key_id')->references('tag_key_id')->on('tag_keys')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('tag_keys');
    }
};
