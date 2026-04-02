<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if (in_array($user->role, ['customer', 'teknisi'])) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun ini hanya bisa diakses melalui aplikasi mobile.'
            ]);
        }

        return redirect('/dashboard');
    }
}
