<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\PsbTransactionItem;
use App\Models\Sale;
use App\Models\Purchase; // Import model Purchase
use App\Models\PsbTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Untuk aggregation query


class ReportController extends Controller
{
    public function combinedSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sales = collect(); // Koleksi kosong untuk penjualan umum
        $psbTransactions = collect(); // Koleksi kosong untuk penjualan PSB

        if ($startDate && $endDate) {
            // Validasi format tanggal (opsional tapi bagus)
            try {
                $formattedStartDate = Carbon::parse($startDate)->startOfDay();
                $formattedEndDate = Carbon::parse($endDate)->endOfDay();
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Format tanggal tidak valid.')->withInput();
            }

            // Ambil data Penjualan Umum
            $sales = Sale::with('items.product') // Load relasi items dan product dari item
                         ->whereBetween('created_at', [$formattedStartDate, $formattedEndDate])
                         ->orderBy('created_at', 'asc')
                         ->get();

            // Ambil data Penjualan PSB
            $psbTransactions = PsbTransaction::with('items.product') // Load relasi items dan product dari item
                                           ->whereBetween('created_at', [$formattedStartDate, $formattedEndDate])
                                           ->orderBy('created_at', 'asc')
                                           ->get();
        }

        return view('reports.combined_sales_report', compact('sales', 'psbTransactions', 'startDate', 'endDate'));
    }

    public function purchaseReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $purchases = collect(); // Koleksi kosong

        if ($startDate && $endDate) {
            try {
                $formattedStartDate = Carbon::parse($startDate)->startOfDay();
                $formattedEndDate = Carbon::parse($endDate)->endOfDay();
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Format tanggal tidak valid.')->withInput();
            }

            $purchases = Purchase::with(['supplier', 'items.product']) // Load relasi supplier, items, dan product dari item
                                 ->whereBetween('created_at', [$formattedStartDate, $formattedEndDate])
                                 ->orderBy('created_at', 'asc')
                                 ->get();
        }

        return view('reports.purchase_report', compact('purchases', 'startDate', 'endDate'));
    }

     public function salesRecap(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Inisialisasi data laporan kosong
        $allRecapData = [];
        $totalBeliGlobal = 0;
        $totalJualGlobal = 0;
        $totalLabaGlobal = 0;

        // Hanya proses data jika kedua tanggal telah diisi
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // --- AMBIL DATA DARI PSB TRANSACTIONS ---
            $psbTransactions = PsbTransaction::whereBetween('created_at', [$start, $end])
                                             ->with('items.product')
                                             ->orderBy('created_at', 'asc')
                                             ->get();

            foreach ($psbTransactions as $transaction) {
                $transactionDate = Carbon::parse($transaction->created_at)->toDateString();
                $transactionTotalBeli = 0;
                $transactionTotalJual = 0;
                $transactionTotalLaba = 0;

                foreach ($transaction->items as $item) {
                    if ($item->product) {
                        $hargaBeliUnit = $item->product->harga_beli;
                        $hargaJualUnit = $item->price_at_transaction;

                        $hargaBeliItem = $hargaBeliUnit * $item->quantity;
                        $hargaJualItem = $hargaJualUnit * $item->quantity;
                        $labaItem = $hargaJualItem - $hargaBeliItem;

                        $transactionTotalBeli += $hargaBeliItem;
                        $transactionTotalJual += $hargaJualItem;
                        $transactionTotalLaba += $labaItem;
                    }
                }

                $allRecapData[] = [
                    'type' => 'PSB', // Menandai jenis transaksi
                    'tanggal' => $transactionDate,
                    'invoice_number' => $transaction->invoice_number,
                    'customer_info' => $transaction->buyer_name ?? '-', // Nama pembeli PSB
                    'total_beli' => $transactionTotalBeli,
                    'total_jual' => $transactionTotalJual,
                    'laba' => $transactionTotalLaba,
                ];

                $totalBeliGlobal += $transactionTotalBeli;
                $totalJualGlobal += $transactionTotalJual;
                $totalLabaGlobal += $transactionTotalLaba;
            }

            // --- AMBIL DATA DARI SALES (PENJUALAN BIASA) ---
            $sales = Sale::whereBetween('created_at', [$start, $end])
                         ->with('items.product')
                         ->orderBy('created_at', 'asc')
                         ->get();

            foreach ($sales as $sale) {
                $saleDate = Carbon::parse($sale->created_at)->toDateString();
                $saleTotalBeli = 0;
                $saleTotalJual = 0;
                $saleTotalLaba = 0;

                foreach ($sale->items as $item) {
                    if ($item->product) {
                        $hargaBeliUnit = $item->product->harga_beli;
                        $hargaJualUnit = $item->price_at_sale;

                        $hargaBeliItem = $hargaBeliUnit * $item->quantity;
                        $hargaJualItem = $hargaJualUnit * $item->quantity;
                        $labaItem = $hargaJualItem - $hargaBeliItem;

                        $saleTotalBeli += $hargaBeliItem;
                        $saleTotalJual += $hargaJualItem;
                        $saleTotalLaba += $labaItem; // PERBAIKAN DI SINI: Akumulasi laba per item ke saleTotalLaba
                    }
                }

                $allRecapData[] = [
                    'type' => 'Umum', // Menandai jenis transaksi
                    'tanggal' => $saleDate,
                    'invoice_number' => $sale->invoice_number,
                    'customer_info' => $sale->customer_name ?? '-', // Asumsi Sales punya customer_name
                    'total_beli' => $saleTotalBeli,
                    'total_jual' => $saleTotalJual,
                    'laba' => $saleTotalLaba,
                ];

                // PERBAIKAN DI SINI: Gunakan $saleTotalLaba
                $totalBeliGlobal += $saleTotalBeli;
                $totalJualGlobal += $saleTotalJual;
                $totalLabaGlobal += $saleTotalLaba;
            }

            // Urutkan semua data berdasarkan tanggal transaksi setelah semua data terkumpul
            usort($allRecapData, function($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
        }


        return view('reports.sales_recap', compact(
            'allRecapData',
            'totalBeliGlobal',
            'totalJualGlobal',
            'totalLabaGlobal',
            'startDate',
            'endDate'
        ));
    }

   public function stockReport()
    {
        $stockData = [];
        $products = Product::orderBy('nama_barang')->get();

        // Opsional: Jika ingin debugging lagi, gunakan 'total_stok' di sini
        // dd($products->pluck('total_stok', 'nama_barang')->toArray());

        // --- 1. Hitung Total Pembelian (dari purchase_items) ---
        $totalPurchasedItems = PurchaseItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                                            ->groupBy('product_id')
                                            ->pluck('total_quantity', 'product_id');

        // --- 2. Hitung Total Penjualan Biasa (dari sale_items) ---
        $totalSoldItems = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                                    ->groupBy('product_id')
                                    ->pluck('total_quantity', 'product_id');

        // --- 3. Hitung Total Penjualan PSB (dari psb_transaction_items) ---
        $totalPsbSoldItems = PsbTransactionItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                                                ->groupBy('product_id')
                                                ->pluck('total_quantity', 'product_id');


        foreach ($products as $product) {
            // --- Kolom Saldo (Stok Tersedia Saat Ini) ---
            // Ini harus menggunakan 'total_stok'
            $saldo = (int) $product->total_stok; // <-- Pastikan ini total_stok

            // --- Kolom Total Pembelian (Via Purchase Items) ---
            $totalViaPurchases = $totalPurchasedItems->get($product->id, 0);

            // --- Kolom Total Penjualan Umum ---
            $totalSalesUmum = $totalSoldItems->get($product->id, 0);

            // --- Kolom Total Penjualan PSB ---
            $totalSalesPsb = $totalPsbSoldItems->get($product->id, 0);

            // --- Kolom Kredit (Total Stok Keluar) ---
            $kredit = $totalSalesUmum + $totalSalesPsb;

            // --- Kolom Debet (Total Stok Masuk Akumulatif) ---
            // Rumus ini sudah benar jika Saldo dan Kredit sudah benar.
            $debet = $saldo + $kredit;

            $stockData[] = [
                'nomor' => count($stockData) + 1,
                'barcode' => $product->barcode,
                'nama_barang' => $product->nama_barang,
                'total_via_purchases' => $totalViaPurchases,
                'total_sales_umum' => $totalSalesUmum,
                'total_sales_psb' => $totalSalesPsb,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $saldo,
            ];
        }

        return view('reports.stock_report', compact('stockData'));
    }
}