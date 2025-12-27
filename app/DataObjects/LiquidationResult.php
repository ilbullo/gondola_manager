<?php

namespace App\DataObjects;

use App\Helpers\Format;
use Livewire\Wireable;

class LiquidationResult implements Wireable
{
    public function __construct(
        public array $counts = [
            'n' => 0, 'x' => 0, 'p' => 0, 'shared' => 0
        ],
        public array $money = [
            'valore_x' => 0.0,
            'wallet_diff' => 0.0,
            'bancale' => 0.0,
            'netto' => 0.0
        ],
        public array $lists = [
            'agencies' => [],
            'shared_vouchers' => []
        ]
    ) {}

    /**
     * Metodi "Presenter" per l'utilizzo diretto nelle View Blade.
     * Es: {{ $liquidation->netto() }}
     */
    public function netto(): string 
    {
        return Format::currency($this->money['netto'],true,false);
    }

    public function valoreX(): string 
    {
        return Format::currency($this->money['valore_x']);
    }

    public function walletDiffFormatted(): string 
    {
        return Format::currency($this->money['wallet_diff']);
    }

    /**
     * Helper per la View: Formatta i parametri per la rotta di stampa.
     * Centralizza la logica di formattazione usando l'helper Format.
     */
    public function toPrintParams(array $extraData = []): array
    {
        return array_merge([
            // Conteggi formattati come numeri interi
            'n_count'         => Format::number($this->counts['n']),
            'x_count'         => Format::number($this->counts['x']),
            'p_count'         => Format::number($this->counts['p']),
            'shared_ff'       => Format::number($this->counts['shared']),
            'netto_raw'       => (float) $this->money['netto'],
            
            // Valori economici (senza simbolo per i parametri URL/Stampa se preferito)
            'x_amount'        => Format::currency($this->money['valore_x'], false),
            'wallet_diff'     => Format::currency($this->money['wallet_diff'], false),
            'bancale'         => Format::currency($this->money['bancale'], false),
            'final'           => Format::currency($this->money['netto'], false),
            
            // Liste e metadati
            'shared_vouchers' => $this->lists['shared_vouchers'],
            'agencies'        => $this->lists['agencies'],
            'generated_at'    => Format::dateTime(now()),
        ], $extraData);
    }

    /**
     * Interfaccia Wireable: Trasforma l'oggetto per il frontend.
     */
    public function toLivewire()
    {
        return [
            'counts' => $this->counts,
            'money'  => $this->money,
            'lists'  => $this->lists,
        ];
    }

    /**
     * Interfaccia Wireable: Ricostruisce l'oggetto dal frontend.
     */
    public static function fromLivewire($value)
    {
        return new static(
            $value['counts'] ?? [], 
            $value['money']  ?? [], 
            $value['lists']  ?? []
        );
    }

    // app/DataObjects/LiquidationResult.php

/**
 * Calcola i totali aggregati per una collezione di risultati.
 * SOLID: La responsabilità di sommare le liquidazioni spetta al dominio dei dati.
 */
public static function aggregateTotals(iterable $liquidations): array
{
    $totals = [
        'n' => 0, 'x' => 0, 'p' => 0, 'shared' => 0, 'netto' => 0.0
    ];

    foreach ($liquidations as $liq) {
        // Gestiamo sia oggetti DTO che array (se Livewire ha de-serializzato)
        $counts = is_array($liq) ? $liq['counts'] : $liq->counts;
        $money = is_array($liq) ? $liq['money'] : $liq->money;

        $totals['n']      += $counts['n'] ?? 0;
        $totals['x']      += $counts['x'] ?? 0;
        $totals['p']      += $counts['p'] ?? 0;
        $totals['shared'] += $counts['shared'] ?? 0;
        $totals['netto']  += (float) ($money['netto'] ?? 0);
    }

    return $totals;
}
}