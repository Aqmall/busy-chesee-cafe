<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffLoginController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses login
    public function login(Request $request)
    {
        // Mock authentication (sesuai kode React)
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (($credentials['username'] === 'kasir' || $credentials['username'] === 'manager') && $credentials['password'] === 'password') {
            // Untuk simulasi, kita bisa simpan peran di session
            $request->session()->put('user_role', $credentials['username']);
            $request->session()->regenerate();
            
            // Perbaikan: Arahkan ke route 'admin.dashboard' yang benar
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}