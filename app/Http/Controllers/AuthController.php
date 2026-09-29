<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AdminDashboardStatistics;
use App\Support\IndianPhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user instanceof User && $user->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('frontend.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $login = $validated['login'];
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        if (! $isEmail && ! IndianPhoneNumber::isValid($login)) {
            return $this->invalidCredentials();
        }

        $credentials = [
            $isEmail ? 'email' : 'whatsapp_number' => $login,
            'password' => $validated['password'],
            'status' => User::STATUS_ACTIVE,
        ];

        if (! Auth::attempt($credentials)) {
            return $this->invalidCredentials();
        }

        $user = Auth::user();

        if (! $user instanceof User || ! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_USER], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->invalidCredentials();
        }

        $request->session()->regenerate();

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user instanceof User && $user->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('frontend.auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);
        AdminDashboardStatistics::forget();
        $user->forceFill([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function invalidCredentials(): RedirectResponse
    {
        return back()->withErrors([
            'login' => 'The provided credentials do not match our records or the account is inactive.',
        ])->onlyInput('login');
    }
}
