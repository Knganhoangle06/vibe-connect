<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'gender'   => 'required|in:male,female,other',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'gender'   => $request->gender,
        ]);

        return redirect()->route('login');
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user && $user->is_locked) {
            return back()->withErrors(['email' => 'Tài khoản của bạn đang bị khóa. Vui lòng liên hệ admin.']);
        }

        if (Auth::attempt($credentials)) {
            return redirect()->route('home');
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng']);
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
