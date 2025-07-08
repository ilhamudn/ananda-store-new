<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    // Nama tabel yang terkait dengan model ini
    protected $table = 'sales'; // Sesuaikan jika nama tabel Anda berbeda

    // Kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'invoice_number',
        'customer_name', // Jika ada kolom nama pelanggan di tabel sales
        'total_amount',
        // Tambahkan kolom lain yang relevan jika ada
    ];

    // Kolom yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'total_amount' => 'float', // Pastikan total_amount adalah float atau decimal
    ];

    /**
     * Mendefinisikan hubungan One-to-Many dengan SaleItem.
     * Sebuah transaksi penjualan (Sale) dapat memiliki banyak item penjualan (SaleItem).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
        // 'sale_id' adalah foreign key di tabel sale_items
        // Sesuaikan 'sale_id' jika nama foreign key Anda berbeda
    }

    // Jika Anda memiliki hubungan lain (misalnya dengan User), tambahkan di sini
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}

