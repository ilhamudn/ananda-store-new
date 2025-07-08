@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Manajemen Penjualan (Delete & Retur)</h1>

    {{-- Flash Messages --}}
    @if (Session::has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ Session::get('success') }}</span>
        </div>
    @endif
    @if (Session::has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ Session::get('error') }}</span>
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

    {{-- Filter Penjualan Berdasarkan Tanggal --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Filter Penjualan Berdasarkan Tanggal</h2>
        {{-- UBAH METHOD DARI GET MENJADI POST DAN TAMBAHKAN @csrf --}}
        <form action="{{ route('admin.filter.penjualan') }}" method="POST" class="flex items-end space-x-4">
            @csrf {{-- Tambahkan CSRF token untuk form POST --}}
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal:</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $start_date ?? '') }}"
                       class="form-input rounded-md shadow-sm mt-1 block w-full">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal:</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $end_date ?? '') }}"
                       class="form-input rounded-md shadow-sm mt-1 block w-full">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                Tampilkan Penjualan
            </button>
        </form>
    </div>

    {{-- Daftar Transaksi Penjualan --}}
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Daftar Transaksi Penjualan (Umum & PSB)</h2>

        @if (isset($sales) && $sales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Invoice
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Harga
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Detail Item
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($sales as $sale)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $sale->invoice_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sale->created_at->format('d-m-Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <ul>
                                        @foreach ($sale->saleItems as $item)
                                            <li>
                                                {{ $item->product->nama_barang ?? 'N/A' }} ({{ $item->quantity }} pcs)
                                                - Rp {{ number_format($item->price_at_sale, 0, ',', '.') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    {{-- Form untuk DELETE --}}
                                    <form action="{{ route('admin.penjualan.delete', $sale->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok akan dikembalikan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 mr-2">Hapus</button>
                                    </form>

                                    {{-- Tombol untuk menampilkan modal Retur --}}
                                    <button type="button" class="text-blue-600 hover:text-blue-900"
                                            onclick="showReturModal({{ $sale->id }}, {{ json_encode($sale->saleItems->map(function($item) {
                                                return [
                                                    'id' => $item->id,
                                                    'product_name' => $item->product->nama_barang ?? 'N/A',
                                                    'quantity' => $item->quantity
                                                ];
                                            })) }})">
                                        Retur
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">Tidak ada transaksi penjualan dalam rentang tanggal yang dipilih.</p>
        @endif
    </div>

    {{-- Modal Retur (Menggunakan Alpine.js atau jQuery sederhana) --}}
    <div x-data="{ open: false, saleId: null, saleItems: [], selectedSaleItemId: null, maxQuantity: 0, quantityToReturn: 1 }"
         x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div x-on:click.away="open = false" class="relative bg-white w-full max-w-md mx-auto rounded-lg shadow-xl p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Retur Item Penjualan</h3>
                <form id="returForm" method="POST">
                    @csrf
                    <input type="hidden" name="sale_item_id" x-model="selectedSaleItemId">

                    <div class="mb-4">
                        <label for="itemSelect" class="block text-sm font-medium text-gray-700">Pilih Item:</label>
                        <select id="itemSelect" x-model="selectedSaleItemId"
                                @change="
                                    const selectedItem = saleItems.find(item => item.id == selectedSaleItemId);
                                    maxQuantity = selectedItem ? selectedItem.quantity : 0;
                                    quantityToReturn = 1; // Reset quantity to 1
                                "
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <template x-for="item in saleItems" :key="item.id">
                                <option :value="item.id" x-text="item.product_name + ' (Qty: ' + item.quantity + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="quantityToReturn" class="block text-sm font-medium text-gray-700">Jumlah Retur:</label>
                        <input type="number" id="quantityToReturn" name="quantity_to_return" x-model="quantityToReturn"
                               :min="1" :max="maxQuantity"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="open = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md">Batal</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">Proses Retur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan modal retur
        function showReturModal(saleId, items) {
            let modal = document.querySelector('[x-data]').__x.$data; // Akses data Alpine.js
            modal.open = true;
            modal.saleId = saleId;
            modal.saleItems = items;
            
            // Set item pertama sebagai default terpilih dan update maxQuantity
            if (items.length > 0) {
                modal.selectedSaleItemId = items[0].id;
                modal.maxQuantity = items[0].quantity;
                modal.quantityToReturn = 1;
            } else {
                modal.selectedSaleItemId = null;
                modal.maxQuantity = 0;
                modal.quantityToReturn = 0;
            }
            
            // Update form action
            document.getElementById('returForm').action = `/admin/penjualan/${saleId}/retur`;
        }
    </script>
</div>
@endsection