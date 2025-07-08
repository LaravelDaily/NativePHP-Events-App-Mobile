<div>
    <h1 class="text-2xl font-bold mb-4">{{ $title }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($events['data'] as $event)
            <div
                class="group relative {{ $event['is_attending'] ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }} rounded-xl border p-6 hover:shadow-lg hover:scale-[1.02] transition-all duration-300">

                <!-- Attending Badge -->
                @if($event['is_attending'])
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
                @endif

                <div class="mb-4 {{ $event['is_attending'] ? 'mt-4' : '' }}">
                    <div class="flex items-center justify-between mb-3">
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white {{ $event['is_attending'] ? 'group-hover:text-green-700 dark:group-hover:text-green-400' : 'group-hover:text-blue-700 dark:group-hover:text-blue-400' }} transition-colors">
                            {{ $event['title'] }}
                        </h3>
                        <span
                            class="px-3 py-1 text-xs font-medium {{ $event['is_attending'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }} rounded-full">
                            {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d') }}
                        </span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                        <svg class="w-4 h-4 mr-2 {{ $event['is_attending'] ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d, Y \a\t g:i A') }}
                    </div>

                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <svg class="w-4 h-4 mr-2 {{ $event['is_attending'] ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            {{ __('Ended') }} {{ \Carbon\Carbon::parse($event['end_datetime'])->format('M d, Y \a\t g:i A') }}
                        @endif
                    </div>
                    <a href="{{ route('events.show', $event['id']) }}" wire:navigate
                        class="inline-flex items-center px-4 py-2 {{ $event['is_attending'] ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-sm font-medium rounded-lg transition-colors duration-200 group-hover:shadow-md">
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

        <div class="col-span-full flex justify-center items-center space-x-4 mt-6">
            @if($events['links']['prev'] !== null)
                <a href="{{ route('events.index', ['filter' => $filter, 'page' => $page - 1]) }}" wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('Previous') }}
                </a>
            @endif

            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Page') }} {{ $page }} {{ __('of') }}
                {{ ceil($events['meta']['total'] / $events['meta']['per_page']) }}
            </span>

            @if($events['links']['next'] !== null)
                <a href="{{ route('events.index', ['filter' => $filter, 'page' => $page + 1]) }}" wire:navigate
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    {{ __('Next') }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>