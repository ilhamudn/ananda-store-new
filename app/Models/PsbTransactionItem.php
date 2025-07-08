<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsbTransactionItem extends Model
{
    use HasFactory;

    // Nama tabel yang terkait dengan model ini di database
    protected $table = 'psb_transaction_items'; // Sesuaikan jika nama tabel Anda berbeda

    // Kolom-kolom yang dapat diisi secara massal (mass assignable)
    // Ini penting untuk keamanan agar hanya kolom yang diizinkan yang bisa diisi
    protected $fillable = [
        'psb_transaction_id', // Foreign key yang menghubungkan ke tabel psb_transactions
        'product_id',         // Foreign key yang menghubungkan ke tabel products
        'quantity',           // Jumlah produk dalam item transaksi
        'price_at_transaction', // Harga produk saat transaksi terjadi (penting untuk histori)
    ];

    // Kolom-kolom yang harus di-cast ke tipe data tertentu
    // Ini membantu memastikan tipe data yang benar saat berinteraksi dengan database
    protected $casts = [
        'quantity' => 'integer',         // Kuantitas harus berupa bilangan bulat
        'price_at_transaction' => 'float', // Harga harus berupa angka desimal/float
    ];

    /**
     * Mendefinisikan hubungan Many-to-One dengan PsbTransaction.
     * Sebuah item transaksi PSB termasuk dalam satu transaksi PSB.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function psbTransaction()
    {
        // Menggunakan belongsTo karena PsbTransactionItem "milik" satu PsbTransaction
        // 'psb_transaction_id' adalah foreign key di tabel 'psb_transaction_items'
        // yang menunjuk ke primary key di tabel 'psb_transactions'
        return $this->belongsTo(PsbTransaction::class, 'psb_transaction_id');
    }

    /**
     * Mendefinisikan hubungan Many-to-One dengan Product.
     * Sebuah item transaksi PSB terkait dengan satu produk.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        // Menggunakan belongsTo karena PsbTransactionItem "milik" satu Product
        // 'product_id' adalah foreign key di tabel 'psb_transaction_items'
        // yang menunjuk ke primary key di tabel 'products'
        return $this->belongsTo(Product::class, 'product_id');
    }
}
