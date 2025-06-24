<nav x-data="{ open: false, notificationsOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Ana Sayfa') }}
                    </x-nav-link>
                    {{-- Kullanıcı Öneri Linkleri --}}
                    <x-nav-link :href="route('suggestions.index')" :active="request()->routeIs('suggestions.index') || request()->routeIs('suggestions.show')">
                        {{ __('Geçmiş Önerilerim') }}
                    </x-nav-link>
                    <x-nav-link :href="route('suggestions.create')" :active="request()->routeIs('suggestions.create')">
                        {{ __('Yeni Öneri Ver') }}
                    </x-nav-link>

                    {{-- Yönetici Linkleri (Sadece 'admin' rolündeki kullanıcılar görür) --}}
                    @if(Auth::check() && Auth::user()->role == 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')">
                            {{ __('Yönetici Paneli') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.suggestions.index')" :active="request()->routeIs('admin.suggestions.*')">
                            {{ __('Tüm Öneriler') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')">
                            {{ __('Bölümler') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notifications Dropdown -->
                @auth
                    @php
                        $unreadNotifications = Auth::user()->unreadNotifications->take(5);
                        $hasUnreadNotifications = Auth::user()->unreadNotifications()->exists();
                        $allNotificationsCount = Auth::user()->notifications()->count();
                    @endphp
                    {{-- Alpine.js'in x-data'sını dropdown'a taşıdık ve notificationsOpen ekledik --}}
                    <div class="ms-3 relative" x-data="{ notificationsOpen: false }">
                        <x-dropdown align="right" width="96" x-show="notificationsOpen" @click.away="notificationsOpen = false">
                            <x-slot name="trigger">
                                {{-- Bildirim Butonu (Çan) --}}
                                <button id="notificationBellButton" @click="notificationsOpen = !notificationsOpen; if(notificationsOpen) { markNotificationsAsSeen(); }" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    {{-- Kırmızı Nokta --}}
                                    <span id="notificationRedDot" class="absolute -top-1 -right-1 block h-3 w-3 rounded-full ring-2 ring-white dark:ring-gray-800 bg-red-500 {{ !$hasUnreadNotifications ? 'hidden' : '' }}"></span>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-600 font-semibold text-sm text-gray-700 dark:text-gray-300">Bildirimler</div>
                                @forelse ($unreadNotifications as $notification)
                                    @php
                                        $notificationLink = $notification->data['link'] ?? '#';
                                        $notificationLink .= (strpos($notificationLink, '?') === false ? '?' : '&') . 'read=' . $notification->id;
                                    @endphp
                                    <x-dropdown-link href="{{ $notificationLink }}" class="whitespace-normal">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $notification->data['message'] }}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </x-dropdown-link>
                                @empty
                                    <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Yeni bildirim yok.</div>
                                @endforelse
                                @if($allNotificationsCount > 0)
                                    <div class="border-t border-gray-200 dark:border-gray-600"></div>
                                    {{-- İleride tüm bildirimleri listeleyecek bir sayfa oluşturursanız bu linki oraya yönlendirin --}}
                                    <x-dropdown-link href="#"> {{-- TODO: Tüm bildirimler sayfası için link --}}
                                        Tüm Bildirimleri Gör ({{ $allNotificationsCount }})
                                    </x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth

                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profil') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Çıkış Yap') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Ana Sayfa') }}
            </x-responsive-nav-link>
            {{-- Kullanıcı Öneri Linkleri (Responsive) --}}
            <x-responsive-nav-link :href="route('suggestions.index')" :active="request()->routeIs('suggestions.index') || request()->routeIs('suggestions.show')">
                {{ __('Geçmiş Önerilerim') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('suggestions.create')" :active="request()->routeIs('suggestions.create')">
                {{ __('Yeni Öneri Ver') }}
            </x-responsive-nav-link>

            {{-- Yönetici Linkleri (Responsive - Sadece 'admin' rolündeki kullanıcılar görür) --}}
            @if(Auth::check() && Auth::user()->role == 'admin')
                <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800 dark:text-gray-200">Yönetici Alanı</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard*')">
                            {{ __('Yönetici Paneli') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.suggestions.index')" :active="request()->routeIs('admin.suggestions.*')">
                            {{ __('Tüm Öneriler') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')">
                            {{ __('Bölümler') }}
                        </x-responsive-nav-link>
                    </div>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                @auth {{-- Bu bloğu Auth::check() ile sarmak daha güvenli --}}
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                @endauth
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Çıkış Yap') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

@auth {{-- Bu script sadece giriş yapmış kullanıcılar için gerekli --}}
<script>
    function markNotificationsAsSeen() {
        const redDot = document.getElementById('notificationRedDot');
        if (redDot && !redDot.classList.contains('hidden')) { // Sadece görünürse gizle
            redDot.classList.add('hidden');
            // console.log('Kırmızı nokta gizlendi.'); // Test için

            // Opsiyonel: Backend'e "tüm bildirimler kullanıcı tarafından görüldü"
            // bilgisini göndermek için bir AJAX isteği buraya eklenebilir.
            // Bu, sayfayı yenilediklerinde kırmızı noktanın tekrar görünmesini engeller.
            // fetch('{{-- route("notifications.mark-all-seen") --}}', { // Örnek rota, tanımlamanız gerekir
            //     method: 'POST',
            //     headers: {
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            //         'Accept': 'application/json',
            //     }
            // }).then(response => {
            //     if (response.ok) {
            //         // console.log('Tüm bildirimler görüldü olarak işaretlendi.');
            //     }
            // }).catch(error => console.error('Bildirimleri görüldü olarak işaretleme hatası:', error));
        }
    }
</script>
@endauth
@stack('scripts')
