@extends('layouts.app')
@section('content')
<div class="flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <input type="email" name="email" placeholder="Email"
                class="w-full border rounded p-3 mb-4" required>
            <input type="password" name="password" placeholder="Password"
                class="w-full border rounded p-3 mb-4" required>
            <button type="submit"
                class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">
                Login
            </button>
        </form>
        <p class="text-center mt-4 text-sm">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Daftar</a>
        </p>
    </div>
</div>
@endsection