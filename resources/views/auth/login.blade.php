@extends('layouts.app')

@section('title', 'Masuk - EduChat')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Masuk ke EduChat</h1>
        <p class="text-sm text-slate-500 mb-6">Login untuk menyimpan riwayat chat dan dokumen.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <label class="inline-flex items-center text-sm text-slate-600">
                <input type="checkbox" name="remember" class="mr-2">
                Ingat saya
            </label>
            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition">Masuk</button>
        </form>

        <p class="mt-5 text-sm text-slate-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar</a>
        </p>
    </div>
</div>
@endsection
