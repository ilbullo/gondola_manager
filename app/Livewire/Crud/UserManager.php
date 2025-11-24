<?php

namespace App\Livewire\Crud;

use App\Models\User;
use App\Enums\{UserRole, LicenseType};
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Illuminate\Validation\Rule as ValidationRule;

class UserManager extends Component
{
    use WithPagination;

    // Proprietà di Ricerca e Stato
    public string $search = '';
    public bool $showDeleted = false; // Mantenuto per coerenza di layout
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // Proprietà Modello/Form
    public bool $editing = false;
    public int $userId = 0; // 0 per creazione, > 0 per modifica

    // Campi del Modello User
    // Le regole per email e password sono gestite condizionalmente in save()
    #[Rule('required|string|max:255')]
    public string $name = '';
    public string $email = '';
    public ?string $password = null;

    #[Rule('required|string')]
    public string $role = UserRole::BANCALE->value;

    #[Rule('nullable|string')]
    public ?string $type = null;

    #[Rule('nullable|string|max:255')]
    public ?string $license_number = null;


    private function notify(string $message): void
    {
        session()->flash('message', $message);
    }

    // ===================================================================
    // AZIONI DI INTERAZIONE
    // ===================================================================

    public function resetForm(): void
    {
        // Resetta le proprietà del form
        $this->reset(['name', 'email', 'password', 'role', 'type', 'license_number', 'editing', 'userId']);
        // Resetta anche gli eventuali errori di validazione precedenti
        $this->resetValidation();
        // Imposta il valore di default
        $this->role = UserRole::BANCALE->value;
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

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        // Accesso sicuro al valore dell'Enum
        $this->role = $user->role->value ?? UserRole::BANCALE->value;
        $this->type = $user->type->value ?? null;
        $this->license_number = $user->license_number;

        $this->editing = true;
    }

    public function save(): void
    {
        // 1. Regole di validazione dinamiche
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'type' => 'nullable|string',
            'license_number' => 'nullable|string|max:255',

            // Email: deve essere univoca, ignorando l'utente corrente in modifica
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                ValidationRule::unique('users', 'email')->ignore($this->userId)
            ],

            // Password: richiesta in creazione, facoltativa in modifica
            'password' => ($this->userId === 0)
                            ? 'required|string|min:8'
                            : 'nullable|string|min:8',
        ];

        $validatedData = $this->validate($rules);

        // 2. Prepara i dati per il salvataggio
        $data = [
            'name'           => $validatedData['name'],
            'email'          => $validatedData['email'],
            // Passiamo il valore della stringa; Eloquent si occuperà del cast
            'role'           => $validatedData['role'],
            'type'           => $validatedData['type'],
            'license_number' => $validatedData['license_number'],
        ];

        // Aggiunge la password solo se l'utente l'ha fornita (se non è vuota)
        if (!empty($validatedData['password'])) {
            // Il modello si occuperà dell'hashing grazie al cast 'password' => 'hashed'
            $data['password'] = $validatedData['password'];
        }

        // 3. Esecuzione salvataggio
        if ($this->userId > 0) {
            User::findOrFail($this->userId)->update($data);
            $message = 'Utente modificato con successo!';
        } else {
            User::create($data);
            $message = 'Nuovo utente creato con successo!';
        }

        // 4. Conclusione
        $this->resetForm();
        // Usa la session flash standard per il messaggio di successo
        $this->notify($message);
    }

    // ===================================================================
    // AZIONI DI CANCELLAZIONE
    // ===================================================================

    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questo utente?',
            'confirmEvent' => 'confirmDeleteUser',
            'payload'      => $id,
        ]);
    }

    // Metodo chiamato direttamente dal bottone (con conferma JavaScript nel Blade)
    #[On('confirmDeleteUser')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload) ? ($payload['id'] ?? $payload[0] ?? null) : $payload;

        if ($id) {
            User::findOrFail($id)->delete();
            $this->notify('Utente eliminato con successo.');
        }
    }


    // ===================================================================
    // AZIONI DI QUERY
    // ===================================================================

    public function setSort(string $field): void
    {
        if ($field === $this->sortField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ===================================================================
    // RENDER
    // ===================================================================

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            // Ordine predefinito o da sorting
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.crud.user-manager', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'licenseTypes' => LicenseType::cases(),
        ]);
    }
}
