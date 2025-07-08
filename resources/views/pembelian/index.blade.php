@extends('layouts.app') {{-- Asumsi layout Anda adalah layouts.app --}}

@section('title', 'Daftar Barang')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-center">Daftar Barang</h1>

    {{-- Form Pencarian --}}
    <div class="bg-white p-4 rounded shadow-md mb-6">
        <form action="{{ route('pembelian.index') }}" method="GET" class="flex items-center gap-4">
            <input type="text" name="search" placeholder="Cari berdasarkan nama barang atau barcode..."
                   value="{{ request('search') }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Cari
            </button>
        </form>
    </div>

    {{-- Tombol Tambah Barang Baru --}}
    <div class="flex justify-end mb-4">
        <a href="{{ route('pembelian.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Tambah Barang Baru
        </a>
    </div>

    {{-- Tabel Daftar Barang --}}
    <div class="bg-white p-4 rounded shadow-md overflow-x-auto">
        @if ($products->isEmpty())
            <p class="text-center text-gray-600">Tidak ada barang ditemukan.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BARCODE</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAMA BARANG</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HARGA BELI</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HARGA JUAL</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STOK</th> {{-- Mengacu pada total_stok --}}
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TGL PEMBELIAN TERAKHIR</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SUPPLIER TERAKHIR</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AKSI</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($products as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->barcode }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->nama_barang }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($product->total_stok, 0, ',', '.') }}</td> {{-- UBAH INI: Menggunakan total_stok --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->tanggal_pembelian ? \Carbon\Carbon::parse($product->tanggal_pembelian)->format('d-m-Y') : '-' }} {{-- UBAH INI: Menggunakan tanggal_pembelian --}}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $product->last_supplier_name ?? '-' }} {{-- Menggunakan last_supplier_name --}}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('pembelian.edit', $product->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit/Restock</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Paginasi --}}
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
