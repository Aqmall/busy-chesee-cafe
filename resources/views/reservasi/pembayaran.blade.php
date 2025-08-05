@extends('layouts.flow', ['currentStep' => 4])

@section('content')
<div class="max-w-md mx-auto text-center">
    <h2 class="text-3xl font-bold text-cafe-secondary mb-2">Pembayaran</h2>
    <p class="text-gray-600 mb-8">Selesaikan pembayaran DP untuk mengonfirmasi reservasi Anda.</p>

    <div class="border rounded-lg p-6 space-y-4 bg-gray-50 mb-8">
        <div class="flex justify-between"><span>Total Tagihan:</span><span class="font-semibold">Rp {{ number_format($reservasi->pesanan->totalTagihan, 0, ',', '.') }}</span></div>
        <div class="flex justify-between text-2xl text-cafe-accent">
            <span class="font-semibold">Down Payment (50%):</span>
            <span class="font-bold">Rp {{ number_format($reservasi->pesanan->totalDP, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="font-semibold text-lg">Pilih Metode Pembayaran</h3>
        <form action="{{ route('reservasi.flow.pembayaran.store') }}" method="POST">
            @csrf
            {{-- Ini adalah simulasi. Di aplikasi nyata, setiap tombol akan mengarah ke payment gateway yang berbeda. --}}
            <button type="submit" class="w-full text-left p-4 border rounded-lg hover:bg-gray-100">Virtual Account</button>
            <button type="submit" class="w-full text-left p-4 border rounded-lg hover:bg-gray-100">QRIS</button>
            <button type="submit" class="w-full text-left p-4 border rounded-lg hover:bg-gray-100">Kartu Kredit/Debit</button>
        </form>
    </div>
</div>
@endsection