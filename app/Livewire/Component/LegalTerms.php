<?php

namespace App\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class LegalTerms extends Component
{

    public function accept()
    {
        Auth::user()->legalAcceptances()->create([
            'version' => config('legal.current_version'),
            'ip_address' => request()->ip(),
            'accepted_at' => now(),
        ]);

        return redirect()->route('home');
    }

    #[Layout('layouts.guest-legal')]
    public function render()
    {
        return view('livewire.component.legal-terms');
    }
}
