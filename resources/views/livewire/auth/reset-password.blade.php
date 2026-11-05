<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('password.update') }}"
            class="flex flex-col gap-6"
            x-data="{ submitting: false }"
            @submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:input
                name="email"
                value="{{ old('email', request('email')) }}"
                :label="__('Email address')"
                type="email"
                required
                autocomplete="email"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button" x-bind:disabled="submitting">
                    <span x-show="!submitting">{{ __('Reset password') }}</span>
                    <span x-cloak x-show="submitting">{{ __('store.auth.submitting') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
