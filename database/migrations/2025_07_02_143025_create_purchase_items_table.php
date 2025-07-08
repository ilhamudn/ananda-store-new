<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_purchase_items_table.php

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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade'); // FK ke purchases
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // FK ke products
            $table->integer('quantity'); // Jumlah barang yang dibeli dalam transaksi ini
            $table->decimal('price_at_purchase', 15, 2); // Harga beli per unit saat transaksi ini terjadi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
