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
        Schema::create('api_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->integer('response_time_ms');
            $table->integer('status_code');
            $table->enum('status', ['healthy', 'unhealthy', 'timeout', 'error']);
            $table->text('error_message')->nullable();
            $table->json('response_headers')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            
            $table->index(['supplier_id', 'checked_at']);
            $table->index(['status', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_health_checks');
    }
};
