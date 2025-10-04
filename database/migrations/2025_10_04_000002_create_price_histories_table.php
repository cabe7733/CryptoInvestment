<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cryptocurrency_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->decimal('price_usd', 20, 8);
            $table->decimal('percent_change_1h', 10, 2)->nullable();
            $table->decimal('percent_change_24h', 10, 2)->nullable();
            $table->decimal('percent_change_7d', 10, 2)->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('volume_24h', 20, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['cryptocurrency_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
