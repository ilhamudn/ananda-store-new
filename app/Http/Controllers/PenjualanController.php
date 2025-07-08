<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon; // Tambahkan ini untuk manipulasi tanggal

class PenjualanController extends Controller
{
    /**
     * Menampilkan halaman penjualan, menangani pencarian produk,
     * dan menampilkan keranjang dari session.
     */
    public function index(Request $request)
    {
        $products = collect(); // Koleksi kosong untuk produk hasil pencarian
        $searchTerm = $request->input('search_query');

        if (!empty($searchTerm) && strlen($searchTerm) >= 3) {
            $products = Product::where('nama_barang', 'like', '%' . $searchTerm . '%')
                               ->orWhere('barcode', 'like', '%' . $searchTerm . '%')
                               ->where('total_stok', '>', 0) // <-- UBAH DARI 'stok' MENJADI 'total_stok'
                               ->limit(10)
                               ->get();
        }

        // Ambil item keranjang dari session
        $cartItems = session()->get('cart', []);

        // Hitung ulang total keranjang dari session
        $cartTotal = 0;
        foreach ($cartItems as $item) {
            $cartTotal += $item['quantity'] * $item['price_at_sale'];
        }

        return view('penjualan.index', compact('products', 'searchTerm', 'cartItems', 'cartTotal'));
    }

