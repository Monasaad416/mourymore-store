<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <x-auth-session-status class="rounded-2xl bg-emerald-50 px-4 py-3 text-center text-emerald-800" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.email') }}"
            class="flex flex-col gap-6"
            x-data="{ submitting: false }"
            @submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button" x-bind:disabled="submitting">
                <span x-show="!submitting">{{ __('Email password reset link') }}</span>
                <span x-cloak x-show="submitting">{{ __('store.auth.submitting') }}</span>
            </flux:button>
        </form>

        <div class="space-x-1 text-center text-sm text-ink/60 rtl:space-x-reverse">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
