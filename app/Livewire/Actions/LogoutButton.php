<?php 

namespace App\Livewire\Actions;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LogoutButton extends Component
{
    public function logout()
    {
        Auth::logout();

        // Invalida la sessione
        session()->invalidate();
        session()->regenerateToken();

        // Reindirizza alla pagina di login o home
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.actions.logout-button');
    }
}
