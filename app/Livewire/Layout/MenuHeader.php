<?php

namespace App\Livewire\Layout;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

class MenuHeader extends Component
{
    public bool $isMenuOpen = false;

    /**
     * Usiamo una Computed Property:
     * 1. Non appesantisce lo stato pubblico (meno traffico JSON)
     * 2. Si aggiorna automaticamente se cambia l'utente
     */
    #[Computed]
    public function menuItems(): array
    {
        if (!Auth::check()) {
            return [
                ['id' => 'login', 'label' => 'Login', 'icon' => 'login', 'route' => route('login')],
            ];
        }

        $user = Auth::user();

        // Usiamo array_values per garantire che l'array sia una lista pulita [0, 1, 2...]
        return array_values(array_filter([
            ['id' => 'dashboard', 'label' => 'Home', 'icon' => 'home', 'route' => route('dashboard')],
            ['id' => 'profile', 'label' => 'Profilo', 'icon' => 'profile', 'route' => route('profile')],
            
            $user->can('access-user-manager') 
                ? ['id' => 'user-manager', 'label' => 'Utenti', 'icon' => 'users', 'route' => route('user-manager')] 
                : null,
                
            $user->can('access-agency-manager') 
                ? ['id' => 'agency-manager', 'label' => 'Agenzie', 'icon' => 'agencies', 'route' => route('agency-manager')] 
                : null,
                
            $user->can('access-table-manager') 
                ? ['id' => 'table-manager', 'label' => 'Tabella', 'icon' => 'table', 'route' => route('table-manager')] 
                : null,
            
            ['id' => 'logout', 'label' => 'Esci', 'icon' => 'logout', 'route' => '#', 'action' => 'logout'],
        ]));
    }

    public function toggleMenu(): void
    {
        $this->isMenuOpen = !$this->isMenuOpen;
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.layout.menu-header');
    }
}