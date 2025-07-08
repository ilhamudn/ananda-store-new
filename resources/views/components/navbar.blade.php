<nav id="main-navbar" class="bg-gray-800 p-4 text-white">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-2xl font-bold">Ananda Store</a>

        <div class="flex space-x-6">
            <a href="/penjualan" class="hover:text-gray-300">Penjualan</a>

            <a href="/psb" class="hover:text-gray-300">PSB</a>

            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" class="hover:text-gray-300 focus:outline-none flex items-center">
                    Pembelian
                    <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute bg-gray-700 text-white mt-2 py-2 rounded shadow-lg z-10"
                     style="display: none;"> {{-- Tambahkan style="display: none;" untuk menghindari flash of unstyled content --}}
                    <a href="{{ route('pembelian.index') }}" class="block px-4 py-2 hover:bg-gray-600">Tambahkan Barang</a>
                    <a href="{{ route('pembelian.create') }}" class="block px-4 py-2 hover:bg-gray-600">Pembelian</a>
                </div>
            </div>

            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" class="hover:text-gray-300 focus:outline-none flex items-center">
                    Report
                    <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute bg-gray-700 text-white mt-2 py-2 rounded shadow-lg z-10"
                     style="display: none;">
                    <a href="{{ route('reports.combined_sales') }}" class="block px-4 py-2 hover:bg-gray-600">Laporan Penjualan</a>
                    <a href="{{ route('reports.purchases') }}" class="block px-4 py-2 hover:bg-gray-600">Laporan Pembelian</a>
                    <a href="{{ route('reports.sales_recap') }}" class="block px-4 py-2 hover:bg-gray-600">Rekapitulasi Penjualan</a>
                    <a href="{{ route('reports.stock_report') }}" class="block px-4 py-2 hover:bg-gray-600">Laporan Stock</a>
                </div>
            </div>

            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" class="hover:text-gray-300 focus:outline-none flex items-center">
                    Admin
                    <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute bg-gray-700 text-white mt-2 py-2 rounded shadow-lg z-10"
                     style="display: none;">
                    <a href="/admin/calculator" class="block px-4 py-2 hover:bg-gray-600">Calculator</a>
                    <a href="{{ route('admin.manage.penjualan') }}" class="block px-4 py-2 hover:bg-gray-600">Delete / Retur Penjualan</a>
                </div>
            </div>
        </div>
    </div>
</nav>