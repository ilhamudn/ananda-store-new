{{-- Ini adalah partial form yang akan di-include --}}
{{-- Jangan masukkan tag <form> di sini, karena akan dimasukkan di create/edit blade --}}

<div class="mb-4">
    <label for="nama_barang" class="block text-gray-700 text-sm font-bold mb-2">Nama Barang:</label>
    <input type="text" name="nama_barang" id="nama_barang"
           value="{{ old('nama_barang', $product->nama_barang ?? '') }}" required
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nama_barang') border-red-500 @enderror">
    @error('nama_barang')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
</div>

{{-- Barcode sekarang ter-generate otomatis, jadi hanya ditampilkan di halaman edit --}}
@if(isset($product))
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Barcode:</label>
        <p class="text-gray-900 bg-gray-100 p-2 rounded">{{ $product->barcode }}</p>
        <p class="text-gray-600 text-xs italic mt-1">Barcode ini digenerate otomatis dan tidak bisa diubah.</p>
    </div>
@endif

<div class="mb-4">
    <label for="harga_beli" class="block text-gray-700 text-sm font-bold mb-2">Harga Beli:</label>
    <input type="number" name="harga_beli" id="harga_beli" step="0.01"
           value="{{ old('harga_beli', $product->harga_beli ?? '') }}" required
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('harga_beli') border-red-500 @enderror">
    @error('harga_beli')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="harga_jual" class="block text-gray-700 text-sm font-bold mb-2">Harga Jual (Opsional):</label>
    <input type="number" name="harga_jual" id="harga_jual" step="0.01"
           value="{{ old('harga_jual', $product->harga_jual ?? '') }}"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('harga_jual') border-red-500 @enderror">
    @error('harga_jual')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
    <p class="text-gray-600 text-xs italic mt-1">Jika kosong, harga jual akan diatur ke 0.</p>
</div>

<div class="mb-4">
    <label for="stok" class="block text-gray-700 text-sm font-bold mb-2">
        {{ isset($product) ? 'Tambahkan Stok (Jumlah Penambahan):' : 'Stok Awal:' }}
    </label>
    <input type="number" name="stok" id="stok"
           value="{{ old('stok', isset($product) ? 0 : '') }}" required
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('stok') border-red-500 @enderror">
    @error('stok')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
    @if(isset($product))
        <p class="text-gray-600 text-xs italic mt-1">Stok saat ini: {{ $product->total_stok }}</p>
    @endif
</div>

<div class="mb-4">
    <label for="tanggal_pembelian" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pembelian/Restock:</label>
    <input type="date" name="tanggal_pembelian" id="tanggal_pembelian"
           value="{{ old('tanggal_pembelian', $product->tanggal_pembelian ?? \Carbon\Carbon::now()->toDateString()) }}" required
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('tanggal_pembelian') border-red-500 @enderror">
    @error('tanggal_pembelian')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="supplier_name" class="block text-gray-700 text-sm font-bold mb-2">Nama Supplier (Opsional):</label>
    <input type="text" name="supplier_name" id="supplier_name"
           value="{{ old('supplier_name', $product->last_supplier_name ?? '') }}"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('supplier_name') border-red-500 @enderror"
           placeholder="Isi nama supplier...">
    @error('supplier_name')
        <p class="text-red-500 text-xs italic">{{ $message }}</p>
    @enderror
    <p class="text-gray-600 text-xs italic mt-1">Bisa dikosongkan jika tidak ada supplier.</p>
</div>