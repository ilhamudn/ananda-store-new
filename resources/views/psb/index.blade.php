@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Form Transaksi PSB</h1>

    {{-- Pesan Sukses/Error --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Kolom Kiri: Detail Pendaftar & Item PSB --}}
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Detail Pendaftar & Item PSB</h2>

            {{-- Form Pencarian Barang PSB --}}
            <form action="{{ route('psb.index') }}" method="GET" class="mb-6">
                <div class="mb-4">
                    <label for="buyer_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Pembeli (Opsional):</label>
                    <input type="text" name="buyer_name" id="buyer_name"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           placeholder="Bisa dikosongkan jika tidak ada nama pembeli spesifik."
                           value="{{ old('buyer_name', $psbBuyerName) }}">
                    @if ($errors->has('buyer_name'))
                        <p class="text-red-500 text-xs italic">{{ $errors->first('buyer_name') }}</p>
                    @endif
                </div>

                <label for="search_query" class="block text-gray-700 text-sm font-bold mb-2">Cari Item PSB (Nama/Barcode):</label>
                <div class="flex">
                    <input type="text" name="search_query" id="search_query"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2"
                           placeholder="Ketik nama atau barcode item PSB..." value="{{ old('search_query', $searchTerm) }}">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Cari
                    </button>
                </div>
                @if ($errors->has('search_query'))
                    <p class="text-red-500 text-xs italic">{{ $errors->first('search_query') }}</p>
                @endif
            </form>

            {{-- Hasil Pencarian --}}
            @if($products->isNotEmpty())
                <h3 class="text-lg font-semibold mb-3">Hasil Pencarian:</h3>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Item</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->nama_barang }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($product->harga_jual, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $product->total_stok }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        {{-- Form untuk menambah item ke keranjang --}}
                                        <form action="{{ route('psb.addToCart') }}" method="POST" class="inline-block">
                                            @csrf
                                            {{-- Kirim buyer_name juga agar tidak hilang saat add to cart --}}
                                            <input type="hidden" name="buyer_name" value="{{ old('buyer_name', $psbBuyerName) }}">
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            {{-- PASTIKAN MENGGUNAKAN total_stok DI SINI --}}
                                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->total_stok }}"
                                                   class="w-16 py-1 px-2 border rounded text-sm text-center mr-2">
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-3 rounded-md text-sm">
                                                Add
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif (!empty($searchTerm) && strlen($searchTerm) >= 2 && $products->isEmpty())
                <p class="text-gray-600 text-center">Item PSB tidak ditemukan.</p>
            @endif
        </div>

        {{-- Kolom Kanan: Keranjang Item PSB --}}
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Keranjang Item PSB</h2>

            @if (empty($cartItems))
                <p class="text-gray-600 text-center">Keranjang masih kosong.</p>
            @else
                <div>
                    <table class="min-w-full bg-white border border-gray-300 mb-4">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Nama Item</th>
                                <th class="py-2 px-4 border-b text-center">Jumlah</th>
                                <th class="py-2 px-4 border-b text-right">Harga</th>
                                <th class="py-2 px-4 border-b">Subtotal</th>
                                <th class="py-2 px-4 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cartItems as $item)
                                <tr>
                                    <td class="py-2 px-4 border-b">{{ $item['nama_barang'] }}</td>
                                    <td class="py-2 px-4 border-b text-center">
                                        {{-- Form untuk update jumlah --}}
                                        <form action="{{ route('psb.addToCart') }}" method="POST" class="inline-block">
                                            @csrf
                                            {{-- Kirim buyer_name juga agar tidak hilang saat update quantity --}}
                                            <input type="hidden" name="buyer_name" value="{{ old('buyer_name', $psbBuyerName) }}">
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            {{-- Penting: max di sini harus mengambil stok produk aktual dari database --}}
                                            {{-- Anda perlu mengambil ulang produk di controller atau mengirimkan stok max ke view --}}
                                            {{-- Untuk saat ini, kita asumsikan stok max sudah ada di item keranjang --}}
                                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                                   class="w-20 text-center border rounded py-1 px-2">
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white py-1 px-2 rounded text-xs">Update</button>
                                        </form>
                                    </td>
                                    <td class="py-2 px-4 border-b text-right">Rp {{ number_format($item['price_at_sale'], 2, ',', '.') }}</td>
                                    <td class="py-2 px-4 border-b text-right">Rp {{ number_format($item['quantity'] * $item['price_at_sale'], 2, ',', '.') }}</td>
                                    <td class="py-2 px-4 border-b text-center">
                                        {{-- Form untuk menghapus item --}}
                                        <form action="{{ route('psb.removeFromCart') }}" method="POST" class="inline-block">
                                            @csrf
                                            {{-- Kirim buyer_name juga agar tidak hilang saat remove from cart --}}
                                            <input type="hidden" name="buyer_name" value="{{ old('buyer_name', $psbBuyerName) }}">
                                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-1 px-2 rounded text-xs">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="py-2 px-4 font-bold text-right border-t">Total Transaksi PSB:</td>
                                <td class="py-2 px-4 font-bold text-right border-t">Rp {{ number_format($cartTotal, 2, ',', '.') }}</td>
                                <td class="py-2 px-4 border-t"></td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- Form Selesaikan Transaksi PSB --}}
                    <form id="psbTransactionForm" action="{{ route('psb.store') }}" method="POST">
                        @csrf
                        {{-- Hapus hidden input 'items' karena data keranjang diambil langsung dari session di backend --}}
                        {{-- Hidden input untuk mengirim buyer_name --}}
                        <input type="hidden" name="buyer_name" value="{{ old('buyer_name', $psbBuyerName) }}">

                        <div class="flex justify-end">
                            <button type="submit" id="completePsbButton"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Selesaikan Transaksi PSB
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection