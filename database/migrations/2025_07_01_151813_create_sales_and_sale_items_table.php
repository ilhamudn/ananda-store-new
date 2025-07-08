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
        // Tabel Sales (untuk mencatat transaksi penjualan)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Nomor invoice unik
            $table->decimal('total_amount', 12, 2); // Total harga transaksi
            $table->timestamps(); // created_at (tanggal penjualan) dan updated_at
        });

        // Tabel Sale Items (untuk mencatat item-item dalam setiap transaksi)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade'); // Foreign key ke tabel sales
            $table->foreignId('product_id')->constrained('products'); // Foreign key ke tabel products
            $table->integer('quantity'); // Jumlah item yang terjual
            $table->decimal('price_at_sale', 10, 2); // Harga jual item saat transaksi (penting jika harga berubah)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};