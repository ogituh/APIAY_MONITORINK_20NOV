<?php

namespace App\Http\Controllers;

use App\Models\OtpVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('Auth.login', [
            'captcha' => captcha_img('default')
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'captcha'  => 'required|captcha'
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();

            $user = Auth::user();

            $hp = $user->phone;
            if (!$hp) {
                return back()->withErrors([
                    'login' => 'Nomor HP belum terdaftar.'
                ]);
            };

            //    $otp = rand(100000, 999999);
            $otp = 123456;

            OtpVerify::create([
                'bpid' => $user->bpid,
                'otp' => $otp,
                'hp' => $hp,
                'expired_date' => now()->addMinutes(5)
            ]);

            session(['otp' => $otp, 'otp_hp' => $hp]);


            return redirect()->route('formotp');
        }

        return back()->withErrors([
            'login' => 'Username atau password salah.'
        ])->withInput($request->only('username'));
    }

    public function verifyOtp(Request $request)
    {
        $user = Auth::user();
        // dd($user->bpid);

        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Sesi login tidak ditemukan.']);
        }

        $otpInput = implode('', $request->otp);

        $otpRecord = OtpVerify::where('bpid', $user->bpid)
            ->where('otp', $otpInput)
            // ->where('expired_date', '>', now())
            // ->latest()
            ->first();


        session(['otp' => $otpInput]);


        if ($otpRecord) {
            return redirect()->route('order-view');
        }

        return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus semua session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.view')->with('success', 'Anda berhasil logout.');
    }
}
