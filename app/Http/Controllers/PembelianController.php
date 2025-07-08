<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase; // Import model Purchase yang baru
use App\Models\PurchaseItem; // Import model PurchaseItem yang baru
use App\Models\Supplier; // Import model Supplier yang baru
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Carbon\Carbon; // Untuk memanipulasi tanggal

class PembelianController extends Controller
{
    /**
     * Menampilkan daftar semua barang (untuk memilih barang yang akan di-edit/restock)
     * Ini akan menjadi 'Daftar Barang' di image_c1c9f8.png
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('nama_barang', 'like', '%' . $searchTerm . '%')
                ->orWhere('barcode', 'like', '%' . $searchTerm . '%');
        }

        // Pastikan nama kolom sudah disesuaikan di model Product
         $products = $query->orderBy('nama_barang')->paginate(10); // Menambahkan paginasi

        // View yang menampilkan daftar barang adalah 'pembelian.index' (sesuai penamaanmu)
        return view('pembelian.index', compact('products'));
    }

    /**
     * Menampilkan form untuk pembelian barang baru.
     * Ini adalah 'Pembelian Barang Baru' di image_c1ca4c.png
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get(); // Ambil semua supplier untuk dropdown
        return view('pembelian.create', compact('suppliers'));
    }

    /**
     * Menyimpan data barang baru ke database (atau restock jika barcode sudah ada).
     * Logika untuk 'Pembelian Barang Baru'.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'tanggal_pembelian' => 'required|date', // Ini adalah input dari form
            'supplier_name' => 'nullable|string|max:255', // Ini adalah input dari form
        ]);

        DB::beginTransaction();
        try {
            $product = new Product();
            $product->barcode = (string) Str::uuid(); // Barcode di-generate otomatis
            $product->nama_barang = $request->nama_barang;
            $product->harga_beli = $request->harga_beli;
            $product->harga_jual = $request->harga_jual ?? 0; // Default 0 jika null/kosong
            $product->total_stok = $request->stok;
            // PENTING: Gunakan nama kolom yang ada di database Anda.
            // Jika kolom di DB Anda adalah 'tanggal_pembelian', gunakan itu.
            $product->tanggal_pembelian = $request->tanggal_pembelian; // Menggunakan kolom 'tanggal_pembelian' dari DB
            // Mengirim null jika supplier_name kosong di form
            $product->last_supplier_name = $request->supplier_name === '' ? null : $request->supplier_name;
            $product->save();

            // Generate invoice number unik untuk pembelian
            $invoiceNumber = 'INV-PCH-' . Str::uuid(); // Contoh format: INV-PCH-UUID

            // Buat entri pembelian
            $purchase = Purchase::create([
                'invoice_number' => $invoiceNumber, // Tambahkan baris ini
                'purchase_date' => $request->tanggal_pembelian,
                // Mengirim null jika supplier_name kosong di form
                'supplier_name' => $request->supplier_name === '' ? null : $request->supplier_name,
                'total_amount' => $request->harga_beli * $request->stok,
            ]);

            // Buat item pembelian
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $request->stok,
                'price_at_purchase' => $request->harga_beli,
            ]);

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Barang baru berhasil ditambahkan!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Validasi gagal: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan barang baru: ' . $e->getMessage());
        }
    }
    

    /**
     * Menampilkan form untuk mengedit/restock barang yang sudah ada.
     * Ini adalah 'Tambahkan Item / Update Barang' di image_c0fae6.png
     * Menggunakan Route Model Binding untuk mendapatkan instance Product.
     */
    public function edit(Product $product)
    {
        $suppliers = Supplier::orderBy('name')->get(); // Ambil semua supplier untuk dropdown
        return view('pembelian.edit', compact('product', 'suppliers')); // Asumsi view-nya adalah 'pembelian.edit'
    }

    /**
     * Mengupdate data barang yang sudah ada (untuk restock).
     * Logika untuk 'Tambahkan Item / Update Barang'.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'tanggal_pembelian' => 'required|date',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        $addedStock = $request->stok;

        DB::beginTransaction();
        try {
            $product->nama_barang = $request->nama_barang;
            $product->harga_beli = $request->harga_beli;
            $product->harga_jual = $request->harga_jual ?? 0;
            $product->total_stok += $addedStock;
            // PENTING: Gunakan nama kolom yang ada di database Anda.
            $product->tanggal_pembelian = $request->tanggal_pembelian; // Menggunakan kolom 'tanggal_pembelian' dari DB
            // Mengirim null jika supplier_name kosong di form
            $product->last_supplier_name = $request->supplier_name === '' ? null : $request->supplier_name;
            $product->save();

            // Generate invoice number unik untuk pembelian/restock
            $invoiceNumber = 'INV-RST-' . Str::uuid(); // Contoh format untuk restock

            // Buat entri pembelian/restock baru
            $purchase = Purchase::create([
                'invoice_number' => $invoiceNumber, // Tambahkan baris ini
                'purchase_date' => $request->tanggal_pembelian,
                // Mengirim null jika supplier_name kosong di form
                'supplier_name' => $request->supplier_name === '' ? null : $request->supplier_name,
                'total_amount' => $request->harga_beli * $addedStock,
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $addedStock,
                'price_at_purchase' => $request->harga_beli,
            ]);

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Barang berhasil diupdate dan stok ditambahkan!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Validasi gagal: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupdate barang: ' . $e->getMessage());
        }
    }
}