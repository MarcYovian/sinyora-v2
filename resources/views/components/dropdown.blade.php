@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };

    $width = match ($width) {
        '48' => 'w-48',
        default => $width,
    };
@endphp

{{--
    CATATAN PENGEMBANG:
    - x-data diubah menjadi sebuah fungsi untuk menampung logika yang lebih kompleks.
    - Menambahkan state baru: `placement` untuk melacak posisi ('top' atau 'bottom').
    - Fungsi `toggle()` sekarang menghitung posisi dropdown setiap kali dibuka.
    - Menggunakan $nextTick untuk memastikan elemen konten sudah dirender sebelum diukur.
    - Menggunakan $refs untuk mendapatkan elemen DOM trigger dan konten.
    - Class CSS untuk posisi (`top-full`, `bottom-full`) dan animasi (`origin-top`, `origin-bottom`) sekarang dinamis menggunakan :class.
--}}
<div class="relative" x-data="dropdown()" @click.outside="close()" @close.stop="close()">

    <div @click="toggle()" x-ref="trigger">
        {{ $trigger }}
    </div>

    <div x-show="open" x-ref="content" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}" style="display: none;"
        @click="close()"
        :class="{
            'top-full mt-2': placement === 'bottom',
            'bottom-full mb-2': placement === 'top',
            'origin-top': placement === 'bottom',
            'origin-bottom': placement === 'top'
        }">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

<script>
    function dropdown() {
        return {
            open: false,
            placement: 'bottom', // Default placement

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.determinePlacement();
                }
            },

            close() {
                this.open = false;
            },

            determinePlacement() {
                // $nextTick memastikan kita mengukur setelah DOM di-update
                this.$nextTick(() => {
                    const triggerRect = this.$refs.trigger.getBoundingClientRect();
                    const contentRect = this.$refs.content.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;

                    // Periksa apakah ada cukup ruang di bawah
                    const spaceBelow = viewportHeight - triggerRect.bottom;
                    if (spaceBelow < contentRect.height) {
                        // Jika tidak cukup ruang di bawah, periksa ruang di atas
                        const spaceAbove = triggerRect.top;
                        if (spaceAbove > contentRect.height) {
                            this.placement = 'top'; // Ganti ke atas
                            return;
                        }
                    }

                    // Jika cukup ruang di bawah atau tidak cukup ruang di atas, gunakan posisi default
                    this.placement = 'bottom';
                });
            }
        }
    }
</script>
