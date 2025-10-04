<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('coin_id')->unique()->comment('CoinMarketCap ID');
            $table->string('symbol', 10);
            $table->string('name', 100);
            $table->boolean('is_tracked')->default(true);
            $table->timestamps();

            $table->index('is_tracked');
            $table->index('symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};
