<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('events')
            ->whereNull('sale_starts_at')
            ->update(['sale_starts_at' => now()]);

        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('sale_starts_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('sale_starts_at')->nullable()->change();
        });
    }
};
