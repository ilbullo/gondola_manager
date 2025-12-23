<?php

namespace App\Livewire\Crud;

use App\Models\Agency;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class AgencyManager extends Component
{
    use WithPagination;

    // Persistiamo la ricerca nell'URL per permettere il refresh della pagina
    #[Url(history: true)]
    public string $search = '';
    
    public bool $showCreateForm = false;
    public bool $showEditForm = false;
    public bool $showDeleted = false;

    // Form fields
    public ?string $name = null;
    public ?string $code = null;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'code' => [
                'required', 'string', 'max:4', 'regex:/^[A-Z0-9]+$/',
                'unique:agencies,code' . ($this->editingId ? ',' . $this->editingId : ''),
            ],
        ];
    }

    public function toggleCreateForm(): void
    {
        $this->showEditForm = false;
        $this->showCreateForm = !$this->showCreateForm;
        $this->resetForm();
    }

    public function toggleShowDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage(); // Importante per non restare su una pagina vuota
    }

    /**
     * Apre il modal di conferma eliminazione tramite evento Livewire.
     */
    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questa agenzia?',
            'confirmEvent' => 'confirmDeleteAgency',
            'payload'      => $id,
        ]);
    }

    public function create(): void
    {
        $this->validate();

        Agency::create([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        $this->afterMutation('Agenzia creata con successo.');
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

        Agency::findOrFail($this->editingId)->update([
            'name' => $this->name,
            'code' => $this->code,
        ]);

        $this->afterMutation('Agenzia aggiornata con successo.');
    }

    #[On('confirmDeleteAgency')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;

        if ($id) {
            Agency::findOrFail($id)->delete();
            $this->afterMutation('Agenzia eliminata con successo.');
        }
    }

    public function restore(int $id): void
    {
        Agency::withTrashed()->findOrFail($id)->restore();
        $this->afterMutation('Agenzia ripristinata.');
    }

    /**
     * Helper centralizzato per le operazioni post-modifica
     */
    private function afterMutation(string $message): void
    {
        cache()->forget('agencies_list'); // Sincronizza Sidebar
        $this->notify($message);
        $this->closeForms();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function closeForms(): void
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'code', 'editingId']);
    }

    private function notify(string $message, string $title = 'SUCCESSO'): void
    {
        // Usiamo l'invio parametri esplicito per Alpine.js
       $this->dispatch('notify', 
            message: $message, 
            title: $title, 
            type: 'success'
        );
    }

    public function render()
    {
        $agencies = Agency::query()
            ->when($this->search, function ($q) {
                $q->where(fn($sub) => 
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                );
            })
            ->when($this->showDeleted, fn($q) => $q->withTrashed())
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.crud.agency-manager', [
            'agencies' => $agencies,
        ]);
    }
}