<?php

namespace App\Livewire\Component;

use Livewire\Component;
use Livewire\Attributes\Computed;


class WorkCell extends Component
{
    public array $work = [];
    public bool $hasWork = false;  
    public int $licenseId = 0;
    public int $slot = 0; 
    
    
    public function mount(array $work, int $licenseId = 0, int $slot = 0)
{
    $this->work = $work;
    $this->licenseId = $licenseId;
    $this->slot = $slot;
    $this->hasWork = !empty($work) && !empty($work['value']); // ← CALCOLA QUI
}

#[Computed]
public function ariaLabel(): string
{
    if (empty($this->work)) {
        return 'Cella vuota';
    }

    $value = $this->work['value'] ?? '';

    $label = match ($value) {
        'A' => 'Agenzia ' . ($this->work['agency_code'] ?? $this->work['agencyName'] ?? 'sconosciuta'),
        'X' => 'Extra',
        'P' => 'Presenza',
        'N' => 'Notte',
        default => 'Lavoro tipo ' . $value,
    };

    if (!empty($this->work['voucher'])) {
        $label .= ', con voucher ' . $this->work['voucher'];
    }

    if ($this->work['excluded'] ?? false) {
        $label .= ', fisso alla licenza, escluso dal conteggio totale';
    } elseif ($this->work['shared_from_first'] ?? false) {
        $label .= ', ripartito dal primo turno';
    }

    return $label;
}
/*
    #[Computed]
    public function textColor(): string
    {
        return match (true) {
            $this->work['excluded'] ?? false         => 'text-red-700 font-bold',
            $this->work['shared_from_first'] ?? false   => 'text-emerald-700 font-bold',
            ($this->work['value'] ?? '') === 'A'      => 'text-blue-700',
            ($this->work['value'] ?? '') === 'X'      => 'text-purple-700',
            in_array($this->work['value'] ?? '', ['P', 'N']) => 'text-orange-700',
            default                                   => 'text-gray-900',
        };
    }
*/
    #[Computed]
    public function badge(): ?array
    {
        return match (true) {
            $this->work['excluded'] ?? false       => ['label' => 'F', 'class' => 'bg-red-100 text-red-700'],
            $this->work['shared_from_first'] ?? false => ['label' => 'R', 'class' => 'bg-emerald-100 text-emerald-700'],
            default                                 => null,
        };
    }

    public function render()
    {
        return view('livewire.component.work-cell');
    }
}