@extends('layouts.app')

@section('title', 'EduChat - Belajar Lebih Mudah dengan AI')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
    <!-- Navbar -->
    <nav class="border-b bg-white/80 backdrop-blur-sm sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span class="font-bold text-2xl text-gray-800">EduChat</span>
            </div>
            <div class="space-x-4">
                @auth
                    <a href="{{ route('chat') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-md hover:shadow-lg">
                        Mulai Chat
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-6 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition font-medium">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition font-medium">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow-md hover:shadow-lg">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container mx-auto px-4 py-20 text-center">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent leading-tight">
                Belajar Lebih Mudah dengan AI
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 mb-8 leading-relaxed">
                Upload slide kuliah, chat dengan AI, dan pahami materi dengan lebih cepat. 
                <span class="font-semibold text-gray-800">Gratis dan mudah digunakan.</span>
            </p>
            @auth
                <a href="{{ route('chat') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transition shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                    Coba Sekarang - Gratis!
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-blue-700 transition shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                    Sign In untuk Mulai
                </a>
            @endauth
        </div>
    </section>

    <!-- Features -->
    <section class="container mx-auto px-4 py-20">
        <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Fitur Unggulan</h2>
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition border border-gray-100">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Upload & Chat</h3>
                <p class="text-gray-600 leading-relaxed">
                    Upload slide PDF dan langsung mulai bertanya tentang materinya. AI akan menjawab berdasarkan konten dokumen.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition border border-gray-100">
                <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">Jawaban Cepat</h3>
                <p class="text-gray-600 leading-relaxed">
                    AI menjawab pertanyaan dalam hitungan detik dengan akurasi tinggi berdasarkan materi yang kamu upload.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-xl transition border border-gray-100">
                <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-3 text-gray-800">100% Gratis</h3>
                <p class="text-gray-600 leading-relaxed">
                    Tidak ada biaya tersembunyi. Gratis untuk semua mahasiswa yang ingin belajar lebih efektif.
                </p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="bg-white py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Cara Menggunakan</h2>
            <div class="max-w-4xl mx-auto">
                <div class="space-y-8">
                    <!-- Step 1 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold">
                            1
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800">Upload Slide Kuliah</h3>
                            <p class="text-gray-600">Klik tombol "Mulai Chat" dan upload file PDF slide materi kuliah kamu.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold">
                            2
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800">Tunggu Proses</h3>
                            <p class="text-gray-600">AI akan memproses dokumen kamu (biasanya 10-30 detik).</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold">
                            3
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-gray-800">Mulai Bertanya!</h3>
                            <p class="text-gray-600">Chat dengan AI tentang materi kuliah. Tanya apa saja!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="container mx-auto px-4 py-20 text-center">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-3xl p-12 max-w-4xl mx-auto shadow-2xl">
            <h2 class="text-4xl font-bold mb-4">
                Siap Tingkatkan Pembelajaran Kamu?
            </h2>
            <p class="text-xl mb-8 opacity-90">
                Bergabung dengan ribuan mahasiswa yang sudah menggunakan EduChat
            </p>
            @auth
                <a href="{{ route('chat') }}" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-xl text-lg font-bold hover:bg-gray-100 transition shadow-lg">
                    Mulai Sekarang ->
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-xl text-lg font-bold hover:bg-gray-100 transition shadow-lg">
                    Buat Akun ->
                </a>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-white py-8">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p class="text-lg">(c) 2024 EduChat. Dibuat untuk membantu mahasiswa belajar lebih baik.</p>
            <p class="mt-2 text-sm">Powered by Laravel + RAG + AI</p>
        </div>
    </footer>
</div>
@endsection
