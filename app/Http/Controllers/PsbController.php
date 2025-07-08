<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PsbTransaction;
use App\Models\PsbTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class PsbController extends Controller
{
    /**
     * Menampilkan halaman transaksi PSB, menangani pencarian produk,
     * dan menampilkan keranjang dari session.
     */
    public function index(Request $request)
    {
        $products = collect(); // Koleksi kosong untuk produk hasil pencarian
        $searchTerm = $request->input('search_query');

        if (!empty($searchTerm) && strlen($searchTerm) >= 2) { // Minimal 2 karakter untuk pencarian
            $products = Product::where('nama_barang', 'like', '%' . $searchTerm . '%')
                               ->orWhere('barcode', 'like', '%' . $searchTerm . '%')
                               ->where('total_stok', '>', 0) // Menggunakan 'total_stok'
                               ->limit(10) // Batasi hasil pencarian
                               ->get();
        }

        // Ambil item keranjang PSB dari session
        $cartItems = session()->get('psb_cart', []);

        // Ambil nama pembeli dari session (jika sudah diisi)
        // Menggunakan old() untuk mempertahankan nilai jika ada error validasi
        $psbBuyerName = old('buyer_name', session()->get('psb_buyer_name', ''));

        // Hitung ulang total keranjang dari session
        $cartTotal = 0;
        foreach ($cartItems as $item) {
            $cartTotal += $item['quantity'] * $item['price_at_sale'];
        }

        return view('psb.index', compact('products', 'searchTerm', 'cartItems', 'cartTotal', 'psbBuyerName'));
    }

    /**
     * Menambahkan produk ke keranjang PSB (menggunakan session).
     */
    public function addToCart(Request $request)
    {
        // Validasi nama pembeli sekarang opsional
        $request->validate([
            'buyer_name' => 'nullable|string|max:255', // UBAH: Tambahkan 'nullable'
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Simpan nama pembeli ke session (ubah string kosong jadi null)
        session()->put('psb_buyer_name', $request->buyer_name === '' ? null : $request->buyer_name);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        $product = Product::find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Item PSB tidak ditemukan.')->withInput();
        }

        if ($product->total_stok < $quantity) { // Menggunakan 'total_stok'
            return redirect()->back()->with('error', 'Stok ' . $product->nama_barang . ' tidak cukup. Tersedia: ' . $product->total_stok . ', Diminta: ' . $quantity)->withInput();
        }

        $cart = session()->get('psb_cart', []);

        if (isset($cart[$productId])) {
            // Jika produk sudah ada di keranjang, update kuantitas
            $newQuantity = $cart[$productId]['quantity'] + $quantity;
            if ($product->total_stok < $newQuantity) { // Menggunakan 'total_stok'
                return redirect()->back()->with('error', 'Total stok ' . $product->nama_barang . ' tidak cukup di keranjang PSB. Tersedia: ' . $product->total_stok . ', Total di keranjang: ' . $newQuantity)->withInput();
            }
            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            // Jika produk belum ada, tambahkan sebagai item baru
            $cart[$productId] = [
                "product_id" => $product->id,
                "nama_barang" => $product->nama_barang,
                "price_at_sale" => $product->harga_jual, // Menggunakan harga jual produk
                "quantity" => $quantity
            ];
        }

        session()->put('psb_cart', $cart);

        return redirect()->route('psb.index')->with('success', $product->nama_barang . ' berhasil ditambahkan ke keranjang PSB.')->withInput();
    }

    /**
     * Menghapus item dari keranjang PSB (menggunakan session).
     */
    public function removeFromCart(Request $request)
    {
        // Validasi nama pembeli sekarang opsional
        $request->validate([
            'buyer_name' => 'nullable|string|max:255', // UBAH: Tambahkan 'nullable'
            'product_id' => 'required|exists:products,id',
        ]);

        // Simpan nama pembeli ke session (ubah string kosong jadi null)
        session()->put('psb_buyer_name', $request->buyer_name === '' ? null : $request->buyer_name);

        $productId = $request->product_id;
        $cart = session()->get('psb_cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('psb_cart', $cart);
            return redirect()->back()->with('success', 'Item berhasil dihapus dari keranjang PSB.')->withInput();
        }

        return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang PSB.')->withInput();
    }

    /**
     * Menyimpan transaksi PSB.
     */
     public function store(Request $request)
    {
        // Ambil buyer_name dari request
        $buyerName = $request->input('buyer_name');

        // Ambil keranjang dari session
        $cartItems = session('psb_cart', []);

        // Lakukan validasi untuk buyer_name saja
        $request->validate([
            'buyer_name' => 'nullable|string|max:255',
        ]);

        // Validasi tambahan: Pastikan keranjang tidak kosong
        if (empty($cartItems)) {
            Session::flash('error', 'Keranjang PSB kosong, tidak dapat menyelesaikan transaksi.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $psbItemsData = [];

            foreach ($cartItems as $item) { // Iterasi melalui $cartItems dari session
                // Pastikan item memiliki product_id dan quantity
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    throw new \Exception("Data item tidak lengkap di keranjang.");
                }

                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Produk dengan ID " . $item['product_id'] . " tidak ditemukan.");
                }

                // Cek stok (jika ada manajemen stok untuk PSB)
                // Jika PSB tidak mengurangi stok, lewati bagian ini
                // Jika PSB mengurangi stok, pastikan stoknya cukup
                // if ($product->total_stok < $item['quantity']) {
                //     throw new \Exception("Stok tidak cukup untuk produk: " . $product->nama_barang);
                // }

                $totalAmount += $item['quantity'] * $product->harga_jual;
                $psbItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_transaction' => $product->harga_jual, // Menggunakan harga jual saat ini
                ];

                // Kurangi stok produk (jika PSB mempengaruhi stok)
                // $product->total_stok -= $item['quantity'];
                // $product->save();
            }

            // Buat transaksi PSB
            $psbTransaction = PsbTransaction::create([
                'invoice_number' => 'PSB-' . uniqid(), // Atau gunakan logika penomoran invoice Anda
                'buyer_name' => $buyerName ?? 'Pembeli Umum', // Gunakan $buyerName dari request
                'total_amount' => $totalAmount,
            ]);

            // Simpan item transaksi PSB
            foreach ($psbItemsData as $itemData) {
                $psbTransaction->psbTransactionItems()->create($itemData);
            }

            // Kosongkan keranjang PSB di session setelah transaksi selesai
            Session::forget('psb_cart');
            Session::forget('psb_buyer_name'); // Kosongkan juga nama pembeli dari session

            DB::commit();
            Session::flash('success', 'Transaksi PSB berhasil diselesaikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Gagal menyelesaikan transaksi PSB: ' . $e->getMessage());
        }

        return redirect()->route('psb.index');
    }
}