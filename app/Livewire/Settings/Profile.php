<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?string $company = null;

    public ?string $job_title = null;

    public ?string $country = null;

    public ?string $city = null;

    public ?array $socials = [];

    public ?string $error = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = cache('user')['name'];
        $this->email = cache('user')['email'];
        $this->phone = cache('user')['phone'];
        $this->company = cache('user')['company'];
        $this->job_title = cache('user')['job_title'];
        $this->country = cache('user')['country'];
        $this->city = cache('user')['city'];
        $this->socials = cache('user')['socials'] ?? [];
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $this->error = null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'socials' => ['nullable', 'array'],
            'socials.*.title' => ['required', 'string', 'max:255'],
            'socials.*.url' => ['required', 'string', 'max:255'],
        ]);

        $update = Http::asJson()
            ->acceptJson()
            ->withToken(session('token'))
            ->put(config('services.api.url') . '/profile', $validated);

        if ($update->successful()) {
            cache()->put('user', $update->json());

            $this->dispatch('profile-updated', name: $this->name);
        } else {
            $this->error = $update->json()['message'];
        }
    }

    public function addSocial(): void
    {
        $this->socials[] = ['title' => '', 'url' => ''];
    }

    public function removeSocial(int $index): void
    {
        unset($this->socials[$index]);
        $this->socials = array_values($this->socials);
    }
}
