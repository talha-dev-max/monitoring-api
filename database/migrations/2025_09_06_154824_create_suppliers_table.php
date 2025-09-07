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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('base_url');
            $table->string('health_endpoint')->default('/health');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->integer('timeout_seconds')->default(30);
            $table->integer('retry_count')->default(3);
            $table->json('headers')->nullable();
            $table->boolean('is_backup')->default(false);
            $table->integer('priority')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
