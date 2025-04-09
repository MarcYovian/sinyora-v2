@props(['showOnDesktop' => true])

<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700"
    :class="{
        '-translate-x-full': !open,
        'translate-x-0': open,
        'sm:translate-x-0': {{ $showOnDesktop ? 'true' : 'false' }}
    }"
    aria-label="Sidebar"
    x-data="{
        open: false,
        isMobile: window.innerWidth < 640,
        init() {
            this.$watch('isMobile', (value) => {
                if (!value && {{ $showOnDesktop ? 'true' : 'false' }}) {
                    this.open = true;
                } else {
                    this.open = false;
                }
            });

            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 640;
            });

            // Set initial state based on screen size
            this.open = !this.isMobile && {{ $showOnDesktop ? 'true' : 'false' }};
        }
    }"
    @toggle-sidebar.window="open = !open"
    x-show="true">

    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
        <ul class="list-none">
            {{ $slot }}
        </ul>
    </div>
</aside>
