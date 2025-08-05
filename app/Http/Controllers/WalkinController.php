<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;

class WalkinController extends Controller
{
    /**
     * Menampilkan form untuk membuat pesanan walk-in.
     */
    public function create()
    {
        $mejas = Meja::where('statusMeja', 'Tersedia')->get();
        $menus = Menu::all();
        return view('admin.walkin.create', compact('mejas', 'menus'));
    }

    /**
     * Menyimpan pesanan walk-in baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomorMeja' => 'required|exists:meja,nomorMeja',
            'namaPelanggan' => 'required|string|max:255',
            'jumlahTamu' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Karena ini walk-in tanpa reservasi, kita cukup buat pesanan
            // dan langsung update status meja.
            $pesanan = new Pesanan();
            // Asumsi walk-in akan langsung bayar di akhir (status Lunas akan diupdate saat checkout)
            $pesanan->statusPembayaran = 'Belum Bayar DP'; 
            $pesanan->totalTagihan = 0; // Total dihitung saat checkout
            $pesanan->save();

            // Update status meja menjadi 'Terisi'
            $meja = Meja::find($request->nomorMeja);
            $meja->statusMeja = 'Terisi';
            $meja->save();
            
            // Di aplikasi nyata, Anda bisa membuat "reservasi" dummy untuk walk-in
            // agar bisa dikelola di dasbor, namun untuk saat ini kita sederhanakan.

            DB::commit();
            return redirect()->route('admin.dashboard')->with('success', 'Walk-in untuk meja ' . $meja->nomorMeja . ' berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat pesanan walk-in: ' . $e->getMessage());
        }
    }
}