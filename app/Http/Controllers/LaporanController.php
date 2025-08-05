<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Tentukan rentang tanggal, defaultnya bulan ini
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Ambil data pesanan yang sudah lunas dalam rentang tanggal
        $pesananSelesai = Pesanan::where('statusPembayaran', 'Lunas')
            ->whereBetween('updated_at', [$startDate, $endDate . ' 23:59:59']);

        // 1. Hitung Statistik Utama (Stat Cards)
        $totalPendapatan = $pesananSelesai->sum('totalTagihan');
        $jumlahTransaksi = $pesananSelesai->count();
        $rataRataOrder = ($jumlahTransaksi > 0) ? $totalPendapatan / $jumlahTransaksi : 0;

        // 2. Data untuk Grafik Pendapatan Harian
        $dailyRevenue = (clone $pesananSelesai)->selectRaw('DATE(updated_at) as date, SUM(totalTagihan) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        $chartLabels = $dailyRevenue->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d M'));
        $chartData = $dailyRevenue->pluck('total');

        // 3. Data untuk Menu Terlaris
        $menuTerlaris = DetailPesanan::select('menu_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->whereIn('pesanan_id', (clone $pesananSelesai)->pluck('id'))
            ->groupBy('menu_id')
            ->orderBy('total_terjual', 'desc')
            ->take(5)
            ->with('menu')
            ->get();

        return view('admin.laporan.index', compact(
            'totalPendapatan', 'jumlahTransaksi', 'rataRataOrder',
            'chartLabels', 'chartData', 'menuTerlaris',
            'startDate', 'endDate'
        ));
    }
}