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
        Schema::table('funds', function (Blueprint $table) {
            $table->index('name'); 
            $table->index('fund_manager_id'); 
            $table->index('start_year');
        });

        Schema::table('fund_aliases', function (Blueprint $table) {
            $table->index('name'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['fund_manager_id']);
            $table->dropIndex(['start_year']);
        });

        Schema::table('fund_aliases', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};