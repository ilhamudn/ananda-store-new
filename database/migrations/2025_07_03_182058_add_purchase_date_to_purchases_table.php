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
        Schema::table('purchases', function (Blueprint $table) {
            // Tambahkan kolom purchase_date setelah kolom supplier_name atau yang relevan
            // Jika supplier_name tidak ada, bisa diletakkan di akhir atau setelah total_amount
            $table->date('purchase_date')->after('total_amount')->nullable();
            // Atau, jika Anda ingin agar kolom ini TIDAK boleh NULL, hapus ->nullable() dan pastikan seeder selalu mengisinya.
            // Jika ingin ada default value: ->default(Carbon::now()) atau ->default(DB::raw('CURRENT_DATE'))
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('purchase_date');
        });
    }
};