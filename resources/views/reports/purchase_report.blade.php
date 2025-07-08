{{-- resources/views/reports/purchase_report.blade.php --}}

@extends('layouts.app') {{-- Asumsi ini menggunakan layout yang sama --}}

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Laporan Pembelian Barang</h1>

    {{-- Form Filter Tanggal --}}
    <div class="bg-white p-6 rounded-lg shadow-md mb-6 print:hidden">
        <form action="{{ route('reports.purchases') }}" method="GET" class="flex flex-wrap items-end space-x-4">
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
            <p>Silakan pilih rentang tanggal untuk menampilkan laporan pembelian.</p>
        </div>
    @else
        <div class="text-center mb-6 text-xl font-semibold print-only">
            Laporan untuk periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </div>

        @if($purchases->isNotEmpty())
            @php
                $grandTotalAllPurchases = 0;
            @endphp

            @foreach($purchases as $purchase)
                <div class="bg-white p-8 rounded-lg shadow-md mb-8 break-inside-avoid">
                    <h2 class="text-2xl font-semibold mb-4 text-center text-blue-700">Invoice Pembelian: {{ $purchase->invoice_number }}</h2>
                    <p class="text-md mb-2">Tanggal Pembelian: {{ \Carbon\Carbon::parse($purchase->created_at)->format('d F Y H:i:s') }}</p>
                    <p class="text-md mb-4">Supplier: {{ $purchase->supplier->name ?? 'Tidak Diketahui' }}</p>

                    <table class="min-w-full bg-white mb-4 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="py-2 px-4 border-b text-left">Nama Barang</th>
                                <th class="py-2 px-4 border-b text-center">Jumlah</th>
                                <th class="py-2 px-4 border-b text-right">Harga Beli Satuan</th>
                                <th class="py-2 px-4 border-b text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $purchaseTotal = 0;
                            @endphp
                            @foreach($purchase->items as $item)
                                <tr>
                                    <td class="py-1 px-4 border-b">{{ $item->product->nama_barang }}</td>
                                    <td class="py-1 px-4 border-b text-center">{{ $item->quantity }}</td>
                                    <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->price_at_purchase, 2, ',', '.') }}</td>
                                    <td class="py-1 px-4 border-b text-right">Rp {{ number_format($item->quantity * $item->price_at_purchase, 2, ',', '.') }}</td>
                                </tr>
                                @php
                                    $purchaseTotal += ($item->quantity * $item->price_at_purchase);
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="py-2 px-4 font-bold text-right border-t">Total Invoice:</td>
                                <td class="py-2 px-4 font-bold text-right border-t">Rp {{ number_format($purchaseTotal, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @php
                    $grandTotalAllPurchases += $purchaseTotal;
                @endphp
            @endforeach

            <div class="text-right mt-6 pt-4 border-t-2 border-gray-400">
                <p class="text-2xl font-bold text-gray-800">Grand Total Semua Pembelian: Rp {{ number_format($grandTotalAllPurchases, 2, ',', '.') }}</p>
            </div>
        @else
            <p class="text-gray-600 text-center">Tidak ada data pembelian dalam rentang tanggal yang dipilih.</p>
        @endif
    @endif
</div>
@endsection

{{-- Bagian untuk Styling Print (sama seperti sebelumnya, pastikan #main-navbar dan #main-footer tersembunyi) --}}
<style>
@media print {
    #main-navbar,
    #main-footer {
        display: none !important;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .print\\:hidden {
        display: none !important;
    }
    .print-only {
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
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    th, td {
        border: 1px solid #ddd;
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
    .break-inside-avoid {
        break-inside: avoid;
    }
    h1, h2 {
        break-after: avoid;
    }
}
.print-only {
    display: none;
}
</style>