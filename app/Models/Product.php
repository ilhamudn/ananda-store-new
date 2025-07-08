<?php

// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'nama_barang',
        'harga_beli',
        'harga_jual',
        'total_stok', // <<< Ubah dari 'stok' ke 'total_stok'
        'tanggal_pembelian',
        'last_purchase_date', // <<< Ubah dari 'tgl_pembelian' ke 'last_purchase_date'
        'last_supplier_name', // <<< Tambahkan ini
    ];

    // Relasi ke purchase items jika ingin melihat riwayat pembelian dari sisi produk
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}