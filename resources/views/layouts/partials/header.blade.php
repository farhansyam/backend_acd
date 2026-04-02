<div class="navbar-header border-b border-neutral-200 dark:border-neutral-600">
    <div class="flex items-center justify-between">

        {{-- Kiri: toggle sidebar + search --}}
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-[16px]">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon active"></iconify-icon>
                </button>
               
                
            </div>
        </div>

        {{-- Kanan: dark mode, notif, profil --}}
        <div class="col-auto">
            <div class="flex flex-wrap items-center gap-3">

                {{-- Dark mode toggle --}}
                <button type="button" id="theme-toggle"
                    class="w-10 h-10 bg-neutral-200 dark:bg-neutral-700 dark:text-white rounded-full flex justify-center items-center">
                    <span id="theme-toggle-dark-icon" class="hidden"><i class="ri-sun-line"></i></span>
                    <span id="theme-toggle-light-icon" class="hidden"><i class="ri-moon-line"></i></span>
                </button>

                {{-- Notifikasi --}}
                <button data-dropdown-toggle="dropdownNotification"
                    class="has-indicator w-10 h-10 bg-neutral-200 dark:bg-neutral-700 rounded-full flex justify-center items-center"
                    type="button">
                    <iconify-icon icon="iconoir:bell" class="text-neutral-900 dark:text-white text-xl"></iconify-icon>
                </button>
                <div id="dropdownNotification"
                    class="z-10 hidden bg-white dark:bg-neutral-700 rounded-2xl overflow-hidden shadow-lg max-w-[394px] w-full">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 dark:bg-primary-600/25 m-4 flex items-center justify-between gap-2">
                        <h6 class="text-lg text-neutral-900 font-semibold mb-0">Notifikasi</h6>
                        <span class="w-10 h-10 bg-white dark:bg-neutral-600 text-primary-600 dark:text-white font-bold flex justify-center items-center rounded-full">
                            0
                        </span>
                    </div>
                    <div class="text-center py-6 text-neutral-400 text-sm">Tidak ada notifikasi baru</div>
                </div>

                {{-- Profil --}}
                <button data-dropdown-toggle="dropdownProfile"
                    class="flex justify-center items-center rounded-full" type="button">
                    <img src="{{ asset('assets/images/user.png') }}" alt="profile"
                        class="w-10 h-10 object-fit-cover rounded-full">
                </button>
                <div id="dropdownProfile"
                    class="z-10 hidden bg-white dark:bg-neutral-700 rounded-lg shadow-lg dropdown-menu-sm p-3">
                    <div class="py-3 px-4 rounded-lg bg-primary-50 dark:bg-primary-600/25 mb-4 flex items-center justify-between gap-2">
                        <div>
                            <h6 class="text-lg text-neutral-900 font-semibold mb-0">{{ auth()->user()->name ?? 'Admin' }}</h6>
                            <span class="text-neutral-500">{{ auth()->user()->role ?? '' }}</span>
                        </div>
                        <button type="button" class="hover:text-danger-600">
                            <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                        </button>
                    </div>
                    <ul class="flex flex-col">
                        <li>
                            <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4"
                                href="/profile">
                                <iconify-icon icon="solar:user-linear" class="icon text-xl"></iconify-icon>
                                Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="text-black px-0 py-2 hover:text-primary-600 flex items-center gap-4"
                                href="/settings">
                                <iconify-icon icon="icon-park-outline:setting-two" class="icon text-xl"></iconify-icon>
                                Pengaturan
                            </a>
                        </li>
                        <li>
                            <form action="/logout" method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-black w-full text-left px-0 py-2 hover:text-danger-600 flex items-center gap-4">
                                    <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>