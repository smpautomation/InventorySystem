<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_wip_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('plant', 50);
            $table->string('daily_check_file', 100);
            $table->string('process', 50);
            $table->string('area', 100);
            $table->decimal('tons', 20, 6);
            $table->unsignedInteger('work_order_count')->default(0);
            $table->timestamp('queried_at')->useCurrent();

            $table->index('plant');
            $table->unique(['plant', 'daily_check_file', 'process', 'area'], 'wip_snapshots_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_wip_snapshots');
    }
};
