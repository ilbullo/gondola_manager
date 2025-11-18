<?php

namespace App\Livewire\Crud;

use App\Models\Agency;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class AgencyManager extends Component
{
    use WithPagination;

    // === Filtri e UI ===
    public string $search = '';
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public bool $showDeleted = false;

    // === Form fields ===
    public ?string $name = null;
    public ?string $code = null;
    public ?int $editingId = null;

    // === Validazione dinamica (Livewire v3 best practice) ===
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                'unique:agencies,code' . ($this->editingId ? ',' . $this->editingId : ''),
            ],
        ];
    }

    // === Lifecycle ===
    public function mount(): void
    {
        $this->resetForm();
    }

    // === Azioni UI ===
    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->showEditForm = false;
        $this->resetForm();
    }

    public function toggleShowDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    // === CRUD Operations ===
    public function create(): void
    {
        $this->validate();

        Agency::create([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        $this->notify('Agenzia creata con successo.');
        $this->closeForms();
    }

    public function edit(int $id): void
    {
        $agency = Agency::findOrFail($id);

        $this->editingId = $id;
        $this->name = $agency->name;
        $this->code = $agency->code;
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function update(): void
    {
        $this->validate();

        $agency = Agency::findOrFail($this->editingId);
        $agency->update([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        $this->notify('Agenzia aggiornata con successo.');
        $this->closeForms();
    }

    #[On('confirmDeleteAgency')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload) ? ($payload['id'] ?? $payload[0] ?? null) : $payload;

        if ($id) {
            Agency::findOrFail($id)->delete();
            $this->notify('Agenzia eliminata con successo.');
        }
    }

    public function restore(int $id): void
    {
        Agency::withTrashed()->findOrFail($id)->restore();
        $this->notify('Agenzia ripristinata con successo.');
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questa agenzia?',
            'confirmEvent' => 'confirmDeleteAgency',
            'payload'      => $id,
        ]);
    }

    // === Hooks Livewire ===
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // === Metodi privati ausiliari ===
    public function closeForms(): void
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function resetForm(): void
    {
        $this->name = null;
        $this->code = null;
        $this->editingId = null;
    }

    private function notify(string $message): void
    {
        session()->flash('message', $message);
    }

    // === Render ===
    public function render()
    {
        $query = Agency::query();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            });
        }

        if ($this->showDeleted) {
            $query->withTrashed();
        }

        $agencies = $query->paginate(10);

        return view('livewire.crud.agency-manager', [
            'agencies' => $agencies,
        ]);
    }
}