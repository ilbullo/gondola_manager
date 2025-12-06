<?php

namespace App\Livewire\Crud;

use App\Models\User;
use App\Enums\{UserRole, LicenseType};
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rule as ValidationRule;

/**
 * Class UserManager
 *
 * Componente Livewire per la gestione completa degli utenti:
 * - Ricerca e ordinamento
 * - Creazione e modifica con validazioni dinamiche
 * - Eliminazione con conferma
 * - Gestione paginazione automatica
 * - Supporto a Enum per ruoli e tipo licenze
 */
class UserManager extends Component
{
    use WithPagination;

    // ===================================================================
    // PROPRIETÀ DI STATO / RICERCA
    // ===================================================================

    /** @var string Ricerca libera per nome o email. */
    public string $search = '';

    /** @var bool Compatibilità layout: mostra eventuali eliminati */
    public bool $showDeleted = false;

    /** @var string Campo usato per l’ordinamento */
    public string $sortField = 'name';

    /** @var string Direzione dell’ordine (asc|desc) */
    public string $sortDirection = 'asc';

    // ===================================================================
    // PROPRIETÀ DI CONTROLLO FORM
    // ===================================================================

    /** @var bool Flag che indica se il form è in modalità editing */
    public bool $editing = false;

    /** @var int ID dell'utente in modifica (0 = creazione) */
    public int $userId = 0;

    // ===================================================================
    // CAMPI MODELLO USER
    // ===================================================================

    /** @var string Nome dell’utente */
    #[Rule('required|string|max:255')]
    public string $name = '';

    /** @var string Email utente, con validazioni dinamiche in save() */
    public string $email = '';

    /** @var ?string Password (obbligatoria solo in creazione) */
    public ?string $password = null;

    /** @var string Ruolo utente (Enum UserRole) */
    #[Rule('required|string')]
    public string $role = UserRole::BANCALE->value;

    /** @var ?string Tipo di licenza (Enum LicenseType) */
    #[Rule('nullable|string')]
    public ?string $type = null;

    /** @var ?string Numero licenza */
    #[Rule('nullable|string|max:255')]
    public ?string $license_number = null;

    // ===================================================================
    // METODI DI SUPPORTO
    // ===================================================================

    /**
     * Mostra un messaggio di notifica tramite flash session.
     *
     * @param string $message
     */
    private function notify(string $message): void
    {
        session()->flash('message', $message);
    }

    // ===================================================================
    // AZIONI DI INTERAZIONE
    // ===================================================================

    /**
     * Resetta completamente il form e le validazioni.
     * Usato sia per un nuovo utente sia prima di una modifica.
     */
    public function resetForm(): void
    {
        $this->reset([
            'name', 'email', 'password', 'role', 
            'type', 'license_number', 'editing', 'userId'
        ]);

        $this->resetValidation();

        // Default ruolo
        $this->role = UserRole::BANCALE->value;
    }

    /**
     * Avvia la creazione di un nuovo utente aprendo il form.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->editing = true;
    }

    /**
     * Carica i dati di un utente esistente nel form per modificarlo.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetForm();

        $user = User::findOrFail($id);

        $this->userId         = $user->id;
        $this->name           = $user->name;
        $this->email          = $user->email;
        $this->role           = $user->role->value ?? UserRole::BANCALE->value;
        $this->type           = $user->type->value ?? null;
        $this->license_number = $user->license_number;

        $this->editing = true;
    }

    /**
     * Salva o aggiorna l’utente applicando validazioni dinamiche:
     * - Password richiesta solo in creazione
     * - Email unica ma ignorando l’utente in modifica
     */
    public function save(): void
    {
        // --- Validazioni dinamiche ---
        $rules = [
            'name'           => 'required|string|max:255',
            'role'           => 'required|string',
            'type'           => 'nullable|string',
            'license_number' => 'nullable|string|max:255',

            'email' => [
                'required', 'string', 'email', 'max:255',
                ValidationRule::unique('users', 'email')->ignore($this->userId)
            ],

            'password' => $this->userId === 0
                ? 'required|string|min:8'
                : 'nullable|string|min:8',
        ];

        $validatedData = $this->validate($rules);

        // --- Preparazione dati ---
        $data = [
            'name'           => $validatedData['name'],
            'email'          => $validatedData['email'],
            'role'           => $validatedData['role'],
            'type'           => $validatedData['type'],
            'license_number' => $validatedData['license_number'],
        ];

        if (!empty($validatedData['password'])) {
            $data['password'] = $validatedData['password'];
        }

        // --- Salvataggio ---
        if ($this->userId > 0) {
            User::findOrFail($this->userId)->update($data);
            $message = 'Utente modificato con successo!';
        } else {
            User::create($data);
            $message = 'Nuovo utente creato con successo!';
        }

        $this->resetForm();
        $this->notify($message);
    }

    // ===================================================================
    // CANCELLAZIONE
    // ===================================================================

    /**
     * Richiede conferma prima di eliminare un utente,
     * aprendo un modal lato front-end tramite evento Livewire.
     *
     * @param int $id
     */
    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo utente?',
            'confirmEvent' => 'confirmDeleteUser',
            'payload'      => $id,
        ]);
    }

    /**
     * Esegue l’eliminazione dopo conferma.
     *
     * @param mixed $payload ID dell’utente passato dal modal
     */
    #[On('confirmDeleteUser')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload)
            ? ($payload['id'] ?? $payload[0] ?? null)
            : $payload;

        if ($id) {
            User::findOrFail($id)->delete();
            $this->notify('Utente eliminato con successo.');
        }
    }

    // ===================================================================
    // GESTIONE QUERY / ORDINAMENTO
    // ===================================================================

    /**
     * Imposta il campo per l’ordinamento e alterna ASC/DESC.
     *
     * @param string $field
     */
    public function setSort(string $field): void
    {
        if ($field === $this->sortField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    /**
     * Reset della paginazione quando cambia la ricerca.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ===================================================================
    // RENDER
    // ===================================================================

    /**
     * Recupera gli utenti applicando:
     * - ricerca
     * - ordinamento
     * - paginazione
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
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
