<?php
namespace App\Http\Controllers;

use App\Events\UserPresenceUpdated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }
    public function showLogin()    { return view('auth.login'); }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        Auth::login($user);
        return redirect()->route('chat.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Set user online
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->update(['is_online' => true, 'last_seen' => now()]);
            broadcast(new UserPresenceUpdated($user, 'online'));

            return redirect()->route('chat.index');
        }

        return back()->withErrors(['email' => 'Kredensial tidak valid.']);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['is_online' => false, 'last_seen' => now()]);
        broadcast(new UserPresenceUpdated($user, 'offline'));

        Auth::logout();
        $request->session()->invalidate();
        return redirect()->route('login');
    }
}