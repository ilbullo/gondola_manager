<?php
namespace App\DataObjects;

use Livewire\Wireable;
use Illuminate\Support\Collection;

class MatrixTable implements Wireable
{
    public Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function refreshAll(float $bancaleCost): void
    {
        foreach ($this->rows as $row) {
            $row->refresh($bancaleCost);
        }
    }

    public function toLivewire(): array
    {
        return ['rows' => $this->rows->map->toLivewire()->toArray()];
    }

    public static function fromLivewire($value): self
    {
        $rows = collect($value['rows'])->map(fn($item) => LicenseRow::fromLivewire($item));
        return new self($rows);
    }
}