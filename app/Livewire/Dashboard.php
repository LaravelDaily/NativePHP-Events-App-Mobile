<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Dashboard extends Component
{
    public function getEvents($filter, $perPage = 3)
    {
        return Http::asJson()
            ->withToken(session('token'))
            ->acceptJson()
            ->get(config('services.api.url') . '/events', [
                'filter' => $filter,
                'per_page' => $perPage,
            ])
            ->json();
    }
    public function render()
    {
        $attendingEvents = $this->getEvents('attending');
        $upcomingEvents = $this->getEvents('upcoming');


        return view('livewire.dashboard', [
            'attendingEvents' => $attendingEvents,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
