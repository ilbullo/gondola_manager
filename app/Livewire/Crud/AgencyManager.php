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

    /**
     * Restituisce le regole di validazione dinamica per il form.
     *
     * La regola "unique" per il campo "code" viene adattata automaticamente
     * nel caso di modifica, permettendo al record attuale di mantenere il proprio codice.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'code' => [
                'required',
                'string',
                'max:4',
                'regex:/^[A-Z0-9]+$/',
                'unique:agencies,code' . ($this->editingId ? ',' . $this->editingId : ''),
            ],
        ];
    }

    /**
     * Metodo eseguito alla prima inizializzazione del componente.
     * Ripulisce i campi del form e assicura uno stato coerente della UI.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->resetForm();
    }

    /**
     * Mostra o nasconde il form di creazione.
     * Chiude automaticamente il form di modifica e resetta i campi.
     *
     * @return void
     */
    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->showEditForm = false;
        $this->resetForm();
    }

    /**
     * Alterna la visualizzazione dei record eliminati (soft delete).
     * Al cambio resetta la paginazione per evitare pagine vuote.
     *
     * @return void
     */
    public function toggleShowDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    /**
     * Crea una nuova agenzia dopo validazione.
     * Mostra una notifica di successo e chiude i form.
     *
     * @return void
     */
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

    /**
     * Popola il form con i dati dell’agenzia selezionata per la modifica.
     *
     * @param int $id
     * @return void
     */
    public function edit(int $id): void
    {
        $agency = Agency::findOrFail($id);

        $this->editingId = $id;
        $this->name = $agency->name;
        $this->code = $agency->code;
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    /**
     * Aggiorna un’agenzia esistente dopo validazione.
     * Notifica l’utente e chiude i moduli di editing.
     *
     * @return void
     */
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

    /**
     * Elimina un’agenzia (soft delete).
     * Metodo collegato a un evento Livewire tramite #[On].
     *
     * Il payload può essere un array o un valore singolo.
     *
     * @param mixed $payload
     * @return void
     */
    #[On('confirmDeleteAgency')]
    public function delete(mixed $payload): void
    {
        $id = is_array($payload) ? ($payload['id'] ?? $payload[0] ?? null) : $payload;

        if ($id) {
            Agency::findOrFail($id)->delete();
            $this->notify('Agenzia eliminata con successo.');
        }
    }

    /**
     * Ripristina un’agenzia eliminata (soft delete).
     *
     * @param int $id
     * @return void
     */
    public function restore(int $id): void
    {
        Agency::withTrashed()->findOrFail($id)->restore();
        $this->notify('Agenzia ripristinata con successo.');
    }

    /**
     * Apre il modal di conferma eliminazione tramite evento Livewire.
     *
     * @param int $id
     * @return void
     */
    public function confirmDelete(int $id): void
    {
        $this->dispatch('openConfirmModal', [
            'message'      => 'Eliminare definitivamente questa agenzia?',
            'confirmEvent' => 'confirmDeleteAgency',
            'payload'      => $id,
        ]);
    }

    /**
     * Reset della paginazione quando cambia il campo di ricerca.
     *
     * @return void
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Chiude entrambi i form (creazione e modifica).
     * Ripulisce campi, errori e stato interno.
     *
     * @return void
     */
    public function closeForms(): void
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
        $this->resetForm();
        $this->resetErrorBag();
    }

    /**
     * Reset dei campi del form a valori nulli.
     *
     * @return void
     */
    public function resetForm(): void
    {
        $this->name = null;
        $this->code = null;
        $this->editingId = null;
    }

    /**
     * Flash message per notifiche lato sessione.
     *
     * @param string $message
     * @return void
     */
    private function notify(string $message): void
    {
        session()->flash('message', $message);
    }

    /**
     * Renderizza il componente con i risultati filtrati e paginati.
     * Include:
     *  - ricerca dinamica
     *  - visualizzazione opzionale dei record eliminati
     *  - impaginazione
     *
     * @return \Illuminate\View\View
     */
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
