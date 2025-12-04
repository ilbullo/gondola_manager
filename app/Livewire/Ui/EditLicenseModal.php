<?php

namespace App\Livewire\Ui;

use App\Models\LicenseTable;
use Livewire\Attributes\On;
use Livewire\Component;

class EditLicenseModal extends Component
{
    public bool $show = false;
    public ?int $licenseTableId = null;
    public string $turn = 'full';
    public bool $onlyCashWorks = false;

    #[On('openEditLicense')]
    public function open($payload)
    {
        $id = $payload['id'] ?? $payload[0] ?? null;
        if (!$id) return;

        $license = LicenseTable::findOrFail($id);

        $this->licenseTableId = $id;
        $this->turn = $license->turn ?? 'full';
        $this->onlyCashWorks = (bool) $license->only_cash_works;
        $this->show = true; // ← Questo apre la modale
    }

    public function save()
    {
        LicenseTable::where('id', $this->licenseTableId)->update([
            'turn' => $this->turn,
            'only_cash_works' => $this->onlyCashWorks,
        ]);

        $this->show = false;
        $this->dispatch('refreshTableBoard');
    }

    public function render()
    {
        return view('livewire.ui.edit-license-modal');
    }
}