<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class LicenseResource
 *
 * @package App\Http\Resources
 *
 * Questa risorsa agisce come strato di trasformazione (API Transformation Layer) tra i modelli Eloquent
 * e l'interfaccia utente. Centralizza la struttura dei dati inviati al frontend, garantendo
 * che la tabella delle assegnazioni riceva una mappa degli slot consistente e performante.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Data Normalization: Converte i tipi di dato del database (es. date, booleani) in formati
 * standard per il frontend (stringhe ISO, integer).
 * 2. Relationship Management: Utilizza il caricamento condizionale (whenLoaded) per prevenire
 * l'invio di dati non necessari e ottimizzare il consumo di memoria.
 * 3. Business Logic Exposure: Espone calcoli complessi definiti nel modello (Accessor) come
 * proprietà semplici ('target_capacity', 'wallet', 'worksMap').
 * 4. Graceful Fallback: Fornisce una struttura di default per la mappa degli slot (worksMap)
 * anche in assenza di relazioni caricate, evitando errori di rendering in tabella.
 *
 * ESEMPIO DI UTILIZZO NEL SERVICE:
 * return LicenseResource::collection($licenses)->resolve();
 */

class LicenseResource extends JsonResource
{
    /**
     * Trasforma la risorsa LicenseTable in un array per la risposta JSON API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            // Identificatori
            'id'                => $this->id,
           // 'license_table_id'  => $this->id, // Mantenuto per retrocompatibilità con frontend esistente

            // Informazioni sull'utente assegnatario
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id'              => $this->user->id,
                    'name'            => $this->user->name,
                    'license_number'  => $this->user->license_number,
                ];
            }),

            // Dati della licenza
            'date'              => $this->date?->toDateString(),
            'turn'              => $this->turn,
            'only_cash_works'   => $this->only_cash_works,

            // Calcoli aggregati (gestiti dal model tramite accessor)
            'target_capacity'   => $this->target_capacity,     // Somma slots_occupied dei lavori
            'slots_occupied'    => $this->slots_occupied,      // Equivalente a target_capacity (se dati coerenti)
            'wallet'            => $this->wallet,              // Somma amount per lavori con value = 'N'

            // Mappa completa degli slot (1-based), con validazione già eseguita nel model
            'worksMap' => $this->whenLoaded('works', function () {
                // Se le relazioni works non sono caricate, getWorksMapAttribute lancerebbe errore su collezione vuota
                // ma con whenLoaded evitiamo problemi
                return $this->works_map;
            }, function () {
                // Fallback sicuro: array vuoto con tutti slot null
                $totalSlots = config('app_settings.matrix.total_slots', 25);
                return array_fill(1, $totalSlots, null);
            }),
        ];
    }
}
