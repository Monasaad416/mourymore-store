<section class="rounded-[2rem] bg-white p-6 ring-1 ring-black/5 sm:p-8" x-data="{ open: false }">
    <h2 class="font-semibold text-deal">{{ __('store.account.delete') }}</h2>
    <p class="mt-1 text-sm text-ink/55">{{ __('store.account.delete_lead') }}</p>

    <button type="button" class="mt-5 rounded-full bg-deal px-5 py-3 text-sm font-semibold text-white hover:opacity-90" @click="open = true">
        {{ __('store.account.delete') }}
    </button>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 grid place-items-center bg-ink/40 p-4"
        @keydown.escape.window="open = false"
    >
        <form wire:submit="deleteUser" class="w-full max-w-md rounded-[1.75rem] bg-white p-6 shadow-xl" @click.outside="open = false">
            <h3 class="font-semibold">{{ __('store.account.delete_confirm_title') }}</h3>
            <p class="mt-2 text-sm text-ink/60">{{ __('store.account.delete_confirm_body') }}</p>

            <label class="mt-4 block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.password') }}</span>
                <input wire:model="password" type="password" required autocomplete="current-password" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                @error('password') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
            </label>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold text-ink/60 hover:bg-sand-50" @click="open = false">
                    {{ __('store.account.cancel') }}
                </button>
                <button type="submit" class="rounded-full bg-deal px-4 py-2 text-sm font-semibold text-white">
                    {{ __('store.account.delete') }}
                </button>
            </div>
        </form>
    </div>
</section>
