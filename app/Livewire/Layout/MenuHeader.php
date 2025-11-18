<?php

namespace App\Livewire\Layout;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class MenuHeader extends Component
{
    public bool $isMenuOpen = false;

    // Menu items definiti come proprietà (più pulito che generare nel render)
    public array $menuItems = [];

    public function mount(): void
    {
        $this->loadMenuItems();
    }

    private function loadMenuItems(): void
{
    $this->menuItems = Auth::check() ? [
        ['id' => 'home',           'label' => 'Home',       'short_label' => 'Home',      'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'route' => route('dashboard')],
        ['id' => 'profile',        'label' => 'Profilo',    'short_label' => 'Profilo',   'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'route' => route('profile')],
        ['id' => 'agency-manager', 'label' => 'Agenzie',    'short_label' => 'Agenz.',    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'route' => route('agency-manager')],
        ['id' => 'table-manager',  'label' => 'Tabella',    'short_label' => 'Tabella',   'icon' => 'M3 3h18v18H3V3zm3 0v18m6-18v18m6-18v18M3 6h18M3 12h18M3 18h18', 'route' => route('table-manager')],
        ['id' => 'logout',         'label' => 'Esci',       'short_label' => 'Esci',      'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1', 'route' => '#', 'action' => 'logout'],
    ] : [
        ['id' => 'login', 'label' => 'Login', 'short_label' => 'Login', 'icon' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1', 'route' => route('login')],
    ];
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