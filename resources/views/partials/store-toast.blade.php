@php
    $flashMessage = \App\Support\CustomerFlash::messageFromRequest();

    if (request()->cookie(\App\Support\CustomerFlash::COOKIE)) {
        cookie()->queue(\App\Support\CustomerFlash::forgetCookie());
    }
@endphp

<div
    id="store-toast"
    role="status"
    aria-live="polite"
    @unless (filled($flashMessage)) hidden @endunless
    data-fallback="{{ __('store.toast.saved') }}"
    class="bg-green-500 px-4 py-3 text-center text-base font-bold text-nile-950"
>{{ $flashMessage }}</div>

<script>
    window.storeToast = function (message) {
        var el = document.getElementById('store-toast');
        var text = (message || '').toString().trim();

        if (! el || ! text) {
            return;
        }

        el.textContent = text;
        el.hidden = false;
    };

    document.addEventListener('livewire:init', function () {
        if (! window.Livewire || window.__storeToastBound) {
            return;
        }

        window.__storeToastBound = true;

        Livewire.on('toast', function (payload) {
            var message = typeof payload === 'string'
                ? payload
                : (payload && payload.message) || (Array.isArray(payload) && payload[0] && (payload[0].message || payload[0]));

            window.storeToast(message);
        });
    });
</script>
