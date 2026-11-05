<div class="mx-auto max-w-3xl px-4 py-10">
    <p class="text-xs font-semibold text-nile-700 uppercase">{{ __('store.account.kicker') }}</p>
    <h1 class="font-display mt-1 text-4xl">{{ __('store.account.title') }}</h1>
    <p class="mt-2 text-sm text-ink/55">{{ __('store.account.lead') }}</p>

    <nav class="mt-6 flex flex-wrap gap-2" aria-label="{{ __('store.account.kicker') }}">
        <a href="{{ route('profile.edit') }}" class="rounded-full bg-nile-900 px-4 py-2 text-sm font-semibold text-white" wire:navigate>
            {{ __('store.account.profile') }}
        </a>
        <a href="{{ route('orders.index') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink ring-1 ring-black/10 hover:bg-sand-50" wire:navigate>
            {{ __('store.nav.orders') }}
        </a>
    </nav>

    @if (request()->boolean('verified'))
        <p class="mt-6 rounded-2xl bg-stock-soft px-4 py-3 text-sm text-stock">
            {{ __('store.account.verified') }}
        </p>
    @endif

    <section class="mt-6 rounded-[2rem] bg-white p-6 ring-1 ring-black/5 sm:p-8">
        <h2 class="font-semibold">{{ __('store.account.profile') }}</h2>
        <p class="mt-1 text-sm text-ink/55">{{ __('store.account.profile_lead') }}</p>

        <form wire:submit="updateProfileInformation" class="mt-6 space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.name') }}</span>
                <input wire:model="name" type="text" required autocomplete="name" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                @error('name') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.email') }}</span>
                <input wire:model="email" type="email" required autocomplete="email" dir="ltr" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                @error('email') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
            </label>

            @if ($this->hasUnverifiedEmail)
                <p class="rounded-2xl bg-deal/10 px-4 py-3 text-sm text-deal">
                    {{ __('store.account.unverified') }}
                    <button type="button" wire:click="resendVerificationNotification" class="ms-1 font-semibold underline">
                        {{ __('store.account.resend') }}
                    </button>
                </p>
            @endif

            <button type="submit" class="rounded-full bg-gold-500 px-5 py-3 text-sm font-semibold text-white hover:bg-gold-600" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateProfileInformation">{{ __('store.account.save') }}</span>
                <span wire:loading wire:target="updateProfileInformation">{{ __('store.account.saving') }}</span>
            </button>
        </form>
    </section>

    <section class="mt-4 rounded-[2rem] bg-white p-6 ring-1 ring-black/5 sm:p-8">
        <h2 class="font-semibold">{{ __('store.account.password_title') }}</h2>
        <p class="mt-1 text-sm text-ink/55">{{ __('store.account.password_lead') }}</p>

        <form wire:submit="updatePassword" class="mt-6 space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.current_password') }}</span>
                <input wire:model="current_password" type="password" required autocomplete="current-password" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                @error('current_password') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.new_password') }}</span>
                <input wire:model="password" type="password" required autocomplete="new-password" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
                @error('password') <span class="mt-1 block text-xs text-deal">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="text-xs font-medium text-ink/60">{{ __('store.account.confirm_password') }}</span>
                <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 h-12 w-full rounded-2xl bg-sand-50 px-4 text-sm ring-1 ring-black/10 outline-none focus:ring-nile-700">
            </label>

            <button type="submit" class="rounded-full bg-nile-900 px-5 py-3 text-sm font-semibold text-white hover:bg-nile-800" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updatePassword">{{ __('store.account.save_password') }}</span>
                <span wire:loading wire:target="updatePassword">{{ __('store.account.saving') }}</span>
            </button>
        </form>
    </section>

    @if ($this->showDeleteUser)

        <div class="mt-4">
            <livewire:settings.delete-user-form />
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="text-sm font-semibold text-ink/55 underline hover:text-ink">
            {{ __('store.account.logout') }}
        </button>
    </form>
</div>
