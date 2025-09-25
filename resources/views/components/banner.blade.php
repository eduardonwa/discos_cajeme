@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div
    class="banner"
    x-data="{
        show: true,
        style: @js($style),
        message: @js($message),
        hideTimer: null,
        showFor(ms = 3000) {
            this.show = true
            clearTimeout(this.hideTimer)
            this.hideTimer = setTimeout(() => { this.show = false }, ms)
        }
    }"
    :class="{
        'banner--success': style === 'success',
        'banner--danger':  style === 'danger',
        'banner--warning': style === 'warning',
        'banner--info':    style === 'info',
        'banner--neutral': !['success','danger','warning','info'].includes(style)
    }"
    x-show="show && message"
    x-cloak
    @banner-message.window="
        style   = $event.detail.style   ?? style;
        message = $event.detail.message ?? message;
        showFor($event.detail.duration ?? 3000);
    "
>
    <div class="banner__container">
        <div class="banner__row">
            <div class="banner__left">
                <span class="banner__icon">
                    <svg x-show="style == 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg x-show="style == 'danger'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <svg x-show="style != 'success' && style != 'danger' && style != 'warning'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <svg x-show="style == 'warning'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4v.01 0 0 " />
                    </svg>
                </span>

                <p class="banner__message" x-text="message"></p>
            </div>

            <div class="banner__actions">
                <x-icon x-on:click="show = false" aria-label="Dismiss">
                    <x-ui.icons.close />
                </x-icon>
            </div>
        </div>
    </div>
</div>
