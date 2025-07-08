<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public ?string $error = null;

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $this->validate();

        $response = Http::asJson()->acceptJson()->post(config('services.api.url') . '/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'web', // TODO: Replace this with app device name
        ]);

        if ($response->successful()) {
            session()->put('token', $response->json('token'));

            $this->redirect(route('dashboard'));
        }

        $this->error = $response->json('message');
    }
}
