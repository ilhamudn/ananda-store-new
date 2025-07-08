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
        // Tabel PsbTransactions (untuk mencatat setiap transaksi PSB)
        Schema::create('psb_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Nomor invoice unik
            $table->string('buyer_name'); // Nama pembeli/pendaftar
            $table->decimal('total_amount', 12, 2); // Total harga transaksi
            $table->timestamps(); // created_at (tanggal transaksi) dan updated_at
        });

        // Tabel PsbTransactionItems (untuk mencatat item-item dalam setiap transaksi PSB)
        Schema::create('psb_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psb_transaction_id')->constrained('psb_transactions')->onDelete('cascade'); // Foreign key ke tabel psb_transactions
            $table->foreignId('product_id')->constrained('products'); // Foreign key ke tabel products (item PSB juga produk)
            $table->integer('quantity'); // Jumlah item yang terjual/diberikan
            $table->decimal('price_at_transaction', 10, 2); // Harga item saat transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psb_transaction_items');
        Schema::dropIfExists('psb_transactions');
    }
};