<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PsbController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome'); // Atau 'dashboard' jika kamu membuat dashboard.blade.php
});

// === Rute-rute Pembelian & Manajemen Barang ===
// Menggunakan prefix 'pembelian' karena PembelianController yang mengelola semuanya
Route::prefix('pembelian')->name('pembelian.')->group(function () {
    // Rute untuk Daftar Barang (index PembelianController) - image_c1c9f8.png
    // Sebelumnya: Route::get('/pembelian/items', ...)
    Route::get('/', [PembelianController::class, 'index'])->name('index'); // Menggunakan '/' di dalam grup prefix 'pembelian'

    // Rute untuk Pembelian Barang Baru (create & store) - image_c1ca4c.png
    Route::get('/create', [PembelianController::class, 'create'])->name('create');
    Route::post('/', [PembelianController::class, 'store'])->name('store'); // Menggunakan '/' di dalam grup prefix 'pembelian'

    // Rute untuk Tambahkan Item / Update Barang (edit & update) - image_c0fae6.png
    // Ini adalah rute yang akan diakses dari tombol 'Edit/Restock' di 'Daftar Barang'
    // Menggunakan {product} sebagai parameter untuk Route Model Binding
    // Sebelumnya: Route::get('/pembelian/items/{product}/edit', ...) dan Route::put('/pembelian/items/{product}', ...)
    Route::get('/{product}/edit', [PembelianController::class, 'edit'])->name('edit');
    Route::put('/{product}', [PembelianController::class, 'update'])->name('update');
});


// === Rute-rute Penjualan ===
// Tidak ada perubahan di sini jika sudah berfungsi
Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::post('/penjualan/add-to-cart', [PenjualanController::class, 'addToCart'])->name('penjualan.addToCart');
Route::post('/penjualan/remove-from-cart', [PenjualanController::class, 'removeFromCart'])->name('penjualan.removeFromCart');
Route::post('/penjualan/complete-sale', [PenjualanController::class, 'store'])->name('penjualan.store');

// === Rute-rute PSB ===
// Tidak ada perubahan di sini jika sudah berfungsi
Route::get('/psb', [PsbController::class, 'index'])->name('psb.index');
Route::post('/psb/add-to-cart', [PsbController::class, 'addToCart'])->name('psb.addToCart');
Route::post('/psb/remove-from-cart', [PsbController::class, 'removeFromCart'])->name('psb.removeFromCart');
Route::post('/psb/complete-transaction', [PsbController::class, 'store'])->name('psb.store');

// === Rute-rute Laporan ===
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/combined-sales', [ReportController::class, 'combinedSalesReport'])->name('combined_sales');
    Route::get('/purchases', [ReportController::class, 'purchaseReport'])->name('purchases'); // Rute untuk laporan pembelian
    Route::get('/sales-recap', [ReportController::class, 'salesRecap'])->name('sales_recap');
    Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock_report');
});

// Route untuk halaman manajemen delete/retur penjualan
Route::get('/admin/manage-penjualan', [PenjualanController::class, 'managePenjualan'])->name('admin.manage.penjualan');
Route::post('/admin/manage-penjualan', [PenjualanController::class, 'filterPenjualan'])->name('admin.filter.penjualan');

// Route untuk aksi delete (menggunakan DELETE method)
Route::delete('/admin/penjualan/{id}/delete', [PenjualanController::class, 'deletePenjualan'])->name('admin.penjualan.delete');

// Route untuk aksi retur (menggunakan POST method, atau PUT/PATCH jika ingin update status)
Route::post('/admin/penjualan/{id}/retur', [PenjualanController::class, 'returPenjualan'])->name('admin.penjualan.retur');


Route::get('/admin/calculator', function () { return "Halaman Calculator"; });