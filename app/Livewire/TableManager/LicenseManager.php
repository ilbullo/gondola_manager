<?php

namespace App\Livewire\TableManager;

use App\Models\{LicenseTable, User};
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class LicenseManager extends Component
{
    /**
     * Utenti disponibili (non ancora assegnati alla tabella di oggi).
     *
     * Ogni elemento contiene:
     * - id
     * - user_id
     * - name
     * - surname
     * - license
     *
     * @var array<int, array{id: int, user_id: int, name: string, surname: string, license: string|null}>
     */
    public array $availableUsers = [];

    /**
     * Utenti selezionati (presenti nella license_table con la data odierna).
     *
     * @var array<int, array{id: int, user_id: int, order: int, user: array{id: int, name: string, surname: string, license: string|null}}>
     */
    public array $selectedUsers = [];

    /** Messaggi di errore mostrati all’utente */
    public string $errorMessage = '';

    // ===================================================================
    // Lifecycle
    // ===================================================================

    /**
     * Inizializza il componente caricando gli utenti disponibili e selezionati.
     */
    public function mount(): void
    {
        $this->refreshData();
    }

    // ===================================================================
    // Public Actions
    // ===================================================================

    /**
     * Assegna un utente alla tabella licenze del giorno.
     *
     * - Recupera l’utente
     * - Calcola il prossimo ordine
     * - Crea la riga nella license_table
     * - Aggiorna i dati del componente
     */
    public function selectUser(int $userId): void
    {
        $this->dispatch('toggleLoading', true);

        $user = User::findOrFail($userId);

        LicenseTable::create([
            'user_id' => $user->id,
            'date'    => today(),
            'order'   => $this->getNextOrder(),
        ]);

        // Aggiorna entrambe le liste (disponibili + selezionati)
        $this->refreshData();

        $this->dispatch('toggleLoading', false);
    }

    /**
     * Rimuove un utente dalla tabella licenze del giorno.
     */
    public function removeUser(int $licenseTableId): void
    {
        $this->dispatch('toggleLoading', true);

        LicenseTable::findOrFail($licenseTableId)->delete();

        // L’utente torna tra i disponibili
        $this->refreshData();

        $this->dispatch('toggleLoading', false);
    }

    /**
     * Aggiorna l’ordine degli utenti assegnati.
     *
     * Metodo ottimizzato:
     * 1. Spostamento temporaneo degli ordini per evitare collisioni UNIQUE (date, order)
     * 2. Aggiornamento massivo tramite SQL CASE → una sola query
     * 3. Ricarica lista selezionati
     */
    public function updateOrder(array $orderedIds): void
    {
        // Evita elaborazioni inutili
        if (empty($orderedIds)) {
            return;
        }

        $this->dispatch('toggleLoading', true);

        // Costruisce la mappatura id → nuovo_ordine
        $orderMapping = [];
        foreach ($orderedIds as $index => $item) {
            if (!empty($item['value'])) {
                $orderMapping[$item['value']] = $index + 1;
            }
        }

        if (empty($orderMapping)) {
            $this->dispatch('toggleLoading', false);
            return;
        }

        DB::transaction(function () use ($orderMapping) {
            $ids = array_keys($orderMapping);

            $date = today();
                DB::table('license_table')
                    ->where('date', $date)
                    ->lockForUpdate()
                    ->get();

            // -----------------------------------------------------------
            // 1) Safe Zone: sposta temporaneamente tutti gli ordini in alto
            // -----------------------------------------------------------
            LicenseTable::whereIn('id', $ids)
                ->update(['order' => DB::raw('`order` + 100000')]);

            // -----------------------------------------------------------
            // 2) Aggiornamento massivo tramite CASE
            // -----------------------------------------------------------
            $cases = [];
            $params = [];

            foreach ($orderMapping as $id => $order) {
                $cases[] = "WHEN ? THEN ?";
                $params[] = $id;
                $params[] = $order;
            }

            // Parametri per WHERE IN
            $params = array_merge($params, $ids);

            $casesSql = implode(' ', $cases);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Aggiornamento in un’unica query
            $query = "UPDATE license_table
                      SET `order` = CASE id $casesSql END,
                          updated_at = NOW()
                      WHERE id IN ($placeholders)";

            DB::update($query, $params);

        });

        $this->loadSelectedUsers();
        $this->dispatch('toggleLoading', false);
        session()->flash('success', 'Ordine aggiornato con successo!');
    }

    /**
     * Conferma la selezione attuale delle licenze:
     * - verifica che ci siano utenti selezionati
     * - verifica che non superino il limite massimo
     * - invia evento al TableManager
     */
    public function confirm(): void
    {
        $this->dispatch('toggleLoading', true);

        if (empty($this->selectedUsers)) {
            $this->errorMessage = 'Seleziona almeno un utente prima di confermare.';
            $this->dispatch('toggleLoading', false);
            return;
        }

        if (count($this->selectedUsers) > config('constants.max_users_in_table')) {
            $this->errorMessage = 'Hai selezionato più licenze di quelle consentite.';
            $this->dispatch('toggleLoading', false);
            return;
        }

        $this->errorMessage = '';

        session()->flash('success', 'Selezione confermata con successo!');
        $this->dispatch('confirmLicenses');

        $this->dispatch('toggleLoading', false);
    }

    // ===================================================================
    // Private Helpers
    // ===================================================================

    /**
     * Ricarica entrambe le liste:
     * - availableUsers
     * - selectedUsers
     */
    private function refreshData(): void
    {
        $this->loadAvailableUsers();
        $this->loadSelectedUsers();
    }

    /**
     * Carica gli utenti non ancora assegnati alla tabella odierna.
     * Ottimizzato per evitare query inutili.
     */
    private function loadAvailableUsers(): void
    {
        // ID utenti già assegnati per la data di oggi
        $assignedUserIds = LicenseTable::whereDate('date', today())
            ->pluck('user_id');

        // Recupera gli utenti non assegnati
        $this->availableUsers = User::whereNotIn('id', $assignedUserIds)
            ->orderBy('license_number')
            ->get()
            ->map(fn($user) => [
                'id'             => $user->id,
                'name'           => $user->name,
                'surname'        => $user->surname ?? '',
                'license_number' => $user->license_number,
                'full_name'      => trim("{$user->name} {$user->surname}"),
            ])
            ->toArray();
    }

    /**
     * Carica gli utenti attualmente nella license_table ordinati per order.
     */
    private function loadSelectedUsers(): void
    {
        $this->selectedUsers = LicenseTable::whereDate('date', today())
            ->with('user')
            ->orderBy('order')
            ->get()
            ->filter(fn($lt) => $lt->user !== null) // sicurezza extra
            ->map(fn($lt) => [
                'id'       => $lt->id,
                'user_id'  => $lt->user_id,
                'order'    => $lt->order,
                'user'     => [
                    'id'        => $lt->user->id,
                    'name'      => $lt->user->name,
                    'surname'   => $lt->user->surname ?? '',
                    'license'   => $lt->user->license_number,
                    'full_name' => trim("{$lt->user->name} " . ($lt->user->surname ?? '')),
                ],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Restituisce il prossimo valore disponibile per `order`.
     */
    private function getNextOrder(): int
    {
        return LicenseTable::whereDate('date', today())->max('order') + 1 ?? 1;
    }

    // ===================================================================
    // Render
    // ===================================================================

    /**
     * Renderizza la view associata al componente.
     */
    public function render()
    {
        return view('livewire.table-manager.license-manager');
    }
}
