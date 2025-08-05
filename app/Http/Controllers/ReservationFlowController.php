<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationFlowController extends Controller
{
    public function startFlow(Request $request)
    {
        $validated = $request->validate([
            'tanggal'    => 'required|date|after_or_equal:today',
            'waktu'      => 'required',
            'jumlahTamu' => 'required|integer|min:1',
        ]);
        $request->session()->put('reservation_data', $validated);
        return redirect()->route('reservasi.flow.meja.show');
    }

    public function showStepMeja(Request $request)
    {
        $reservationData = $request->session()->get('reservation_data');
        if (!$reservationData) return redirect()->route('home');
        
        $mejas = Meja::all();
        return view('reservasi.pilih-meja', compact('reservationData', 'mejas'));
    }

    public function storeStepMeja(Request $request)
    {
        $validated                     = $request->validate(['nomorMeja' => 'required|exists:meja,nomorMeja']);
        $reservationData               = $request->session()->get('reservation_data');
        $reservationData['nomorMeja']  = $validated['nomorMeja'];
        $request->session()->put('reservation_data', $reservationData);
        return redirect()->route('reservasi.flow.menu.show');
    }

    public function showStepMenu(Request $request)
    {
        $reservationData = $request->session()->get('reservation_data');
        if (!$reservationData || !isset($reservationData['nomorMeja'])) return redirect()->route('home');
        
        $menus = Menu::orderBy('kategori')->get()->groupBy('kategori');
        return view('reservasi.pilih-menu', compact('reservationData', 'menus'));
    }

    public function storeStepMenu(Request $request)
    {
        $validated   = $request->validate(['menu_items' => 'present|array']);
        $menuDipesan = [];
        if (isset($validated['menu_items'])) {
            foreach ($validated['menu_items'] as $menu_id => $item) {
                if (!empty($item['jumlah']) && $item['jumlah'] > 0) {
                    $menuDipesan[$menu_id] = $item;
                }
            }
        }

        if (empty($menuDipesan)) {
            return back()->withInput()->withErrors(['menu_items' => 'Pilih minimal satu menu untuk melanjutkan.']);
        }

        $reservationData                 = $request->session()->get('reservation_data');
        $reservationData['menu_items'] = $menuDipesan;
        $request->session()->put('reservation_data', $reservationData);
        return redirect()->route('reservasi.flow.datadiri.show');
    }

    public function showStepDataDiri(Request $request)
    {
        $reservationData = $request->session()->get('reservation_data');
        if (!$reservationData || !isset($reservationData['menu_items'])) return redirect()->route('home');
        
        $menuIds       = array_keys($reservationData['menu_items']);
        $selectedMenus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');
        return view('reservasi.data-diri', compact('reservationData', 'selectedMenus'));
    }

    public function storeStepDataDiri(Request $request)
    {
        $validated = $request->validate([
            'namaPelanggan' => 'required|string|max:255',
            'noTelepon'     => 'required|string|max:15',
            'email'         => 'required|email',
        ]);
        $reservationData = $request->session()->get('reservation_data');

        DB::beginTransaction();
        try {
            $pesanan                   = new Pesanan();
            $pesanan->statusPembayaran = 'Belum Bayar DP';
            $pesanan->save();
            $totalTagihan              = 0;
            $menuIds                   = array_keys($reservationData['menu_items']);
            $selectedMenus             = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

            foreach ($reservationData['menu_items'] as $menu_id => $item) {
                $menu     = $selectedMenus[$menu_id];
                $subTotal = $menu->harga * $item['jumlah'];
                DetailPesanan::create(['pesanan_id' => $pesanan->id, 'menu_id' => $menu->id, 'jumlah' => $item['jumlah'], 'subTotal' => $subTotal]);
                $totalTagihan += $subTotal;
            }

            $totalDP               = $totalTagihan * 0.50;
            $pesanan->totalTagihan = $totalTagihan;
            $pesanan->totalDP      = $totalDP;
            $pesanan->sisaTagihan  = $totalTagihan - $totalDP;
            $pesanan->save();

            $reservasi                = new Reservasi();
            $reservasi->kodeReservasi = 'BC-' . strtoupper(Str::random(8));
            $reservasi->namaPelanggan = $validated['namaPelanggan'];
            $reservasi->noTelepon     = $validated['noTelepon'];
            $reservasi->email         = $validated['email'];
            $reservasi->jumlahTamu    = $reservationData['jumlahTamu'];
            $reservasi->tanggal       = $reservationData['tanggal'];
            $reservasi->waktu         = $reservationData['waktu'];
            $reservasi->nomorMeja     = $reservationData['nomorMeja'];
            $reservasi->pesanan_id    = $pesanan->id;
            $reservasi->save();
            $request->session()->put('final_reservation_code', $reservasi->kodeReservasi);
            
            $meja             = Meja::find($reservationData['nomorMeja']);
            $meja->statusMeja = 'Dipesan';
            $meja->save();

            DB::commit();
            return redirect()->route('reservasi.flow.pembayaran.show');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    // Menampilkan halaman pembayaran (Step 4)
    public function showStepPembayaran(Request $request)
    {
        $kodeReservasi = $request->session()->get('final_reservation_code');
        if (!$kodeReservasi) return redirect()->route('home');
        
        $reservasi = Reservasi::where('kodeReservasi', $kodeReservasi)->firstOrFail();
        return view('reservasi.pembayaran', compact('reservasi'));
    }

    // Menyimpan status pembayaran (Step 4)
    public function storeStepPembayaran(Request $request)
    {
        $kodeReservasi = $request->session()->get('final_reservation_code');
        $reservasi = Reservasi::where('kodeReservasi', $kodeReservasi)->firstOrFail();
        
        // Simulasi pembayaran berhasil
        $reservasi->pesanan->statusPembayaran = 'Sudah Bayar DP';
        $reservasi->pesanan->save();

        // Hapus data dari session setelah selesai
        $request->session()->forget('reservation_data');

        return redirect()->route('reservasi.flow.sukses');
    }

    // Menampilkan halaman sukses
    public function success(Request $request)
    {
        $kodeReservasi = $request->session()->get('final_reservation_code');
        if (!$kodeReservasi) return redirect()->route('home');

        $reservasi = Reservasi::where('kodeReservasi', $kodeReservasi)->firstOrFail();
        $request->session()->forget('final_reservation_code'); // Hapus session terakhir

        return view('reservasi.sukses', compact('reservasi'));
    }
    public function showStruk(Reservasi $reservasi)
    {
        return view('reservasi.struk', compact('reservasi'));
    }
}