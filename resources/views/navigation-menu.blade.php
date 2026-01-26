@php $isCollection = request()->routeIs('collection'); @endphp
<nav x-data class="nav no-print">
    <div class="site-header">
        <article class="site-header__inner">
            <button @click="$store.ui.navOpen = !$store.ui.navOpen" class="burger-icon ui-icon-btn" aria-expanded="$store.ui.navOpen">
                <svg class="ui-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <nav class="nav-links">
                <a href="#" class="uppercase fw-bold">acerca</a>
                <a href="#" class="uppercase fw-bold">contacto</a>
            </nav>

            <a wire:navigate href="{{ route('home') }}" class="wordmark | no-decor">
                <x-application-mark />
            </a>

            {{-- action links --}}
            <div class="action-links">
                <div class="action-links__icons">
                    <x-icon
                        @click="$dispatch('open-search')"
                        x-data
                        :size="24"
                        orientation="left"
                        decorative
                        fill="#344D55"
                        aria-haspop="dialog"
                        aria-controls="heroSearchModal"
                    >
                        <x-ui.icons.search />
                    </x-icon>
                    
                    @livewire('navigation-cart', [], key('nav-cart'))

                    @if ($isCollection)
                        <button
                            @click="$store.ui.filtersOpen = true"
                            type="button"
                            class="button uppercase ff-semibold" data-type="ghost"
                        >
                            filtros
                        </button>
                    @endunless
                </div>

                @unless ($isCollection)
                    @auth
                        <div class="action-links__auth">
                            <x-dropdown align="right" width="sm">
                                <x-slot name="trigger">
                                    <div>
                                        <x-icon :size="24" decorative>
                                            <x-ui.icons.account />
                                        </x-icon>
                                        
                                        <svg class="ui-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </div>
                                </x-slot>

                                <x-slot name="content">
                                    <!-- Account Management -->
                                    <div>
                                        {{ __('profile.manage_account') }}
                                    </div>

                                    <x-dropdown-link wire:navigate href="{{ route('profile.show') }}">
                                        {{ __('profile.profile') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link wire:navigate href="{{ route('my-orders') }}">
                                        {{ __('profile.my_orders') }}
                                    </x-dropdown-link>

                                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                        <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                            {{ __('API Tokens') }}
                                        </x-dropdown-link>
                                    @endif

                                    <hr>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}" x-data>
                                        @csrf

                                        <x-dropdown-link href="{{ route('logout') }}"
                                                @click.prevent="$root.submit();">
                                            {{ __('auth.log_out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endauth

                    @guest
                        <div class="action-links__guest">
                            <x-nav-link class="uppercase fs-200" wire:navigate href="{{ route('login') }}">
                                {{ __('auth.log_in') }}
                            </x-nav-link>
                            
                            <x-nav-link class="uppercase fs-200" wire:navigate href="{{ route('register') }}">
                                {{ __('auth.register') }}
                            </x-nav-link>
                        </div>
                    @endguest
                @endunless
            </div>
        </article>
        
        <div class="desktop-collection text-center padding-block-6">
            <x-ui.collections-header />
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-cloak :class="{ 'is-block': $store.ui.navOpen, 'is-hidden': !$store.ui.navOpen }">
        <!-- Responsive Settings Options -->
        <div class="mobile-menu" x-trap.noscroll="$store.ui.navOpen" @keydown.escape.window="$store.ui.navOpen=false">
            <header class="mobile-menu__top">
                <button
                    type="button"
                    @click="$store.ui.navOpen=false"
                    class="close-mobile | ui-icon-btn"
                >
                    <span>&#10006;</span>
                </button>
    
                @guest
                    <div>
                        <x-nav-link class="uppercase" wire:navigate href="{{ route('login') }}">
                            {{ __('auth.log_in') }}
                        </x-nav-link>
                        |
                        <x-nav-link class="uppercase" wire:navigate href="{{ route('register') }}">
                            {{ __('auth.register') }}
                        </x-nav-link>
                    </div>
                @endguest

                @auth
                    <div class="flex-group" style="align-items: center;">
                        <!-- Account Management -->
                        <a href="{{ route('notifications') }}">
                            <x-icon :size="24" decorative fill="#F6F6F6">
                                <x-ui.icons.notification />
                            </x-icon>
                        </a>
                    </div>
                @endauth
            </header>

            <x-responsive-nav-link class="mobile-menu__home" href="{{ route('home') }}" :active="request()->routeIs('home')">
                {{ __('global.home') }}
            </x-responsive-nav-link>

            <x-ui.collections-header />

            @auth
                <article class="mobile-menu__auth">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf

                        <x-responsive-nav-link href="{{ route('logout') }}"
                                    @click.prevent="$root.submit();">
                            {{ __('auth.log_out') }}
                        </x-responsive-nav-link>
                    </form>
                </article>
            @endauth

            <footer class="mobile-menu__footer">
                <div class="links">
                    <a href="#" class="uppercase ff-bold">
                        acerca
                    </a>
                    <a href="#" class="uppercase ff-bold">
                        contacto
                    </a>
                </div>

                <div class="icons">
                    <x-icon href="https://facebook.com" label="Facebook">
                        <x-ui.icons.socials.facebook />
                    </x-icon>
                    
                    <x-icon href="https://instagram.com" label="Instagram">
                        <x-ui.icons.socials.instagram />
                    </x-icon>

                    <x-icon href="https://youtube.com" label="YouTube">
                        <x-ui.icons.socials.youtube />
                    </x-icon>
                </div>
            </footer>
        </div>
    </div>
</nav>