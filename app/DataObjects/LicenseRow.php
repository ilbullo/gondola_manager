<?php
namespace App\DataObjects;

use Livewire\Wireable;

class LicenseRow implements Wireable
{
    public function __construct(
        public array $user,
        public int $id,
        public int $target_capacity,
        public bool $only_cash_works,
        public float $wallet,
        public array $worksMap,
        public int $slots_occupied = 0,
        public ?LiquidationResult $liquidation = null // Tipizzazione forte
    ) {}

    public function refresh(float $bancaleCost): void
    {
        $this->slots_occupied = count(array_filter($this->worksMap));
        $defaultAmount = (float) config('app_settings.works.default_amount', 90.0);
        
        $nCount = collect($this->worksMap)->where('value', 'N')->count();
        $walletDiff = ($nCount * $defaultAmount) - $this->wallet;

        // Il calcolo restituisce un oggetto LiquidationResult
        $this->liquidation = \App\Services\LiquidationService::calculate(
            $this->worksMap,
            $walletDiff,
            $bancaleCost
        );
    }

    public function toLivewire(): array
    {
        return [
            'user' => $this->user,
            'id' => $this->id,
            'target_capacity' => $this->target_capacity,
            'only_cash_works' => $this->only_cash_works,
            'wallet' => $this->wallet,
            'worksMap' => $this->worksMap,
            'slots_occupied' => $this->slots_occupied,
            'liquidation' => $this->liquidation?->toLivewire(),
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self(
            user: $value['user'],
            id: $value['id'],
            target_capacity: $value['target_capacity'],
            only_cash_works: $value['only_cash_works'],
            wallet: (float) $value['wallet'],
            worksMap: $value['worksMap'],
            slots_occupied: $value['slots_occupied'],
            // REIDRATAZIONE PROFONDA: Trasformiamo l'array di nuovo in LiquidationResult
            liquidation: isset($value['liquidation']) 
                ? LiquidationResult::fromLivewire($value['liquidation']) 
                : null,
        );
    }
}