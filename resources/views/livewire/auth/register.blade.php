<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your name, email and password below to create an account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    @if ($error)
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/50 dark:bg-red-950/50">
            <div class="flex items-center justify-center gap-2">
                <svg class="size-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-red-800 dark:text-red-200">{{ $error }}</span>
            </div>
        </div>
    @endif

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name"
            placeholder="John Doe" />

        <!-- Email Address -->
        <flux:input wire:model="email" :label="__('Email address')" type="email" required autocomplete="email"
            placeholder="email@example.com" />

        <!-- Password -->
        <div class="relative">
            <flux:input wire:model="password" :label="__('Password')" type="password" required
                autocomplete="current-password" :placeholder="__('Password')" />

        </div>

        <!-- Password Confirmation -->
        <div class="relative">
            <!-- Password Confirmation -->
            <flux:input wire:model="password_confirmation" :label="__('Password Confirmation')" type="password" required
                autocomplete="new-password" :placeholder="__('Password Confirmation')" />
        </div>

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Create account') }}</flux:button>
        </div>
    </form>

    @if (Route::has('login'))
        <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Already have an account?') }}
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    @endif
</div>