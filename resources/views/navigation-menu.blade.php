<nav x-data="{ open: false }" x-effect="document.body.classList.toggle('menu-open', open)" class="nav">
    <div class="site-header">
        <article class="site-header__inner">
            <button @click="open = ! open" class="burger-icon | ui-icon-btn">
                <svg class="ui-icon" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'is-hidden': open,   'is-inline': ! open }" class="is-inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'is-hidden': ! open, 'is-inline': open }" class="is-hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                    <x-icon :size="24" decorative fill="#344D55">
                        <x-ui.icons.search />
                    </x-icon>
                    @livewire('navigation-cart')
                </div>

                @auth
                    <div class="action-links__auth">
                        <x-dropdown align="right" width="sm">
                            <x-slot name="trigger">
                                <div>
                                    <x-icon :size="24" decorative fill="#344D55">
                                        <x-ui.icons.account />
                                    </x-icon>
                                    
                                    <svg class="ui-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </x-slot>

                            <x-slot name="content">
                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
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
            </div>
        </article>
        
        <div class="desktop-collection text-center padding-block-6">
            <x-ui.product-collections />
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'is-block': open, 'is-hidden': ! open}" class="is-hidden">
        <!-- Responsive Settings Options -->
        <div class="mobile-menu">
            <header class="mobile-menu__top">
                <button
                    type="button"
                    @click="open = false"
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
                        <x-responsive-nav-link href="{{ route('notifications') }}">
                            <x-icon :size="24" decorative fill="#F6F6F6">
                                <x-ui.icons.notification />
                            </x-icon>
                        </x-responsive-nav-link>
                    </div>
                @endauth
            </header>

            <x-responsive-nav-link class="mobile-menu__home" href="{{ route('home') }}" :active="request()->routeIs('home')">
                {{ __('global.home') }}
            </x-responsive-nav-link>

            <x-ui.product-collections />

            @auth
                <article class="mobile-menu__auth">
                    <div class="mt-3 space-y-1">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
    
                            <x-responsive-nav-link href="{{ route('logout') }}"
                                        @click.prevent="$root.submit();">
                                {{ __('auth.log_out') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
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