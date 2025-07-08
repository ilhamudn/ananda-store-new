<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_purchases_table.php

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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Nomor invoice pembelian
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null'); // FK ke suppliers, nullable
            $table->decimal('total_amount', 15, 2); // Total nilai pembelian
            $table->timestamps(); // created_at akan menjadi tanggal pembelian
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};