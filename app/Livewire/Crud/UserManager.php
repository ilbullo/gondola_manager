<?php

namespace App\Livewire\Crud;

use App\Models\User;
use App\Enums\{UserRole, LicenseType};
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule as ValidationRule;


/* Class UserManager
 *
 * @package App\Livewire\Crud
 *
 * Gestore centralizzato dell'anagrafica utenti e delle credenziali di accesso.
 * Implementa logiche di creazione, modifica e ordinamento dinamico, integrando
 * il controllo dei ruoli e le specifiche delle licenze.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Identity Management: Gestisce il ciclo di vita degli account, inclusa la cifratura
 * sicura delle password (bcrypt) e la validazione univoca delle email.
 * 2. Dynamic Sorting: Implementa un sistema di ordinamento bidirezionale sulle colonne della tabella,
 * migliorando l'usabilità per database con elevato numero di record.
 * 3. UI State Orchestration: Coordina la transizione tra la vista elenco e la modalità
 * di editing senza ricaricamento della pagina, utilizzando proprietà reattive.
 * 4. Enum Injection: Inserisce automaticamente le casistiche degli Enum (UserRole, LicenseType)
 * nella view per popolare dropdown coerenti con la logica di dominio.
 * 5. Event-Driven Deletion: Utilizza un sistema di conferma asincrono per prevenire
 * cancellazioni accidentali di profili utente.
 *
 * SICUREZZA:
 * - Password Handling: Gestisce la password in modo opzionale durante l'update,
 * aggiornandola solo se effettivamente digitata dall'amministratore.
 */
class UserManager extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // UI State
    public bool $editing = false;
    public int $userId = 0;

    // Form fields
    public string $name = '';
    public string $email = '';
    public ?string $password = null;
    public string $role = '';
    public ?string $type = null;
    public ?string $license_number = null;

    /**
     * Centralizziamo le notifiche coerenti con il Toast component
     */
    private function notify(string $message, string $title = 'SUCCESSO'): void
    {
        $this->dispatch('notify',
            message: $message,
            title: $title,
            type: 'success'
        );
    }

    /**
     * Hook di Livewire: viene eseguito ogni volta che $license_number cambia nel frontend
     */
    public function updatedLicenseNumber($value): void
    {
        // Autocompila l'email solo se stiamo creando un nuovo utente (userId === 0)
        // o se l'email è vuota, per evitare di sovrascrivere dati esistenti involontariamente.
        if ($this->userId === 0 && !empty($value)) {
            $this->email = strtolower(trim($value)) . '@dogana.it';
        }
    }

    public function resetForm(): void
    {
        // Aggiorna anche qui nel reset
        $this->reset(['name', 'email', 'password', 'role', 'type', 'license_number', 'userId', 'editing']);
        $this->resetValidation();
        $this->role = UserRole::BANCALE->value;
        // Impostiamo la password predefinita al reset (creazione nuovo utente)
        $this->password = 'password';
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editing = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();
        $user = User::findOrFail($id);

        $this->userId         = $user->id;
        $this->name           = $user->name;
        $this->email          = $user->email;
        $this->role           = $user->role->value ?? '';
        $this->type           = $user->type->value ?? null;
        $this->license_number = $user->license_number;

        $this->editing = true;
    }

    public function save(): void
    {
        $validatedData = $this->validate([
            'name'           => 'required|string|max:255',
            'role'           => 'required|string',
            'type'           => 'nullable|string',
            'license_number' => 'required|string|max:255',
            'email' => [
                'required', 'string', 'email', 'max:255',
                ValidationRule::unique('users', 'email')->ignore($this->userId)
            ],
            'password' => $this->userId === 0
                ? 'required|string|min:8'
                : 'nullable|string|min:8',
        ]);

        $data = collect($validatedData)->except('password')->toArray();
        if (!empty($validatedData['password'])) {
            $data['password'] = bcrypt($validatedData['password']);
        }

        if ($this->userId > 0) {
            User::findOrFail($this->userId)->update($data);
            $msg = 'Utente aggiornato.';
        } else {
            User::create($data);
            $msg = 'Utente creato.';
        }

        $this->resetForm();
        $this->notify($msg);
        // Se hai una cache per la lista utenti, svuotala qui
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo utente?',
            'confirmEvent' => 'confirmDeleteUser',
            'payload'      => $id,
        ]);
    }

    #[On('confirmDeleteUser')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;

        //impedisco di autoeliminarmi (solo per sicurezza! il mio ID non appare nella lista)
        if ($id === Auth::user()->id) {
            $this->dispatch('notify', message: 'Non puoi eliminare il tuo stesso account!', type: 'error');
            return;
        }

        if ($id) {
            User::findOrFail($id)->delete();
            $this->notify('Utente eliminato.', 'SISTEMA');
        }
    }

    public function setSort(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(fn($q) =>
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%')
                );
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.crud.user-manager', [
            'users'        => $users,
            'roles'        => UserRole::cases(),
            'licenseTypes' => LicenseType::cases(),
        ]);
    }
}
