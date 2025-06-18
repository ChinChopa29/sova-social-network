<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['user' => auth()->user()]);
        }

        // Возврат валидационной ошибки для поля email
        return response()->json([
            'errors' => [
                'email' => ['Неверный email или пароль'],
            ],
        ], 422);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); 
        $request->session()->invalidate(); 
        $request->session()->regenerateToken(); 

        return response()->json(['message' => 'Logged out successfully']);
    }
}
