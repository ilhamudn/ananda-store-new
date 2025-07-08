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
        Schema::table('sales', function (Blueprint $table) {
            // Tambahkan kolom customer_name setelah invoice_number atau yang relevan
            $table->string('customer_name')->after('invoice_number')->nullable();
            // Atau, jika Anda ingin agar kolom ini TIDAK boleh NULL, hapus ->nullable().
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};