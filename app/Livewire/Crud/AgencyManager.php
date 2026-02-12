<?php

namespace App\Livewire\Crud;

use App\Models\Agency;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Class AgencyManager
 *
 * @package App\Livewire\Crud
 *
 * Gestisce l'interfaccia amministrativa per l'anagrafica delle agenzie.
 * Implementa operazioni di creazione, lettura, aggiornamento e cancellazione (CRUD)
 * con supporto integrato per il ripristino dei record eliminati (Soft Deletes).
 *
 * RESPONSABILITÀ (SOLID):
 * 1. State Persistence: Utilizza l'attributo #[Url] per mantenere i filtri di ricerca sincronizzati
 * con la barra degli indirizzi, migliorando l'accessibilità e la navigazione.
 * 2. Data Validation: Implementa regole rigorose (Regex) per garantire la coerenza dei codici agenzia
 * e dei nomi, prevenendo errori di inserimento.
 * 3. Cache Management: Gestisce l'invalidazione della cache ('agencies_list') dopo ogni modifica,
 * assicurando che la Sidebar e gli altri componenti siano sempre aggiornati.
 * 4. User Feedback: Interagisce con componenti Alpine.js tramite dispatch di eventi ('notify')
 * per fornire notifiche push non bloccanti all'operatore.
 * 5. Soft Delete Management: Permette la visualizzazione e il ripristino selettivo delle agenzie
 * eliminate, garantendo la sicurezza del dato storico.
 *
 * FLUSSO DI LAVORO:
 * - Ricerca/Filtro -> Aggiornamento Query (Paginata) -> Rendering View.
 * - Mutazione (Create/Update/Delete) -> Invalidazione Cache -> Notifica UI -> Reset Form.
 */
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
    public ?string $colour = null;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'code' => [
                'required', 'string', 'max:4', 'regex:/^[A-Z0-9]+$/',
                'unique:agencies,code' . ($this->editingId ? ',' . $this->editingId : ''),
            ],
            'colour' => 'string|nullable',
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
            'colour' => $this->colour ?: null, // Salva null se vuoto
        ]);

        $this->afterMutation('Agenzia creata con successo.');
    }

    public function edit(int $id): void
    {
        $agency = Agency::findOrFail($id);
        $this->editingId = $id;
        $this->name = $agency->name;
        $this->code = $agency->code;
        $this->colour = $agency->colour;

        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function update(): void
    {
        $this->validate();

        Agency::findOrFail($this->editingId)->update([
            'name' => $this->name,
            'code' => $this->code,
            'colour' => $this->colour ?: null,
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
        $this->reset(['name', 'code', 'colour','editingId']);
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
