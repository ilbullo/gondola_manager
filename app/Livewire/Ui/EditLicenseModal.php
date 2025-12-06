<?php

namespace App\Livewire\Ui;

use App\Models\LicenseTable;
use Livewire\Attributes\On;
use Livewire\Component;

class EditLicenseModal extends Component
{
    /**
     * Controlla la visibilità della modale.
     * True = modale aperta, False = chiusa.
     */
    public bool $show = false;

    /**
     * ID della riga LicenseTable da modificare.
     * Null se nessuna riga è selezionata.
     */
    public ?int $licenseTableId = null;

    /**
     * Indica il tipo di turno della licenza.
     * Esempi: 'full', 'morning', 'afternoon'
     */
    public string $turn = 'full';

    /**
     * Indica se la licenza deve accettare solo lavori "cash".
     */
    public bool $onlyCashWorks = false;

    // ===================================================================
    // Event Handlers
    // ===================================================================

    /**
     * Listener Livewire: apre la modale di modifica.
     *
     * L’evento riceve:
     * - ['id' => <ID>]     oppure
     * - [<ID>] come array semplice
     *
     * @param mixed $payload Dati inviati dall'evento Livewire
     */
    #[On('openEditLicense')]
    public function open($payload)
    {
        // Recupera l'ID in modo flessibile (sia come array associativo che indicizzato)
        $id = $payload['id'] ?? $payload[0] ?? null;
        if (!$id) return;

        // Carica la riga da modificare (404 se non esiste)
        $license = LicenseTable::findOrFail($id);

        // Popola lo stato del component con i valori correnti
        $this->licenseTableId  = $id;
        $this->turn            = $license->turn ?? 'full';
        $this->onlyCashWorks   = (bool) $license->only_cash_works;

        // Apre la modale
        $this->show = true;
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

    /**
     * Salva le modifiche fatte alla licenza.
     * Aggiorna il record e chiude la modale.
     */
    public function save()
    {
        LicenseTable::where('id', $this->licenseTableId)->update([
            'turn'             => $this->turn,
            'only_cash_works'  => $this->onlyCashWorks,
        ]);

        // Chiude la modale
        $this->show = false;

        // Richiede al wrapper di aggiornare la tabella principale
        $this->dispatch('refreshTableBoard');
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Restituisce la vista Blade che rappresenta la modale.
     */
    public function render()
    {
        return view('livewire.ui.edit-license-modal');
    }
}
