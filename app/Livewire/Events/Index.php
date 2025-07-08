<?php

namespace App\Livewire\Events;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Index extends Component
{
    public string $filter = 'attending';

    public int $page = 1;

    public function mount($filter = 'attending', $page = 1)
    {
        $this->filter = $filter ?? 'attending';
        $this->page = $page ?? 1;
    }

    public function getEvents($perPage = 10, $page = 1)
    {
        return Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events', ['filter' => $this->filter, 'per_page' => $perPage, 'page' => $page])
            ->json();
    }

    public function render()
    {
        $events = $this->getEvents(3, $this->page);
        $title = match ($this->filter) {
            'upcoming' => __('Upcoming Events'),
            'attending' => __('Attending Events'),
            'past' => __('Past Events'),
            default => __('All Events'),
        };

        return view('livewire.events.index', [
            'filter' => $this->filter,
            'events' => $events,
            'title' => $title,
        ]);
    }
}
