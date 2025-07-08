<?php

use App\Livewire\Dashboard;
use App\Livewire\Events;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['api.token.exists'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('events/show/{event}', Events\Show::class)->name('events.show');
    Route::get('events/{filter?}/{page?}', Events\Index::class)->name('events.index');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
});

require __DIR__ . '/auth.php';
