<?php

// database/migrations/YYYY_MM_DD_HHMMSS_update_products_table_for_purchase_history.php

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
        Schema::table('products', function (Blueprint $table) {
            // Ubah nama kolom 'stok' menjadi 'total_stok'
            if (Schema::hasColumn('products', 'stok')) {
                $table->renameColumn('stok', 'total_stok');
            }

            // Ubah nama kolom 'tgl_pembelian' menjadi 'last_purchase_date'
            if (Schema::hasColumn('products', 'tgl_pembelian')) {
                $table->renameColumn('tgl_pembelian', 'last_purchase_date');
            }

            // Tambahkan kolom 'last_supplier_name'
            $table->string('last_supplier_name')->nullable()->after('total_stok');

            // Opsional: Untuk harga beli di `products` ini akan menjadi harga beli terakhir
            // atau bisa juga average price, tapi untuk kesederhanaan kita pakai last.
            // Kolom harga_beli sudah ada di products, jadi tidak perlu ditambah lagi.
            // $table->decimal('harga_beli', 15, 2)->change(); // Jika perlu perubahan tipe data atau properti
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Balikkan nama kolom 'total_stok' menjadi 'stok'
            if (Schema::hasColumn('products', 'total_stok')) {
                $table->renameColumn('total_stok', 'stok');
            }
            // Balikkan nama kolom 'last_purchase_date' menjadi 'tgl_pembelian'
            if (Schema::hasColumn('products', 'last_purchase_date')) {
                $table->renameColumn('last_purchase_date', 'tgl_pembelian');
            }
            // Hapus kolom 'last_supplier_name'
            $table->dropColumn('last_supplier_name');
        });
    }
};
