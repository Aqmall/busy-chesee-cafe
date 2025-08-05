<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        // Mengambil 4 menu acak untuk ditampilkan di section "Menu Favorit"
        $featuredMenus = Menu::inRandomOrder()->take(4)->get();
        
        // Daftar waktu yang tersedia untuk dropdown
        $timeSlots = ['10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'];
        
        return view('home', compact('featuredMenus', 'timeSlots'));
    }
}