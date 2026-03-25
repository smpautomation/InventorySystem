<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_target_tons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('plant');
            $table->string('month');
            $table->string('year');
            $table->float('target');
            $table->float('working_days');
        });

        Schema::create('daily_target_tons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('plant');
            $table->string('area');
            $table->string('month');
            $table->string('year');
            $table->tinyInteger('day');
            $table->float('target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_target_tons');
        Schema::dropIfExists('daily_target_tons');
    }
};
