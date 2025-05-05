@push('styles')
    <style>
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
@endpush

<div class="font-sans antialiased text-gray-900">
    <!-- Hero Section -->
    <section id="beranda"
        class="relative bg-cover bg-center bg-no-repeat min-h-screen flex items-center justify-center px-6 py-20 md:py-32"
        style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ asset('images/1.jpg') }}');">
        <div class="container mx-auto text-center text-white max-w-4xl px-4">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Selamat Datang di Kapel St. Yohanes Rasul
            </h1>
            <p class="text-lg sm:text-xl md:text-2xl mb-10 opacity-90">
                Di bawah naungan Paroki Santo Yusup Karangpilang, Surabaya
            </p>
            <a href="#jadwal-misa"
                class="inline-block px-8 py-3 bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-semibold rounded-full shadow-lg transform transition hover:scale-105 duration-300 text-lg">
                Lihat Jadwal Misa
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline ml-2" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Jadwal Misa -->
    <section id="jadwal-misa" class="bg-[#282834] py-16">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-[#FFD24C] mb-4">
                    Jadwal Misa <span class="text-white">Mingguan</span>
                </h2>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 max-w-md mx-auto">
                    <div class="flex items-center justify-center space-x-4 mb-6">
                        <div class="bg-[#FFD24C] p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#825700]" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-semibold text-white">Minggu Sore: 17.00 WIB</p>
                    </div>
                    <p class="text-[#FFD24C] text-sm md:text-base">
                        *Jadwal bisa berubah saat hari raya tertentu. Silakan pantau pengumuman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Welcome --}}
    <section id="welcome" class="container mx-auto px-6 py-20 max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="relative">
                <!-- Teks besar di belakang -->
                <h1
                    class="absolute -top-8 -left-4 text-gray-200 text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
                    WELCOME
                </h1>

                <!-- Teks kecil di depan -->
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">
                        Kapel St. Yohanes Rasul
                    </h2>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        Selamat datang di situs resmi Kapel St. Yohanes Rasul. Kami berharap
                        informasi di sini dapat membantu umat semakin dekat dengan Kristus. Salam damai dalam Kristus.
                        Semoga Kapel St. Yohanes Rasul menjadi tempat yang membawa berkat dan sukacita bagi umat semua.
                    </p>
                    <div class="mt-8">
                        <a href="#pelayanan"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-[#825700] hover:bg-[#6b4900] transition-colors duration-300">
                            Pelayanan Kami
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div
                class="relative rounded-xl overflow-hidden shadow-2xl transform transition hover:scale-[1.02] duration-500">
                <img src="{{ asset('images/about.jpg') }}" alt="Kapel St. Yohanes Rasul"
                    class="w-full h-auto object-cover aspect-video">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            </div>
        </div>
    </section>

    {{-- Event Kami --}}
    <section id="event" class="bg-gray-50 py-20">
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="relative mb-16">
                <!-- Teks besar di belakang -->
                <h1
                    class="absolute -top-16 left-0 text-gray-200 text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
                    JELAJAHI
                </h1>

                <!-- Teks kecil di depan -->
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                        Event Kami
                    </h2>
                    <p class="text-gray-600 text-lg max-w-2xl">
                        Lihat dan jelajahi event-event kami pada saat hari besar perayaan
                    </p>
                </div>
            </div>

            <livewire:pages.home.event-section lazy />
        </div>
    </section>

    {{-- Article --}}
    <section id="article" class="container mx-auto px-6 py-20 max-w-6xl">
        <div class="relative mb-16">
            <!-- Teks besar di belakang -->
            <h1
                class="absolute -top-16 left-0 text-gray-200 text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
                SINYORA
            </h1>

            <!-- Teks kecil di depan -->
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Funfact & Artikel
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl">
                    Baca seputar event, berita dan funfact kami lainnya!
                </p>
            </div>
        </div>

        <livewire:pages.home.article-section lazy />
    </section>

    {{-- Pelayanan --}}
    <section id="pelayanan" class="bg-gray-50 py-20">
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="relative mb-16">
                <!-- Teks besar di belakang -->
                <h1
                    class="absolute -top-16 left-0 text-gray-200 text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-20 z-0">
                    SERVICES
                </h1>

                <!-- Teks kecil di depan -->
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                        Pelayanan Liturgis Kapela
                    </h2>
                    <p class="text-gray-600 text-lg max-w-2xl">
                        Jelajahi dan Temukan Pelayanan Liturgis Kapela St Yohanes Rasul
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Service Card 1 -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-md text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="bg-[#FFD24C]/10 p-4 rounded-full inline-flex items-center justify-center mb-4">
                            <i class="fa-solid fa-book-bible text-3xl text-[#825700]"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Lektor Pemazmur</h3>
                        <p class="text-gray-600 text-sm">
                            Melayani dalam pembacaan Kitab Suci dan Mazmur
                        </p>
                        <a href="#" class="mt-4 inline-block text-sm text-[#825700] font-medium hover:underline">
                            Selengkapnya →
                        </a>
                    </div>

                    <!-- Service Card 2 -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-md text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="bg-[#FFD24C]/10 p-4 rounded-full inline-flex items-center justify-center mb-4">
                            <i class="fa-solid fa-cross text-3xl text-[#825700]"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Misdinar</h3>
                        <p class="text-gray-600 text-sm">
                            Melayani dalam perayaan Ekaristi
                        </p>
                        <a href="#" class="mt-4 inline-block text-sm text-[#825700] font-medium hover:underline">
                            Selengkapnya →
                        </a>
                    </div>

                    <!-- Service Card 3 -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-md text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="bg-[#FFD24C]/10 p-4 rounded-full inline-flex items-center justify-center mb-4">
                            <i class="fa-solid fa-users text-3xl text-[#825700]"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">PUK</h3>
                        <p class="text-gray-600 text-sm">
                            Pelayanan Umum dan Kebersihan
                        </p>
                        <a href="#"
                            class="mt-4 inline-block text-sm text-[#825700] font-medium hover:underline">
                            Selengkapnya →
                        </a>
                    </div>

                    <!-- Service Card 4 -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-md text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <div class="bg-[#FFD24C]/10 p-4 rounded-full inline-flex items-center justify-center mb-4">
                            <i class="fa-solid fa-hands-praying text-3xl text-[#825700]"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Paguyuban Organis</h3>
                        <p class="text-gray-600 text-sm">
                            Memimpin nyanyian dalam perayaan liturgi
                        </p>
                        <a href="#"
                            class="mt-4 inline-block text-sm text-[#825700] font-medium hover:underline">
                            Selengkapnya →
                        </a>
                    </div>
                </div>

                <div class="relative rounded-xl overflow-hidden shadow-2xl h-full">
                    <img src="{{ asset('images/about.jpg') }}" alt="Pelayanan Liturgi"
                        class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-8">
                        <div class="text-white">
                            <h3 class="text-2xl font-bold mb-2">Bergabunglah Dengan Kami</h3>
                            <p class="mb-4">
                                Mari berpartisipasi dalam pelayanan liturgi Kapel St. Yohanes Rasul
                            </p>
                            <a href="#contact"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-[#825700] bg-[#FFD24C] hover:bg-[#FEC006]">
                                Hubungi Kami
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact" class="bg-[#282834] py-20">
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="relative mb-16 text-center">
                <!-- Teks besar di belakang -->
                <h1
                    class="absolute -top-16 left-1/2 transform -translate-x-1/2 text-[#FFD24C] text-7xl sm:text-8xl md:text-9xl font-bold tracking-wide opacity-10 z-0">
                    CONTACT
                </h1>

                <!-- Teks kecil di depan -->
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-[#FFD24C] mb-4">
                        Hubungi Kami
                    </h2>
                    <p class="text-white text-lg max-w-2xl mx-auto">
                        Kapela Santo Yohanes Rasul Terletak di Jl. Taman Pondok Jati A04-A04a, Geluran, Kec. Taman, Jawa
                        Timur
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Google Maps -->
                <div class="rounded-xl overflow-hidden shadow-2xl h-96">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15827.556838026932!2d112.695676!3d-7.3663139!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e30029993af5%3A0xb0767f7c7446cba1!2sKapel%20Santo%20Yohanes%20Rasul!5e0!3m2!1sid!2sid!4v1732779963574!5m2!1sid!2sid"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        class="w-full h-full border-0"></iframe>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-xl shadow-xl p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Kirim Pesan</h3>
                    <form wire:submit="send" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                Lengkap</label>
                            <input type="text" id="name" wire:model="contactForm.name"
                                placeholder="Nama Anda"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFD24C] focus:border-transparent transition">
                            @if ($errors->has('contactForm.name'))
                                <span class="text-red-500 text-sm mt-1">{{ $errors->first('contactForm.name') }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" wire:model="contactForm.email"
                                    placeholder="email@contoh.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFD24C] focus:border-transparent transition">
                                @if ($errors->has('contactForm.name'))
                                    <span
                                        class="text-red-500 text-sm mt-1">{{ $errors->first('contactForm.name') }}</span>
                                @endif
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor
                                    Telepon</label>
                                <input type="tel" id="phone" wire:model="contactForm.phone"
                                    placeholder="0812-3456-7890"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFD24C] focus:border-transparent transition">
                                @if ($errors->has('contactForm.name'))
                                    <span
                                        class="text-red-500 text-sm mt-1">{{ $errors->first('contactForm.name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                            <textarea id="message" rows="4" wire:model="contactForm.message" placeholder="Tulis pesan Anda..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FFD24C] focus:border-transparent transition"></textarea>
                            @if ($errors->has('contactForm.name'))
                                <span class="text-red-500 text-sm mt-1">{{ $errors->first('contactForm.name') }}</span>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full bg-[#FFD24C] hover:bg-[#FEC006] text-[#825700] font-semibold py-3 px-4 rounded-lg shadow-md transition-colors duration-300">
                            Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
