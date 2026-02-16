<?php

namespace App\Services;
use App\DataObjects\LiquidationResult;

/**
 * Class LiquidationService
 *
 * @package App\Services
 *
 * Gestore dei calcoli finanziari e dei conguagli di fine turno.
 * Elabora la distribuzione dei lavori e lo stato del wallet per determinare
 * la posizione economica netta del conducente rispetto alla cassa centrale.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Financial Computation: Applica le regole di business per sommare i ricavi
 * da contanti (X) e detrarre i costi fissi o di gestione (Bancale).
 * 2. Data Decoupling: Utilizza l'oggetto 'LiquidationResult' (DTO) per restituire
 * i calcoli, evitando che la UI dipenda direttamente dalla logica di calcolo.
 * 3. Categorization Logic: Segmenta i lavori in base alla loro rilevanza fiscale
 * (Standard vs Shared vs Agencies).
 *
 * FORMULA DEL NETTO:
 * $Netto = \sum(CashX) + \Delta Wallet - CostoBancale$
 */

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
        $pWorks = $worksColl->where('value','P');

        // 2. Calcolo Economico Reale (Solo Cassa di oggi)
        //$valoreX = $cashXStandard->count() * $unitPrice;
        $valoreX = $cashXStandard->sum('amount');

        // Netto = (X oggi) + (Conguaglio Wallet) - (Costo Bancale)
        $nettoOggi = round( ($valoreX + $walletDifference - $bancaleCost), 2);

        return new LiquidationResult(
            counts: [
                'n' => $noli->count(),
                'x' => $cashXStandard->count(),
                'p' => $pWorks->count(),
                'shared' => $sharedFF->count(),
                'agencies' => $agencies->count(),
            ],
            money: [
                'valore_x' => $valoreX,
                'wallet_diff' => $walletDifference,
                'bancale' => $bancaleCost,
                'netto' => $nettoOggi,
            ],
            lists: [
                'shared_vouchers' => $sharedFF->pluck('voucher')->filter()->toArray(),
                'agencies' => $agencies
                                ->groupBy('agency')
                                ->map(fn($items) => $items->pluck('voucher'))
                                ->toArray(),
            ]
        );
    }
}
