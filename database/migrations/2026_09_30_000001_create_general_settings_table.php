<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('application_name', 120);
            $table->string('brand_name', 120);
            $table->string('helpline_number', 32);
            $table->text('whatsapp_contact_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->timestamps();
        });

        DB::table('general_settings')->insert([
            'id' => 1,
            'application_name' => 'India PVC',
            'brand_name' => 'Bengal PVC',
            'helpline_number' => '+91 8900162634',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};