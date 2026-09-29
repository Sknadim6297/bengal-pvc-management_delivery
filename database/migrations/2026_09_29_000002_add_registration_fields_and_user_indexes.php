<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'whatsapp_number')) {
                $table->string('whatsapp_number', 15)->nullable();
            }

            if (! Schema::hasColumn('users', 'district')) {
                $table->string('district', 100)->nullable();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('whatsapp_number', 'users_whatsapp_number_unique');
            $table->dropIndex('users_role_status_index');
            $table->index(['role', 'status', 'id'], 'users_role_status_id_index');
            $table->index(['role', 'id'], 'users_role_id_index');
            $table->index(['status', 'id'], 'users_status_id_index');
            $table->index(['name', 'id'], 'users_name_id_index');
            $table->index(['created_at', 'id'], 'users_created_at_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_whatsapp_number_unique');
            $table->dropIndex('users_role_status_id_index');
            $table->dropIndex('users_role_id_index');
            $table->dropIndex('users_status_id_index');
            $table->dropIndex('users_name_id_index');
            $table->dropIndex('users_created_at_id_index');
            $table->index(['role', 'status'], 'users_role_status_index');
            $table->dropColumn(['whatsapp_number', 'district']);
        });
    }
};
