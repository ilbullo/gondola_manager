<?php

namespace App\Livewire\Layout;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

/**
 * Class MenuHeader
 *
 * @package App\Livewire\Layout
 *
 * Gestisce la barra di navigazione superiore e la logica del menu adattivo.
 * Il componente agisce come orchestratore dei punti di accesso, filtrando le voci
 * di menu in base allo stato di autenticazione e ai permessi (Gate/Policy) dell'utente.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Dynamic Navigation: Costruisce la struttura del menu in tempo reale, assicurando
 * coerenza tra i privilegi dell'utente e i link visualizzati.
 * 2. Performance Optimization: Utilizza l'attributo #[Computed] per calcolare i
 * link solo quando necessario, riducendo il payload JSON scambiato tra server e client.
 * 3. Encapsulated Actions: Centralizza operazioni di sistema critiche come il Logout,
 * delegando la logica a classi Action dedicate per una maggiore testabilità.
 * 4. UI State Management: Gestisce lo stato "toggle" del menu per dispositivi mobile
 * senza dipendere da librerie JavaScript esterne pesanti.
 *
 * SICUREZZA:
 * - Utilizza il metodo `can()` di Laravel per verificare le autorizzazioni prima
 * di esporre rotte amministrative, agendo come primo strato di protezione visiva.
 */
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
