<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Menampilkan halaman utama dasbor.
     */
    public function index()
    {
        $mejas = Meja::all();

        $reservasis = Reservasi::where('tanggal', today())
            ->with('pesanan.detailPesanan.menu')
            ->get();
        
        $reservasiMap = $reservasis->keyBy('nomorMeja');

        $stats = [
            'reservasiHariIni' => $reservasis->count(),
            'sudahCheckIn' => $reservasis->where('statusReservasi', 'Check-in')->count(),
            'mejaTerisi' => $mejas->where('statusMeja', 'Terisi')->count(),
            'pendapatanHariIni' => Pesanan::whereHas('reservasi', function ($query) {
                $query->where('tanggal', today())->where('statusReservasi', 'Selesai');
            })->sum('totalTagihan'),
        ];
        
        $recentActivity = [
            ['type' => 'reservation', 'message' => 'Reservasi baru dari Aqmal Raditya', 'time' => '2 menit lalu'],
            ['type' => 'checkin', 'message' => 'Meja 3 Check-in', 'time' => '5 menit lalu'],
            ['type' => 'payment', 'message' => 'Pembayaran Lunas Meja 7', 'time' => '8 menit lalu'],
        ];

        return view('dashboard.index', compact('mejas', 'reservasiMap', 'stats', 'recentActivity'));
    }

    /**
     * Menampilkan halaman detail/kelola untuk satu reservasi.
     */
    public function show(Reservasi $reservasi)
    {
        $menus = Menu::all();
        return view('admin.show', compact('reservasi', 'menus'));
    }

    /**
     * Memproses aksi check-in.
     */
    public function checkin(Reservasi $reservasi)
    {
        $reservasi->statusReservasi = 'Check-in';
        $reservasi->save();

        $reservasi->meja->statusMeja = 'Terisi';
        $reservasi->meja->save();

        return redirect()->route('admin.dashboard')->with('success', 'Reservasi ' . $reservasi->kodeReservasi . ' berhasil di Check-in.');
    }

    /**
     * Memproses aksi pembatalan reservasi.
     */
    public function cancel(Reservasi $reservasi)
    {
        if ($reservasi->statusReservasi != 'Dipesan') {
            return redirect()->route('admin.dashboard')->with('error', 'Reservasi ini tidak dapat dibatalkan.');
        }

        $reservasi->statusReservasi = 'Dibatalkan';
        $reservasi->save();

        $reservasi->pesanan->statusPembayaran = 'Dibatalkan';
        $reservasi->pesanan->save();

        $reservasi->meja->statusMeja = 'Tersedia';
        $reservasi->meja->save();

        return redirect()->route('admin.dashboard')->with('success', 'Reservasi ' . $reservasi->kodeReservasi . ' berhasil dibatalkan.');
    }

    /**
     * Memproses aksi selesaikan reservasi (checkout).
     */
    public function complete(Reservasi $reservasi)
    {
        if ($reservasi->statusReservasi != 'Check-in') {
            return redirect()->back()->with('error', 'Reservasi ini belum check-in.');
        }

        $reservasi->statusReservasi = 'Selesai';
        $reservasi->save();

        $reservasi->pesanan->statusPembayaran = 'Lunas';
        $reservasi->pesanan->save();

        $reservasi->meja->statusMeja = 'Tersedia';
        $reservasi->meja->save();

        return redirect()->route('admin.dashboard')->with('success', 'Reservasi ' . $reservasi->kodeReservasi . ' telah diselesaikan.');
    }

    /**
     * Menambahkan item menu baru ke pesanan yang ada.
     */
    public function addOrder(Request $request, Reservasi $reservasi)
    {
        $request->validate(['menu_items' => 'required|array']);

        DB::beginTransaction();
        try {
            $pesanan = $reservasi->pesanan;
            $totalTambahan = 0;

            foreach ($request->menu_items as $menu_id => $jumlah) {
                if ($jumlah > 0) {
                    $menu = Menu::find($menu_id);
                    $subTotal = $menu->harga * $jumlah;
                    DetailPesanan::create([
                        'pesanan_id' => $pesanan->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $jumlah,
                        'subTotal' => $subTotal,
                    ]);
                    $totalTambahan += $subTotal;
                }
            }
            
            if ($totalTambahan > 0) {
                $pesanan->totalTagihan += $totalTambahan;
                $pesanan->sisaTagihan += $totalTambahan;
                $pesanan->save();
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Pesanan berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambah pesanan.');
        }
    }
    public function listReservations(Request $request)
{
    $query = Reservasi::with('meja')->orderBy('tanggal', 'desc')->orderBy('waktu', 'desc');

    // Logic untuk filter pencarian
    if ($request->has('search')) {
        $query->where('namaPelanggan', 'like', '%' . $request->search . '%')
              ->orWhere('kodeReservasi', 'like', '%' . $request->search . '%');
    }

    $reservasis = $query->paginate(15); // Tampilkan 15 data per halaman

    return view('admin.reservasi-list', compact('reservasis'));
}
}