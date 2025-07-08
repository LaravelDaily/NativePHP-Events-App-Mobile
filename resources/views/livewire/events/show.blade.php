<div>
    <!-- Hero Section -->
    <div
        class="relative bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-4 sm:p-8 mb-6 sm:mb-8 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-purple-600/10"></div>
        <div class="relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                <div class="flex-1 mb-6 lg:mb-0">
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mb-4">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Event Details
                    </div>
                    <h1
                        class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 leading-tight">
                        {{ $event['title'] }}
                    </h1>
                    <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300 mb-6 leading-relaxed max-w-3xl">
                        {{ $event['description'] }}
                    </p>

                    <div class="flex items-center text-gray-600 dark:text-gray-400 mb-4">
                        <svg class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium text-sm sm:text-base">{{ $event['location'] }}</span>
                    </div>

                    <!-- Attend Event Button -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-3 sm:space-y-0">
                        @if($event['is_attending'])
                            <button type="button" wire:click="cancelAttendance({{ $event['id'] }})"
                                class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center text-base sm:text-lg">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel Attendance
                            </button>
                        @else
                            <button type="button" wire:click="attendEvent({{ $event['id'] }})"
                                class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 flex items-center justify-center text-base sm:text-lg">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Attend Event
                            </button>
                        @endif

                        @if($event['is_attending'])
                            <div
                                class="flex items-center justify-center px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-sm sm:text-base">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">You're attending!</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center lg:text-right lg:ml-8">
                    <div
                        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 sm:p-6 shadow-lg inline-block">
                        <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ \Carbon\Carbon::parse($event['start_datetime'])->format('M d') }}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ \Carbon\Carbon::parse($event['start_datetime'])->format('Y') }}
                        </div>
                        <div
                            class="flex items-center justify-center text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ \Carbon\Carbon::parse($event['start_datetime'])->format('g:i A') }} -
                            {{ \Carbon\Carbon::parse($event['end_datetime'])->format('g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Section -->
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div
            class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 px-4 sm:px-8 py-4 sm:py-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div
                    class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3 sm:mr-4">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Event Schedule</h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Complete agenda and speaker lineup
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-8">
            @if(count($event['talks']) === 0)
                <div class="text-center py-12 sm:py-16">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">No talks scheduled yet
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 max-w-md mx-auto">The event schedule
                        will be updated soon. Check back later for the complete agenda.</p>
                </div>
            @else
                @foreach($event['talks'] as $date => $dayTalks)
                    <div class="mb-8 sm:mb-12 last:mb-0">
                        <div class="flex items-center mb-4 sm:mb-6">
                            <div class="w-1 h-6 sm:h-8 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mr-3 sm:mr-4">
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                            </h3>
                        </div>

                        <div class="space-y-4 sm:space-y-6">
                            @foreach($dayTalks as $index => $talk)
                                <div class="group relative">
                                    <!-- Timeline connector -->
                                    @if($index < count($dayTalks) - 1)
                                        <div
                                            class="absolute left-4 sm:left-6 top-10 sm:top-12 w-0.5 h-12 sm:h-16 bg-gradient-to-b from-blue-200 to-transparent">
                                        </div>
                                    @endif

                                    <div
                                        class="relative bg-white dark:bg-gray-700 rounded-xl p-4 sm:p-6 shadow-lg border border-gray-100 dark:border-gray-600 hover:shadow-xl transition-all duration-300 group-hover:scale-[1.02]">
                                        <!-- Timeline dot -->
                                        <div
                                            class="absolute left-0 top-4 sm:top-6 w-2 h-2 sm:w-3 sm:h-3 bg-blue-600 rounded-full border-2 sm:border-4 border-white dark:border-gray-800 shadow-lg transform -translate-x-1 sm:-translate-x-1.5">
                                        </div>

                                        <div class="ml-6 sm:ml-8">
                                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-4">
                                                <div class="flex-1 mb-4 lg:mb-0">
                                                    <h4
                                                        class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                        {{ $talk['title'] }}
                                                    </h4>

                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0 mb-3">
                                                        <div
                                                            class="flex items-center text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">
                                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2 flex-shrink-0" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            {{ \Carbon\Carbon::parse($talk['start_time'])->format('g:i A') }} -
                                                            {{ \Carbon\Carbon::parse($talk['end_time'])->format('g:i A') }}
                                                        </div>
                                                        <div
                                                            class="flex items-center text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2 flex-shrink-0" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                                </path>
                                                            </svg>
                                                            {{ $talk['speaker_name'] }}
                                                        </div>
                                                    </div>

                                                    <p
                                                        class="text-sm sm:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                                                        {{ $talk['description'] }}
                                                    </p>
                                                </div>

                                                <div class="lg:ml-6">
                                                    @if($talk['is_attending'])
                                                        <button type="button" wire:click="cancelAttendanceTalk({{ $talk['id'] }})"
                                                            class="w-full lg:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-sm sm:text-base">
                                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                            {{ __('Cancel') }}
                                                        </button>
                                                    @else
                                                        <button type="button" wire:click="attendTalk({{ $talk['id'] }})"
                                                            class="w-full lg:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-sm sm:text-base">
                                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                            {{ __('Attend') }}
                                                        </button>
                                                    @endif

                                                    @if($talk['is_attending'])
                                                        <div
                                                            class="mt-2 flex items-center justify-center lg:justify-start px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-xs sm:text-sm">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="font-medium">Attending</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>