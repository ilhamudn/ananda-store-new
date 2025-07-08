<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PsbTransaction;
use App\Models\PsbTransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- Pastikan ini ada

class DummyStockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // --- NONAKTIFKAN FOREIGN KEY CHECKS SEMENTARA ---
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // PERHATIAN: Ini akan menghapus semua data di tabel-tabel ini!
        // Urutan TRUNCATE penting: tabel anak dulu, baru tabel induk.
        // PsbTransactionItem::truncate();
        // PsbTransaction::truncate();
        // SaleItem::truncate();
        // Sale::truncate();
        // PurchaseItem::truncate();
        // Purchase::truncate();
        // Product::truncate();

        // --- AKTIFKAN KEMBALI FOREIGN KEY CHECKS ---
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. Buat Dummy Products ---
        // Produk 1: KAOS KAKI
        $product1 = Product::create([
            'barcode' => 'P001',
            'nama_barang' => 'KAOS KAKI',
            'harga_beli' => 15000,
            'harga_jual' => 25000,
            'total_stok' => 50, // Stok awal
            'tanggal_pembelian' => Carbon::now()->subDays(30),
        ]);

        // Produk 2: KAOS KAKI SEBELAH DOANG
        $product2 = Product::create([
            'barcode' => 'P002',
            'nama_barang' => 'KAOS KAKI SEBELAH DOANG',
            'harga_beli' => 10000,
            'harga_jual' => 20000,
            'total_stok' => 20, // Stok awal
            'tanggal_pembelian' => Carbon::now()->subDays(25),
        ]);

        // Produk 3: KAOS POLO L
        $product3 = Product::create([
            'barcode' => 'P003',
            'nama_barang' => 'KAOS POLO L',
            'harga_beli' => 70000,
            'harga_jual' => 100000,
            'total_stok' => 30, // Stok awal
            'tanggal_pembelian' => Carbon::now()->subDays(20),
        ]);

        // Produk 4: KEMEJA PUTIH S
        $product4 = Product::create([
            'barcode' => 'P004',
            'nama_barang' => 'KEMEJA PUTIH S',
            'harga_beli' => 80000,
            'harga_jual' => 120000,
            'total_stok' => 0, // Stok awal langsung 0, akan diisi via pembelian
            'tanggal_pembelian' => Carbon::now()->subDays(15),
        ]);

        // Produk 5: CELANA JEANS
        $product5 = Product::create([
            'barcode' => 'P005',
            'nama_barang' => 'CELANA JEANS',
            'harga_beli' => 150000,
            'harga_jual' => 250000,
            'total_stok' => 100, // Stok awal
            'tanggal_pembelian' => Carbon::now()->subDays(10),
        ]);


        // --- 2. Buat Dummy Purchases (yang tercatat di purchase_items) ---

        // Pembelian untuk Product 2 (KAOS KAKI SEBELAH DOANG)
        $purchase1 = Purchase::create([
            'invoice_number' => 'PO-' . Carbon::now()->format('YmdHis'),
            'supplier_name' => 'Supplier A',
            'total_amount' => $product2->harga_beli * 30, // total_amount harus dihitung
            'purchase_date' => Carbon::now()->subDays(10),
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase1->id,
            'product_id' => $product2->id,
            'quantity' => 30, // Tambah 30 stok ke KAOS KAKI SEBELAH DOANG
            'price_at_purchase' => $product2->harga_beli,
        ]);
        $product2->increment('total_stok', 30); // Update stok produk

        // Pembelian untuk Product 4 (KEMEJA PUTIH S)
        $purchase2 = Purchase::create([
            'invoice_number' => 'PO-' . Carbon::now()->addDays(1)->format('YmdHis'),
            'supplier_name' => 'Supplier B',
            'total_amount' => $product4->harga_beli * 15, // total_amount harus dihitung
            'purchase_date' => Carbon::now()->subDays(5),
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase2->id,
            'product_id' => $product4->id,
            'quantity' => 15, // Tambah 15 stok ke KEMEJA PUTIH S
            'price_at_purchase' => $product4->harga_beli,
        ]);
        $product4->increment('total_stok', 15); // Update stok produk


        // --- 3. Buat Dummy Sales (Penjualan Biasa) ---

        // Penjualan untuk Product 1 (KAOS KAKI)
        $sale1 = Sale::create([
            'invoice_number' => 'INV-' . Carbon::now()->subDays(20)->format('YmdHis'),
            'customer_name' => 'Pelanggan X',
            'total_amount' => $product1->harga_jual * 10, // total_amount harus dihitung
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'price_at_sale' => $product1->harga_jual,
        ]);
        $product1->decrement('total_stok', 10); // Update stok produk

        // Penjualan untuk Product 2 (KAOS KAKI SEBELAH DOANG)
        $sale2 = Sale::create([
            'invoice_number' => 'INV-' . Carbon::now()->subDays(18)->format('YmdHis'),
            'customer_name' => 'Pelanggan Y',
            'total_amount' => $product2->harga_jual * 5, // total_amount harus dihitung
        ]);
        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'price_at_sale' => $product2->harga_jual,
        ]);
        $product2->decrement('total_stok', 5); // Update stok produk

        // Penjualan untuk Product 4 (KEMEJA PUTIH S) - Habis
        $sale3 = Sale::create([
            'invoice_number' => 'INV-' . Carbon::now()->subDays(3)->format('YmdHis'),
            'customer_name' => 'Pelanggan Z',
            'total_amount' => $product4->harga_jual * 15, // total_amount harus dihitung
        ]);
        SaleItem::create([
            'sale_id' => $sale3->id,
            'product_id' => $product4->id,
            'quantity' => 15,
            'price_at_sale' => $product4->harga_jual,
        ]);
        $product4->decrement('total_stok', 15); // Update stok produk


        // --- 4. Buat Dummy PSB Transactions (Penjualan PSB) ---

        // Penjualan PSB untuk Product 3 (KAOS POLO L)
        $psb1 = PsbTransaction::create([
            'invoice_number' => 'PSB-' . Carbon::now()->subDays(12)->format('YmdHis'),
            'buyer_name' => 'Sekolah A',
            'total_amount' => $product3->harga_jual * 8, // total_amount harus dihitung
        ]);
        PsbTransactionItem::create([
            'psb_transaction_id' => $psb1->id,
            'product_id' => $product3->id,
            'quantity' => 8,
            'price_at_transaction' => $product3->harga_jual,
        ]);
        $product3->decrement('total_stok', 8); // Update stok produk

        // Penjualan PSB untuk Product 5 (CELANA JEANS)
        $psb2 = PsbTransaction::create([
            'invoice_number' => 'PSB-' . Carbon::now()->subDays(7)->format('YmdHis'),
            'buyer_name' => 'Kampus B',
            'total_amount' => $product5->harga_jual * 2, // total_amount harus dihitung
        ]);
        PsbTransactionItem::create([
            'psb_transaction_id' => $psb2->id,
            'product_id' => $product5->id,
            'quantity' => 2,
            'price_at_transaction' => $product5->harga_jual,
        ]);
        $product5->decrement('total_stok', 2); // Update stok produk

        $this->command->info('Dummy stock data seeded successfully!');
    }
}