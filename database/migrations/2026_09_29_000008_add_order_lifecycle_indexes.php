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
            if (! in_array('orders_created_at_id_index', $indexes, true)) {
                $table->index(['created_at', 'id'], 'orders_created_at_id_index');
            }

            if (! in_array('orders_status_created_index', $indexes, true)) {
                $table->index(['status', 'created_at'], 'orders_status_created_index');
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('orders'))->pluck('name')->all();

        Schema::table('orders', function (Blueprint $table) use ($indexes): void {
            if (in_array('orders_created_at_id_index', $indexes, true)) {
                $table->dropIndex('orders_created_at_id_index');
            }

            if (in_array('orders_status_created_index', $indexes, true)) {
                $table->dropIndex('orders_status_created_index');
            }
        });
    }
};