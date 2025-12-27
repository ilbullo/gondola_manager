<?php

namespace App\DataObjects;

use Livewire\Wireable;

class LiquidationResult implements Wireable
{
    public function __construct(
        public array $counts = [],
        public array $money = [],
        public array $lists = []
    ) {}

    /**
     * Helper per la View: Formatta i parametri per la rotta di stampa.
     * Se cambi una chiave qui, si aggiorna ovunque.
     */
    public function toPrintParams(array $extraData = []): array
    {
        return array_merge([
            'n_count'         => $this->counts['n'],
            'x_count'         => $this->counts['x'],
            'p_count'         => $this->counts['p'],
            'x_amount'        => $this->money['valore_x'],
            'wallet_diff'     => number_format($this->money['wallet_diff'], 2, ',', '.'),
            'shared_ff'       => $this->counts['shared'],
            'shared_vouchers' => $this->lists['shared_vouchers'],
            'agencies'        => $this->lists['agencies'],
            'bancale'         => number_format($this->money['bancale'], 2, ',', '.'),
            'final'           => number_format($this->money['netto'], 2, ',', '.'),
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
        return new static($value['counts'], $value['money'], $value['lists']);
    }
}