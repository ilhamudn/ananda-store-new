@extends('layouts.app') {{-- Asumsi layout Anda adalah layouts.app --}}

@section('title', 'Laporan Rekapitulasi Penjualan Komprehensif')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-center">Laporan Rekapitulasi Penjualan (PSB & Umum)</h1>

    {{-- Filter Form --}}
    <div class="bg-white p-4 rounded shadow-md mb-6 filter-form flex justify-center"> {{-- Tambah flex justify-center untuk menengahkan --}}
        <form action="{{ route('reports.sales_recap') }}" method="GET" class="flex flex-wrap items-end gap-4">
            {{-- Filter Rentang Tanggal --}}
            <div>
                <label for="start_date" class="block text-gray-700 text-sm font-bold mb-1">Dari Tanggal:</label>
                <input type="date" name="start_date" id="start_date"
                       value="{{ old('start_date', $startDate) }}"
                       class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="end_date" class="block text-gray-700 text-sm font-bold mb-1">Sampai Tanggal:</label>
                <input type="date" name="end_date" id="end_date"
                       value="{{ old('end_date', $endDate) }}"
                       class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Tampilkan Laporan
            </button>
            <button type="button" onclick="window.print()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline print-button">
                Cetak Laporan
            </button>
        </form>
    </div>

    {{-- Tabel Laporan --}}
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        {{-- Pesan jika tanggal belum diisi --}}
        @if (!$startDate || !$endDate)
            <p class="text-center text-gray-600 mb-4">Silakan pilih "Dari Tanggal" dan "Sampai Tanggal" untuk menampilkan laporan.</p>
        @elseif (empty($allRecapData))
            <p class="text-center text-gray-600">Tidak ada data rekapitulasi penjualan untuk periode ini.</p>
        @else
            <table class="min-w-full divide-y divide-gray-800">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Transaksi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informasi Pembeli</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga Beli (Estimasi)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga Jual</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laba (Estimasi)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($allRecapData as $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['tanggal'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['type'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['invoice_number'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data['customer_info'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($data['total_beli'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($data['total_jual'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($data['laba'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    {{-- Baris Total --}}
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="4" class="px-6 py-4 text-right text-sm text-gray-900">TOTAL KESELURUHAN:</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($totalBeliGlobal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($totalJualGlobal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($totalLabaGlobal, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Sembunyikan tombol cetak dan form filter saat mencetak */
    @media print {
        .print-button, .filter-form {
            display: none;
        }
    }
</style>
@endpush