<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('operating_hours')->default('10:00 - 22:00');
            $table->time('open_time')->default('10:00');
            $table->time('close_time')->default('22:00');
            $table->string('image_url')->nullable();
            $table->boolean('is_pickup_available')->default(true);
            // Geography Point (WGS 84 / SRID 4326)
            $table->geography('location', subtype: 'point', srid: 4326);
            $table->timestamps();
        });

        DB::statement('CREATE INDEX stores_location_gist ON stores USING GIST (location);');
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};