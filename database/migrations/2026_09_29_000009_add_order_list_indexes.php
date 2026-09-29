<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('orders'))->pluck('name')->all();

        Schema::table('orders', function (Blueprint $table) use ($indexes): void {
            if (! in_array('orders_service_created_id_index', $indexes, true)) {
                $table->index(['service_type', 'created_at', 'id'], 'orders_service_created_id_index');
            }

        });
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('orders'))->pluck('name')->all();

        Schema::table('orders', function (Blueprint $table) use ($indexes): void {
            if (in_array('orders_service_created_id_index', $indexes, true)) {
                $table->dropIndex('orders_service_created_id_index');
            }

        });
    }
};