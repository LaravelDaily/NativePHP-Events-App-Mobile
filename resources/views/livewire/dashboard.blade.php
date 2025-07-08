<div class="container mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Dashboard') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg">
            {{ __('Welcome back! Here\'s what\'s happening with your events.') }}
        </p>
    </div>

    <!-- Events You're Attending Section -->
    @if(count($attendingEvents['data']) > 0)
        <div class="mb-10" x-data="{ attendingEventsOpen: true }">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Events You\'re Attending') }}
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            {{ __('You\'re registered for these upcoming events') }}
                        </p>
                    </div>
                </div>
                <button @click="attendingEventsOpen = !attendingEventsOpen"
                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': attendingEventsOpen }" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div x-show="attendingEventsOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-4"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($attendingEvents['data'] as $event)
                    <div
                        class="group relative bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800 p-6 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                        <!-- Attending Badge -->
                        <div class="absolute top-4 right-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                {{ __('Attending') }}
                            </span>
                        </div>

                        <div class="mb-4 mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-green-700 dark:group-hover:text-green-400 transition-colors">
                                    {{ $event['title'] }}
                                </h3>
                            </div>

                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                                <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d, Y \a\t g:i A') }}
                            </div>

                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                                <svg class="w-4 h-4 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $event['location'] }}
                            </div>
                        </div>

                        <p class="text-gray-700 dark:text-gray-300 text-sm mb-6 line-clamp-3">
                            {{ $event['description'] }}
                        </p>

                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if(Carbon\Carbon::parse($event['start_datetime']) > now())
                                    {{ __('Starts in') }} {{ \Carbon\Carbon::parse($event['start_datetime'])->diffForHumans() }}
                                @else
                                    {{ __('Ended') }}
                                    {{ \Carbon\Carbon::parse($event['end_datetime'])->format('M d, Y \a\t g:i A') }}
                                @endif
                            </div>
                            <a href="{{ route('events.show', $event['id']) }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 group-hover:shadow-md">
                                {{ __('View Details') }}
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach

                @if($attendingEvents['meta']['total'] > $attendingEvents['meta']['to'])
                    <div class="col-span-full">
                        <a href="{{ route('events.index', ['filter' => 'attending']) }}" wire:navigate
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 group-hover:shadow-md">
                            {{ __('View All Events') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Other Events Section -->
    <div class="mb-8" x-data="{ otherEventsOpen: true }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg mr-4">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Discover More Events') }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ __('Explore these upcoming events and conferences') }}
                    </p>
                </div>
            </div>
            <button @click="otherEventsOpen = !otherEventsOpen"
                class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 transition-transform duration-200"
                    :class="{ 'rotate-180': otherEventsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <div x-show="otherEventsOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4">
            @if(count($upcomingEvents['data']) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($upcomingEvents['data'] as $event)
                        <div
                            class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3
                                        class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $event['title'] }}
                                    </h3>
                                    <span
                                        class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                                        {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d') }}
                                    </span>
                                </div>

                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d, Y \a\t g:i A') }}
                                </div>

                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $event['location'] }}
                                </div>
                            </div>

                            <p class="text-gray-700 dark:text-gray-300 text-sm mb-6 line-clamp-3">
                                {{ $event['description'] }}
                            </p>

                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @if(Carbon\Carbon::parse($event['start_datetime']) > now())
                                        {{ __('Starts in') }} {{ \Carbon\Carbon::parse($event['start_datetime'])->diffForHumans() }}
                                    @else
                                        {{ __('Ended') }}
                                        {{ \Carbon\Carbon::parse($event['end_datetime'])->format('M d, Y \a\t g:i A') }}
                                    @endif
                                </div>
                                <a href="{{ route('events.show', $event['id']) }}" wire:navigate
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 group-hover:shadow-md">
                                    {{ __('View Details') }}
                                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @if($upcomingEvents['meta']['total'] > $upcomingEvents['meta']['to'])
                        <div class="col-span-full">
                            <a href="{{ route('events.index', ['filter' => 'upcoming']) }}" wire:navigate
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 group-hover:shadow-md">
                                {{ __('View All Events') }}
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-12">
                    <div
                        class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No events available') }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ __('Check back later for new events and conferences.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>


</div>