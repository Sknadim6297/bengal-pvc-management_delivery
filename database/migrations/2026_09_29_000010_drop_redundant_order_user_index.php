<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('orders'))->pluck('name')->all();

        if (in_array('orders_user_created_id_index', $indexes, true)) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropIndex('orders_user_created_id_index');
            });
        }
    }

    public function down(): void
    {
    }
};