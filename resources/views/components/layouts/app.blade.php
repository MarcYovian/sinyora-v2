<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <x-seo-meta-tags />
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @livewireStyles

    <style>
        /* ===== ANIMASI ===== */
        @keyframes focusIn {
            0% {
                filter: blur(12px);
                opacity: 0;
                transform: scale(1.1);
            }

            100% {
                filter: blur(0);
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes drawLine {
            from {
                width: 0%;
            }

            to {
                width: 100%;
            }
        }

        @keyframes fadeOutUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-50px);
            }
        }

        /* Loader */
        #global-loader {
            transition: opacity 0.6s ease-in-out, background-color 0.6s ease-in-out;
            background-color: white;
        }

        /* Fade background saat keluar */
        #global-loader.is-leaving {
            opacity: 0;
            background-color: transparent;
        }

        /* Animasi huruf */
        .splash-char {
            opacity: 0;
            animation: focusIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        .splash-char:nth-child(1) {
            animation-delay: 0.1s;
        }

        .splash-char:nth-child(2) {
            animation-delay: 0.2s;
        }

        .splash-char:nth-child(3) {
            animation-delay: 0.3s;
        }

        .splash-char:nth-child(4) {
            animation-delay: 0.4s;
        }

        .splash-char:nth-child(5) {
            animation-delay: 0.5s;
        }

        .splash-char:nth-child(6) {
            animation-delay: 0.6s;
        }

        .splash-char:nth-child(7) {
            animation-delay: 0.7s;
        }

        /* Garis bawah */
        .splash-underline {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            background-color: #FFD24C;
            width: 0;
            animation: drawLine 0.7s ease-out forwards;
            animation-delay: 1s;
        }

        /* Outro teks */
        #global-loader.is-leaving .splash-text-container {
            animation: fadeOutUp 0.5s ease-in forwards;
        }
    </style>
</head>

<body class="font-sans antialiased">

    <!-- Loader -->
    <div id="global-loader"
        class="fixed top-0 left-0 w-full h-full flex flex-col gap-6 z-[9990] items-center justify-center">
        <div class="splash-text-container relative">
            <h1 class="splash-text text-[#FFD24C] text-5xl md:text-7xl font-bold tracking-wider">
                <span class="splash-char">S</span>
                <span class="splash-char">I</span>
                <span class="splash-char">N</span>
                <span class="splash-char">Y</span>
                <span class="splash-char">O</span>
                <span class="splash-char">R</span>
                <span class="splash-char">A</span>
            </h1>
            <div class="splash-underline"></div>
        </div>
    </div>

    <!-- Content -->
    <div class="min-h-screen bg-gray-100">
        <livewire:layout.navigation-guest />

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main>
            {{ $slot }}
        </main>

        <livewire:layout.footer-guest />
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    @stack('scripts')
    @livewireScripts

    <script>
        function hideLoader() {
            const loader = document.getElementById("global-loader");
            if (loader && !loader.classList.contains("is-leaving")) {
                loader.classList.add("is-leaving");
                setTimeout(() => {
                    loader.style.display = "none";
                }, 600); // lebih lama biar smooth
            }
        }

        function resetLoaderAnimation(loader) {
            // Reset huruf
            loader.querySelectorAll(".splash-char").forEach((char) => {
                char.style.animation = "none";
                char.offsetHeight; // reflow
                char.style.animation = "";
            });

            // Reset garis bawah
            const underline = loader.querySelector(".splash-underline");
            if (underline) {
                underline.style.animation = "none";
                underline.offsetHeight; // reflow
                underline.style.animation = "";
            }
        }

        // Saat mulai navigasi (SPA)
        document.addEventListener("livewire:navigating", () => {
            const loader = document.getElementById("global-loader");
            if (loader) {
                loader.classList.remove("is-leaving");
                loader.style.display = "flex";
                loader.style.opacity = "1";
                loader.style.backgroundColor = "white";
                resetLoaderAnimation(loader);
            }
        });

        // Saat selesai navigasi
        document.addEventListener("livewire:navigated", () => {
            hideLoader();
        });

        // Saat load pertama
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(hideLoader, 1800);
        });
    </script>
</body>

</html>
