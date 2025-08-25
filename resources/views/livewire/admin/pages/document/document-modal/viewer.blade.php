<div class="w-full h-full bg-slate-200 dark:bg-black rounded-r-lg overflow-hidden relative"
    style="background-image: linear-gradient(45deg, #e2e8f0 25%, transparent 25%), linear-gradient(-45deg, #e2e8f0 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e2e8f0 75%), linear-gradient(-45deg, transparent 75%, #e2e8f0 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
    @if (Str::startsWith($this->doc->mime_type, 'image/'))
        {{-- Container untuk image dengan scrolling yang proper --}}
        <div class="absolute inset-0 overflow-auto" x-data="{
            zoomed: false,
            toggleZoom() {
                this.zoomed = !this.zoomed;
                this.$refs.image.classList.toggle('max-w-full');
                this.$refs.image.classList.toggle('w-auto');
                this.$refs.image.classList.toggle('h-auto');
                this.$refs.image.classList.toggle('object-contain');
            }
        }">
            <div class="min-h-full min-w-full flex items-center justify-center p-4">
                <img x-ref="image" src="{{ Storage::url($this->doc->document_path) }}" alt="Pratinjau Dokumen"
                    @click="toggleZoom()" :class="zoomed ? 'cursor-zoom-out' : 'cursor-zoom-in'"
                    class="max-w-full h-auto object-contain rounded-lg shadow-2xl border border-gray-300 dark:border-gray-600 transition-all duration-300 ease-in-out select-none">
            </div>

            {{-- Zoom indicator --}}
            <div
                class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm font-medium">
                <span x-text="zoomed ? 'Klik untuk zoom out' : 'Klik untuk zoom in'"></span>
            </div>
        </div>
    @else
        {{-- Container untuk iframe --}}
        <div class="absolute inset-0">
            <iframe src="{{ Storage::url($this->doc->document_path) }}" width="100%" height="100%"
                class="border-0 rounded-lg shadow-2xl bg-white w-full h-full">
            </iframe>
        </div>
    @endif
</div>
