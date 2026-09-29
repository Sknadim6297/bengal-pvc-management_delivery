<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('print_qualities', 'display_order')) {
            Schema::table('print_qualities', function (Blueprint $table) {
                $table->unsignedSmallInteger('display_order')->default(0);
            });
        }

        $photoServiceId = DB::table('print_services')->where('slug', 'photo-print')->value('id');
        if ($photoServiceId) {
            DB::table('print_qualities')->where('service_id', $photoServiceId)->where('slug', 'photo-4x6')->update(['display_order' => 1]);
            DB::table('print_qualities')->where('service_id', $photoServiceId)->where('slug', 'photo-a4')->update(['display_order' => 2]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('print_qualities', 'display_order')) {
            Schema::table('print_qualities', function (Blueprint $table) {
                $table->dropColumn('display_order');
            });
        }
    }
};