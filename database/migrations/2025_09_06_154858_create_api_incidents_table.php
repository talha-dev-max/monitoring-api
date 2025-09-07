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
        Schema::create('api_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['downtime', 'high_response_time', 'error_rate', 'timeout']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['supplier_id', 'started_at']);
            $table->index(['type', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_incidents');
    }
};
