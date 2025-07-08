<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    #[Validate('required|string|min:3')]
    public string $name = '';

    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public ?string $error = null;

    public function render()
    {
        return view('livewire.auth.register');
    }

    public function register()
    {
        $this->validate();

        $response = Http::asJson()->acceptJson()->post(config('services.api.url') . '/register', [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'device_name' => 'web', // TODO: Replace this with app device name
        ]);

        if ($response->successful()) {
            session()->put('token', $response->json('token'));
            $this->redirect(route('dashboard'));
            return;
        }

        $this->error = $response->json('message');
    }
}
