<div>
    @if ($available)
        <p class="text-xs font-semibold text-gold-400 uppercase">{{ __('store.content.subscribe_title') }}</p>
        <p class="mt-3 text-sm leading-6 text-sand-100/75">{{ __('store.content.subscribe_text') }}</p>
        @if ($done)
            <p class="mt-4 text-sm font-semibold text-white">{{ __('store.content.subscribe_done') }}</p>
        @else
            <form wire:submit="subscribe" class="mt-4 flex gap-2">
                <label class="sr-only" for="subscribe-email">{{ __('store.content.subscribe_email') }}</label>
                <input id="subscribe-email" type="email" wire:model="email" required placeholder="{{ __('store.content.subscribe_email') }}" class="h-11 min-w-0 flex-1 rounded-full bg-white px-4 text-sm text-ink outline-none">
                <button type="submit" class="h-11 shrink-0 rounded-full bg-gold-500 px-4 text-sm font-semibold text-white hover:bg-gold-600">{{ __('store.content.subscribe_button') }}</button>
            </form>
            @error('email')
                <p class="mt-2 text-xs text-gold-300">{{ $message }}</p>
            @enderror
        @endif
    @endif
</div>
