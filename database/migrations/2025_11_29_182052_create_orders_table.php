<?php

use App\Models\Hold;
use App\Models\Product;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Hold::class)->unique();
            $table->decimal('total_price',10,2);
            $table->integer('quantity');
            $table->foreignIdFor(Product::class);
            $table->tinyInteger('status')->comment('payed = 1 pending = 2 cancelled = 0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
