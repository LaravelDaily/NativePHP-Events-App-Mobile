<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout>
        <form wire:submit="updateProfileInformation" class="mb-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

            <flux:input wire:model="phone" :label="__('Phone')" type="text" autocomplete="phone" />
            <flux:input wire:model="company" :label="__('Company')" type="text" autocomplete="company" />
            <flux:input wire:model="job_title" :label="__('Job Title')" type="text" autocomplete="job_title" />
            <flux:input wire:model="country" :label="__('Country')" type="text" autocomplete="country" />
            <flux:input wire:model="city" :label="__('City')" type="text" autocomplete="city" />

            {{-- Socials should be a list of links with title and url. Ability to add and remove links.--}}

            <div>
                <flux:heading>{{ __('Socials') }}</flux:heading>
                <flux:subheading>{{ __('Add your social media links') }}</flux:subheading>

                @foreach($socials as $social)
                    <div class="flex flex-col sm:flex-row gap-2 mt-2">
                        <div class="flex-1">
                            <flux:input wire:model="socials.{{ $loop->index }}.title" :label="__('Title')" type="text"
                                required />
                        </div>
                        <div class="flex-1">
                            <flux:input wire:model="socials.{{ $loop->index }}.url" :label="__('URL')" type="text"
                                required />
                        </div>
                        <div class="flex items-end">
                            <flux:button variant="danger" type="button" wire:click="removeSocial({{ $loop->index }})"
                                class="whitespace-nowrap">
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach

                <div class="mt-2">
                    <flux:button variant="primary" type="button" wire:click="addSocial">
                        {{ __('Add Social') }}
                    </flux:button>
                </div>
            </div>

            @if ($error)
                <div
                    class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/50 dark:bg-red-950/50">
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

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message
                    class="me-3 bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded-lg shadow-sm"
                    on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

    </x-settings.layout>
</section>