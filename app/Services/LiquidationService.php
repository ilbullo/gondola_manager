<?php 

namespace App\Services;

class LiquidationService
{
    public static function calculate($works, $walletDifference, $bancaleCost = 0)
    {
        $unitPrice = config('app_settings.works.default_amount'); // Prezzo fisso per nolo/contante

        // 1. Filtriamo i lavori
        $worksColl = collect($works);
        
        $noli = $worksColl->where('value', 'N');
        $cashXStandard = $worksColl->where('value', 'X')->where('shared_from_first', 0);
        $sharedFF = $worksColl->where('value', 'X')->where('shared_from_first', 1);
        $agencies = $worksColl->where('value', 'A');

        // 2. Calcolo Economico Reale (Solo Cassa di oggi)
        //$valoreX = $cashXStandard->count() * $unitPrice;
        $valoreX = $cashXStandard->sum('amount');
        
        // Netto = (X oggi) + (Conguaglio Wallet) - (Costo Bancale)
        $nettoOggi = $valoreX + $walletDifference - $bancaleCost;

        return [
            'counts' => [
                'n' => $noli->count(),
                'x' => $cashXStandard->count(),
                'shared' => $sharedFF->count(),
                'agencies' => $agencies->count(),
            ],
            'money' => [
                'valore_x' => $valoreX,
                'wallet_diff' => $walletDifference,
                'bancale' => $bancaleCost,
                'netto' => $nettoOggi,
            ],
            'lists' => [
                'shared_vouchers' => $sharedFF->pluck('voucher')->filter()->toArray(),
                'agencies' => $agencies->mapWithKeys(fn($w) => [$w['agency'] => $w['voucher']])->toArray(),
            ]
        ];
    }
}