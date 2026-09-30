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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
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

    public function showUserSecurity(): View
    {
        return view('user-panel.security', ['user' => Auth::user()]);
    }

    public function showAdminSecurity(): View
    {
        return view('admin-panel.security', ['user' => Auth::user()]);
    }

    public function updateUserPassword(Request $request): RedirectResponse
    {
        return $this->updatePassword($request, 'user.security');
    }

    public function updateAdminPassword(Request $request): RedirectResponse
    {
        return $this->updatePassword($request, 'admin.security');
    }

    private function updatePassword(Request $request, string $routeName): RedirectResponse
    {
        $user = $request->user();
        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('password');
        $confirmation = $request->input('password_confirmation');

        if (! is_string($currentPassword) || trim($currentPassword) === '') {
            return redirect()->route($routeName)->with('toast_error', 'Please enter your current password.');
        }

        if (! Hash::check($currentPassword, $user->password)) {
            return redirect()->route($routeName)->with('toast_error', 'Current password is incorrect.');
        }

        if (! is_string($newPassword) || trim($newPassword) === '') {
            return redirect()->route($routeName)->with('toast_error', 'Please enter a new password.');
        }

        if (! is_string($confirmation) || trim($confirmation) === '') {
            return redirect()->route($routeName)->with('toast_error', 'Please confirm your new password.');
        }

        if ($newPassword !== $confirmation) {
            return redirect()->route($routeName)->with('toast_error', 'New password and confirmation do not match.');
        }

        if ($newPassword === $currentPassword) {
            return redirect()->route($routeName)->with('toast_error', 'New password must be different from the current password.');
        }

        $passwordRule = Password::min(12)->mixedCase()->numbers()->symbols();
        $validator = \Validator::make($request->all(), [
            'password' => ['required', 'string', $passwordRule],
        ]);

        if ($validator->fails()) {
            return redirect()->route($routeName)->with('toast_error', 'Password must be at least 12 characters and include uppercase, lowercase, a number, and a symbol.');
        }

        $user->forceFill(['password' => $newPassword])->save();
        $request->session()->regenerate();

        return redirect()->route($routeName)->with('toast_success', 'Password updated successfully.');
    }

    private function invalidCredentials(): RedirectResponse
    {
        return back()->withErrors([
            'login' => 'The provided credentials do not match our records or the account is inactive.',
        ])->onlyInput('login');
    }
}
