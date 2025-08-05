@extends('layouts.dashboard')

@section('content')
<div x-data="dashboard()">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Dasbor Operasional</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-500">Reservasi Hari Ini</h3><p class="text-3xl font-bold mt-1">{{ $stats['reservasiHariIni'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-500">Sudah Check-in</h3><p class="text-3xl font-bold mt-1">{{ $stats['sudahCheckIn'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-500">Meja Terisi</h3><p class="text-3xl font-bold mt-1">{{ $stats['mejaTerisi'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><h3 class="text-sm font-semibold text-gray-500">Pendapatan Hari Ini</h3><p class="text-xl font-bold mt-1">Rp {{ number_format($stats['pendapatanHariIni'], 0, ',', '.') }}</p></div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-700">Denah Meja Real-time</h3>
                <a href="{{ route('admin.reservasi.list') }}" class="text-sm font-semibold bg-gray-200 px-3 py-1 rounded-md hover:bg-gray-300">Daftar Reservasi</a>
            </div>
            <div class="relative bg-gray-50 rounded-xl p-4 border min-h-[500px] grid grid-cols-4 gap-4">
                @foreach($mejas as $meja)
                    @php
                        $colorClass = $meja->statusMeja == 'Tersedia' ? 'bg-status-available' : ($meja->statusMeja == 'Dipesan' ? 'bg-status-booked' : 'bg-status-occupied');
                        $textColor = $meja->statusMeja == 'Dipesan' ? 'text-cafe-secondary' : 'text-white';
                    @endphp
                    <div @click="openModal({{ $meja->nomorMeja }})" class="relative aspect-square rounded-full flex flex-col items-center justify-center cursor-pointer {{ $colorClass }} {{ $textColor }}">
                        <span class="font-bold text-2xl">{{ $meja->nomorMeja }}</span>
                        <span class="text-xs">{{ $meja->kapasitas }}p</span>
                        @if(isset($reservasiMap[$meja->nomorMeja]))
                            <span class="absolute -bottom-4 text-xs text-center text-gray-700 font-semibold">{{ explode(' ', $reservasiMap[$meja->nomorMeja]->namaPelanggan)[0] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
             <div class="mt-6 flex justify-center items-center flex-wrap gap-x-6 gap-y-2 text-sm">
                <div class="flex items-center space-x-2"><div class="w-4 h-4 rounded-full bg-status-available"></div><span>Tersedia ({{ $mejas->where('statusMeja', 'Tersedia')->count() }})</span></div>
                <div class="flex items-center space-x-2"><div class="w-4 h-4 rounded-full bg-status-booked"></div><span>Dipesan ({{ $mejas->where('statusMeja', 'Dipesan')->count() }})</span></div>
                <div class="flex items-center space-x-2"><div class="w-4 h-4 rounded-full bg-status-occupied"></div><span>Terisi ({{ $mejas->where('statusMeja', 'Terisi')->count() }})</span></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
             <h3 class="text-xl font-bold mb-4 text-gray-700">Aktivitas Terkini</h3>
            <div class="space-y-4">
                @foreach($recentActivity as $activity)
                <div class="flex items-start space-x-3">
                    <div class="text-gray-400 mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ $activity['message'] }}</p>
                        <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="showModal" @keydown.escape.window="showModal = false" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="showModal = false">
            {{-- Header Modal dengan Tombol Close --}}
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-bold" x-text="`Meja ${selectedTable.nomorMeja} - ${selectedTable.statusMeja}`"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="p-6">
                <div x-show="selectedTable.statusMeja === 'Tersedia'">
                    <h4 class="font-semibold mb-2">Buat Reservasi Walk-in</h4>
                    <form action="{{ route('admin.walkin.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="nomorMeja" :value="selectedTable.nomorMeja">
                        <div class="space-y-3">
                            <input name="namaPelanggan" class="w-full border p-2 rounded" placeholder="Nama Pelanggan" required>
                            <select name="jumlahTamu" class="w-full border p-2 rounded bg-white">
                                <template x-for="i in selectedTable.kapasitas">
                                    <option :value="i" x-text="`${i} orang`"></option>
                                </template>
                            </select>
                            <button type="submit" class="w-full bg-cafe-primary py-2 rounded text-cafe-secondary font-semibold">Buat Walk-in</button>
                        </div>
                    </form>
                </div>

                <div x-show="selectedTable.statusMeja !== 'Tersedia' && reservation.id">
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg">
                        <h4 class="font-semibold">Meja Aktif</h4>
                        <p>Pelanggan: <span class="font-bold" x-text="reservation.namaPelanggan"></span></p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a :href="`/admin/reservasi/${reservation.id}`" class="flex-1 text-center bg-gray-200 py-2 rounded font-semibold hover:bg-gray-300">Lihat Detail & Kelola</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function dashboard() {
        return {
            showModal: false,
            selectedTable: {},
            reservation: {},
            tables: @json($mejas),
            reservations: @json($reservasiMap),
            openModal(tableId) {
                this.selectedTable = this.tables.find(t => t.nomorMeja == tableId) || {};
                this.reservation = this.reservations[tableId] || {};
                this.showModal = true;
            }
        }
    }
</script>
@endpush
@endsection