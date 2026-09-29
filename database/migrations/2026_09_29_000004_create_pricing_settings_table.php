<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('normal_single_price')->default(77);
            $table->unsignedInteger('normal_2_5_price')->default(55);
            $table->unsignedInteger('normal_2_5_discount')->default(31);
            $table->unsignedInteger('normal_6_7_price')->default(48);
            $table->unsignedInteger('normal_6_7_discount')->default(40);
            $table->unsignedInteger('normal_8_10_price')->default(42);
            $table->unsignedInteger('normal_8_10_discount')->default(48);
            $table->unsignedInteger('normal_11_plus_price')->default(37);
            $table->unsignedInteger('normal_11_plus_discount')->default(54);
            $table->unsignedInteger('premium_under_12_price')->default(90);
            $table->unsignedInteger('premium_12_plus_price')->default(50);
            $table->unsignedInteger('shipping_fee')->default(40);
            $table->unsignedInteger('free_shipping_minimum_quantity')->default(10);
            $table->unsignedInteger('card_cover_price')->default(15);
            $table->timestamps();
        });

        DB::table('pricing_settings')->insert([
            'id' => 1,
            'normal_single_price' => 77,
            'normal_2_5_price' => 55,
            'normal_2_5_discount' => 31,
            'normal_6_7_price' => 48,
            'normal_6_7_discount' => 40,
            'normal_8_10_price' => 42,
            'normal_8_10_discount' => 48,
            'normal_11_plus_price' => 37,
            'normal_11_plus_discount' => 54,
            'premium_under_12_price' => 90,
            'premium_12_plus_price' => 50,
            'shipping_fee' => 40,
            'free_shipping_minimum_quantity' => 10,
            'card_cover_price' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};