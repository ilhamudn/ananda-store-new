@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Laporan Penjualan & PSB Komprehensif</h1>

    {{-- Form Filter Tanggal --}}
    <div class="bg-white p-6 rounded-lg shadow-md mb-6 print:hidden"> {{-- Tambah print:hidden --}}
        <form action="{{ route('reports.combined_sales') }}" method="GET" class="flex flex-wrap items-end space-x-4">
            <div class="mb-4 md:mb-0">
                <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal:</label>
                <input type="date" id="start_date" name="start_date"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       value="{{ old('start_date', $startDate) }}" required>
            </div>
            <div class="mb-4 md:mb-0">
                <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal:</label>
                <input type="date" id="end_date" name="end_date"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       value="{{ old('end_date', $endDate) }}" required>
            </div>
            <div class="mb-4 md:mb-0">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Tampilkan Laporan
                </button>
            </div>
            @if($startDate && $endDate)
            <div class="mb-4 md:mb-0">
                <button type="button" onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Cetak Laporan
                </button>
            </div>
            @endif
        </form>
    </div>

    @if(!$startDate || !$endDate)
        <div class="bg-white p-8 rounded-lg shadow-md text-center text-gray-600">
            <p>Silakan pilih rentang tanggal untuk menampilkan laporan.</p>
        </div>
    @else
        {{-- Tanggal Laporan Disesuaikan --}}
        <div class="text-center mb-6 text-xl font-semibold print-only"> {{-- Akan tampil saat print --}}
            Laporan untuk periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </div>

        {{-- BAGIAN LAPORAN PENJUALAN UMUM --}}
        <div class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-center text-black-700">Laporan Penjualan Umum</h2>

            @if($sales->isNotEmpty())
                @php
                    $grandTotalSales = 0;
                @endphp

                @foreach($sales as $sale)
                    <div class="mb-6 border border-gray-200 p-4 rounded-md break-inside-avoid"> {{-- break-inside-avoid untuk print --}}
                        <div class="flex justify-between items-center mb-2 pb-2 border-b">
                            <p class="font-bold text-lg">Invoice: {{ $sale->invoice_number }}</p>
                            <p class="font-bold text-gray-600 text-sm">Tanggal: {{ $sale->created_at->format('d F Y H:i:s') }}</p>
                        </div>
                        <table class="min-w-full bg-white mb-2 text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-2 px-4 border-b text-left">Nama Barang</th>
                                    <th class="py-2 px-4 border-b text-center">Jumlah</th>
                                    <th class="py-2 px-4 border-b text-right">Harga Satuan</th>
                                    <th class="py-2 px-4 border-b text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                    <tr>
                                        <td class="py-1 px-4 border-b">{{ $item->product->nama_barang }}</td>
                                        <td class="py-1 px-4 border-b text-center">{{ $item->quantity }}</td>
                                        <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->price_at_sale, 2, ',', '.') }}</td>
                                        <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->quantity * $item->price_at_sale, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="py-2 px-4 font-bold text-right border-t">Total Transaksi:</td>
                                    <td class="py-2 px-4 font-bold text-right border-t">Rp {{ number_format($sale->total_amount, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @php
                        $grandTotalSales += $sale->total_amount;
                    @endphp
                @endforeach

                <div class="text-right mt-6 pt-4">
                    <p class="text-2xl font-bold text-gray-800">Total Penjualan Umum: Rp {{ number_format($grandTotalSales, 2, ',', '.') }}</p>
                </div>
            @else
                <p class="text-gray-600 text-center">Tidak ada data penjualan umum dalam rentang tanggal yang dipilih.</p>
            @endif
        </div>

        {{-- BATAS ANTARA LAPORAN PENJUALAN UMUM DAN PSB --}}
        <hr class="my-10 border-t-4 border-black-300"> {{-- Garis pemisah yang tebal --}}

        {{-- BAGIAN LAPORAN PENJUALAN PSB --}}
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-center text-black-700">Laporan Penjualan PSB</h2>

            @if($psbTransactions->isNotEmpty())
                @php
                    $grandTotalPsb = 0;
                @endphp

                @foreach($psbTransactions as $transaction)
                    <div class="mb-6 border border-gray-200 p-4 rounded-md break-inside-avoid"> {{-- break-inside-avoid untuk print --}}
                        <div class="flex justify-between items-center mb-2 pb-2 border-b">
                            <p class="font-bold text-lg">Invoice: {{ $transaction->invoice_number }}</p>
                            <p class="font-bold text-md text-gray-700">Pembeli: {{ $transaction->buyer_name }}</p>
                            <p class=" font-bold text-gray-600 text-sm">Tanggal: {{ $transaction->created_at->format('d F Y H:i:s') }}</p>
                        </div>
                        <table class="min-w-full bg-white mb-2 text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="py-2 px-4 border-b text-left">Nama Item PSB</th>
                                    <th class="py-2 px-4 border-b text-center">Jumlah</th>
                                    <th class="py-2 px-4 border-b text-right">Harga Satuan</th>
                                    <th class="py-2 px-4 border-b text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->items as $item)
                                    <tr>
                                        <td class="py-1 px-4 border-b">{{ $item->product->nama_barang }}</td>
                                        <td class="py-1 px-4 border-b text-center">{{ $item->quantity }}</td>
                                        <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->price_at_transaction, 2, ',', '.') }}</td>
                                        <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->quantity * $item->price_at_transaction, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="py-2 px-4 font-bold text-right border-t">Total Transaksi:</td>
                                    <td class="py-2 px-4 font-bold text-right border-t">Rp {{ number_format($transaction->total_amount, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @php
                        $grandTotalPsb += $transaction->total_amount;
                    @endphp
                @endforeach

                <div class="text-right mt-6 pt-4">
                    <p class="text-2xl font-bold text-gray-800">Total Penjualan PSB: Rp {{ number_format($grandTotalPsb, 2, ',', '.') }}</p>
                </div>
            @else
                <p class="text-gray-600 text-center">Tidak ada data penjualan PSB dalam rentang tanggal yang dipilih.</p>
            @endif
        </div>
    @endif
</div>
@endsection

{{-- Bagian untuk Styling Print --}}
<style>
@media print {

    #main-navbar,
    #main-footer { /* Jika ada footer dan diberi id="main-footer" */
        display: none !important;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
    }
    .container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .print\\:hidden { /* Untuk menyembunyikan elemen saat cetak */
        display: none !important;
    }
    .print-only { /* Untuk menampilkan elemen hanya saat cetak */
        display: block !important;
    }
    .bg-white, .shadow-md, .border {
        box-shadow: none !important;
        border: none !important;
    }
    .p-4, .p-6, .p-8 {
        padding: 0.5rem !important;
    }
    .mb-6, .mb-4, .mb-2 {
        margin-bottom: 0.5rem !important;
    }
    .my-10 {
        margin-top: 1rem !important; /* Kurangi margin vertikal untuk print */
        margin-bottom: 1rem !important;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 0.5rem; /* Sedikit margin antar tabel */
    }
    th, td {
        border: 1px solid #ddd; /* Tambah border untuk print */
        padding: 4px 8px;
        font-size: 9pt;
    }
    .border-b, .border-t {
        border-width: 1px !important;
        border-color: #ddd !important;
    }
    .border-t-2 {
        border-top-width: 2px !important;
        border-color: #aaa !important;
    }
    .break-inside-avoid { /* Membantu mencegah pemotongan konten saat cetak */
        break-inside: avoid;
    }
    h1, h2 {
        break-after: avoid; /* Mencegah judul terpisah dari kontennya */
    }
}
/* Default: Sembunyikan print-only di layar */
.print-only {
    display: none;
}
</style>