    /**
     * Menambahkan produk ke keranjang (menggunakan session).
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        if ($product->total_stok < $quantity) { // <-- UBAH DARI 'stok' MENJADI 'total_stok'
            return redirect()->back()->with('error', 'Stok ' . $product->nama_barang . ' tidak cukup. Tersedia: ' . $product->total_stok . ', Diminta: ' . $quantity);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            // Jika produk sudah ada di keranjang, update kuantitas
            $newQuantity = $cart[$productId]['quantity'] + $quantity;
            if ($product->total_stok < $newQuantity) { // <-- UBAH DARI 'stok' MENJADI 'total_stok'
                return redirect()->back()->with('error', 'Total stok ' . $product->nama_barang . ' tidak cukup. Tersedia: ' . $product->total_stok . ', Total di keranjang: ' . $newQuantity);
            }
            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            // Jika produk belum ada, tambahkan sebagai item baru
            $cart[$productId] = [
                "product_id" => $product->id,
                "nama_barang" => $product->nama_barang,
                "price_at_sale" => $product->harga_jual,
                "quantity" => $quantity
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('penjualan.index')->with('success', $product->nama_barang . ' berhasil ditambahkan ke keranjang.');
    }

    /**
     * Menghapus item dari keranjang (menggunakan session).
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang.');
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
    }

    /**
     * Menyimpan transaksi penjualan.
     */
    public function store(Request $request)
    {
        $cartItems = session()->get('cart', []);

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Keranjang belanja tidak boleh kosong.');
        }

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // Validasi Stok Akhir dan Dapatkan Produk Aktual (Penting untuk race conditions)
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->total_stok < $item['quantity']) { // <-- UBAH DARI 'stok' MENJADI 'total_stok'
                    throw ValidationException::withMessages([
                        'stok' => ['Stok ' . $product->nama_barang . ' tidak cukup. Transaksi dibatalkan.'],
                    ]);
                }
            }

            // Hitung ulang total_amount dari item di session untuk keamanan
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']); // Ambil lagi produk untuk harga jual terbaru
                $totalAmount += $item['quantity'] * $product->harga_jual;
            }

            // Buat Transaksi Penjualan Baru
            $sale = Sale::create([
                'invoice_number' => 'INV-' . time() . Str::random(4),
                'total_amount' => $totalAmount,
            ]);

            // Simpan Item Penjualan dan Kurangi Stok Produk
            foreach ($cartItems as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $product->harga_jual, // Ambil harga jual dari produk saat ini
                ]);

                $product->decrement('total_stok', $item['quantity']); // <-- UBAH DARI 'stok' MENJADI 'total_stok'
            }

            DB::commit(); // Komit transaksi jika semua berhasil
            session()->forget('cart'); // Kosongkan keranjang setelah transaksi sukses

            return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan! Nomor Invoice: ' . $sale->invoice_number);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Validasi gagal: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman manajemen penjualan.
     */
    public function managePenjualan()
    {
        // Default, tidak ada penjualan yang ditampilkan saat pertama kali halaman dimuat
        $sales = collect();
        $startDate = null;
        $endDate = null;
        return view('admin.manage_penjualan', compact('sales', 'startDate', 'endDate'));
    }

    /**
     * Memfilter dan menampilkan daftar penjualan berdasarkan rentang tanggal.
     */
    public function filterPenjualan(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sales = collect(); // Default collection kosong

        if ($startDate && $endDate) {
            // Menggunakan Carbon untuk memastikan rentang waktu penuh dari tanggal awal hingga akhir
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();

            $sales = Sale::with('saleItems.product') // Load relasi saleItems dan product
                         ->whereBetween('created_at', [$startDateTime, $endDateTime])
                         ->orderBy('created_at', 'desc')
                         ->get();
        }

        // --- BARIS DEBUGGING BARU ---
        // dd($sales->toArray(), $startDate, $endDate); // Aktifkan ini jika masih tidak tampil data
        // --- AKHIR BARIS DEBUGGING BARU ---

        return view('admin.manage_penjualan', [ // Kembali ke view yang sama, tapi kirimkan data sales
            'sales' => $sales,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Menghapus transaksi penjualan dan mengembalikan stok.
     */
    public function deletePenjualan(Request $request, $id)
    {
        // Temukan transaksi penjualan
        $sale = Sale::with('saleItems.product')->find($id);

        if (!$sale) {
            Session::flash('error', 'Transaksi penjualan tidak ditemukan.');
            return redirect()->route('admin.manage.penjualan');
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok produk
            foreach ($sale->saleItems as $item) {
                $product = $item->product;
                if ($product) {
                    $product->total_stok += $item->quantity; // Tambahkan kembali stok
                    $product->save();
                }
            }

            // Hapus item penjualan
            $sale->saleItems()->delete();

            // Hapus transaksi penjualan
            $sale->delete();

            DB::commit();
            Session::flash('success', 'Transaksi penjualan berhasil dihapus dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Gagal menghapus transaksi penjualan: ' . $e->getMessage());
        }

        return redirect()->route('admin.manage.penjualan');
    }

    /**
     * Melakukan retur sebagian atau seluruh item penjualan.
     * Asumsi: request akan berisi sale_item_id dan quantity_to_return.
     * Untuk kesederhanaan, ini akan mengembalikan stok dan mengurangi dari sale_item.
     * Jika quantity_to_return sama dengan quantity asli, maka sale_item dianggap diretur penuh.
     */
    public function returPenjualan(Request $request, $saleId)
    {
        $request->validate([
            'sale_item_id' => 'required|exists:sale_items,id',
            'quantity_to_return' => 'required|integer|min:1',
        ]);

        $saleItem = SaleItem::with('product')->find($request->sale_item_id);

        if (!$saleItem || $saleItem->sale_id != $saleId) {
            Session::flash('error', 'Item penjualan tidak ditemukan atau tidak sesuai dengan transaksi.');
            return redirect()->back(); // Atau redirect ke halaman manajemen penjualan
        }

        $quantityToReturn = $request->quantity_to_return;

        if ($quantityToReturn > $saleItem->quantity) {
            Session::flash('error', 'Jumlah retur melebihi jumlah item yang terjual.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok produk
            $product = $saleItem->product;
            if ($product) {
                $product->total_stok += $quantityToReturn;
                $product->save();
            }

            // Kurangi kuantitas item penjualan
            $saleItem->quantity -= $quantityToReturn;
            // Jika kuantitas menjadi 0, hapus item penjualan
            if ($saleItem->quantity <= 0) {
                $saleItem->delete();
            } else {
                $saleItem->save();
            }

            // Cek apakah transaksi penjualan menjadi kosong (semua item diretur/dihapus)
            $sale = Sale::find($saleId);
            if ($sale && $sale->saleItems()->count() == 0) {
                $sale->delete(); // Hapus transaksi penjualan jika sudah tidak ada item
            }

            DB::commit();
            Session::flash('success', 'Item berhasil diretur dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Gagal melakukan retur: ' . $e->getMessage());
        }

        return redirect()->route('admin.manage.penjualan'); // Redirect ke halaman manajemen
    }

    // Metode searchProduct() tidak lagi digunakan karena kita lakukan pencarian di index()
    // public function searchProduct(Request $request) { ... }
}