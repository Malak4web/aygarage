<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_bikes', function (Blueprint $table) {
            if (!Schema::hasColumn('user_bikes', 'last_visit_at')) {
                $table->date('last_visit_at')->nullable()->after('license_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_bikes', function (Blueprint $table) {
            if (Schema::hasColumn('user_bikes', 'last_visit_at')) {
                $table->dropColumn('last_visit_at');
            }
        });
    }
};
