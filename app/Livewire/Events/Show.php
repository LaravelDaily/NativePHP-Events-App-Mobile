<?php

namespace App\Livewire\Events;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Show extends Component
{
    public $event;

    public function mount($event)
    {
        $this->event = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events/' . $event)
            ->json('data');
    }

    public function render()
    {
        return view('livewire.events.show', [
            'event' => $this->event,
        ]);
    }

    public function attendEvent($eventId)
    {
        Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->post(config('services.api.url') . '/events/' . $eventId . '/attend')
            ->json();

        $this->event = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events/' . $eventId)
            ->json('data');
    }

    public function cancelAttendance($eventId)
    {
        Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->delete(config('services.api.url') . '/events/' . $eventId . '/attend')
            ->json();

        $this->event = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events/' . $eventId)
            ->json('data');
    }

    public function attendTalk($talkId)
    {
        Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->post(config('services.api.url') . '/talks/' . $talkId . '/attend')
            ->json();

        $this->event = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events/' . $this->event['id'])
            ->json('data');
    }

    public function cancelAttendanceTalk($talkId)
    {
        Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->delete(config('services.api.url') . '/talks/' . $talkId . '/attend')
            ->json();


        $this->event = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->get(config('services.api.url') . '/events/' . $this->event['id'])
            ->json('data');
    }
}
