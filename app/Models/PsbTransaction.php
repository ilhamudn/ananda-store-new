<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsbTransaction extends Model
{
    use HasFactory;

    // Nama tabel yang terkait dengan model ini
    protected $table = 'psb_transactions'; // Sesuaikan jika nama tabel Anda berbeda

    // Kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'invoice_number',
        'buyer_name',
        'total_amount',
        // Tambahkan kolom lain yang relevan jika ada (misalnya user_id, payment_status, dll.)
    ];

    // Kolom yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'total_amount' => 'float', // Pastikan total_amount adalah float atau decimal
    ];

    /**
     * Mendefinisikan hubungan One-to-Many dengan PsbTransactionItem.
     * Sebuah transaksi PSB dapat memiliki banyak item transaksi PSB.
     */
    public function psbTransactionItems()
    {
        return $this->hasMany(PsbTransactionItem::class, 'psb_transaction_id');
        // 'psb_transaction_id' adalah foreign key di tabel psb_transaction_items
        // Sesuaikan 'psb_transaction_id' jika nama foreign key Anda berbeda
    }

    // Jika Anda memiliki hubungan lain (misalnya dengan User), tambahkan di sini
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}

