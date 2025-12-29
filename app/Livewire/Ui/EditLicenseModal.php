<?php

namespace App\Livewire\Ui;

use App\Models\LicenseTable;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Class EditLicenseModal
 *
 * @package App\Livewire\Ui
 *
 * Gestisce la configurazione granulare delle impostazioni di una licenza in tabella.
 * Permette di modificare parametri critici come la tipologia di turno (Full/Mattina/Pomeriggio)
 * e i vincoli operativi (es. Only Cash) tramite un'interfaccia modale isolata.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Targeted Mutation: Isola la logica di aggiornamento delle proprietà della licenza,
 * evitando di appesantire il componente principale della tabella.
 * 2. State Mapping: Converte lo stato del database in proprietà reattive del componente
 * per permettere l'editing immediato.
 * 3. Event-Driven Lifecycle: Utilizza un sistema di trigger ('openEditLicense') per
 * attivarsi contestualmente al click sulla riga della tabella.
 * 4. Indirect UI Refresh: Dopo il salvataggio, comunica con il sistema tramite
 * 'refreshTableBoard' per garantire che i cambiamenti visivi siano propagati globalmente.
 *
 * FLUSSO OPERATIVO:
 * [WorkAssignmentTable] -> dispatch('openEditLicense', id) -> [EditLicenseModal] ->
 * Update Record -> dispatch('refreshTableBoard') -> [Table Refresh]
 */

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
