<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SlotsMismatchException extends Exception
{
    protected $licenseNumber;
    protected $declaredSlots;
    protected $actualCount;

    public function __construct(
        string $licenseNumber,
        int $declaredSlots,
        int $actualCount,
        string $message = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->licenseNumber   = $licenseNumber;
        $this->declaredSlots   = $declaredSlots;
        $this->actualCount     = $actualCount;

        $defaultMessage = "Licenza #{$licenseNumber}: Slots dichiarati ({$declaredSlots}) " .
                          "non corrispondono ai lavori presenti ({$actualCount}).";

        parent::__construct($message ?? $defaultMessage, $code, $previous);
    }

    /**
     * Renderizza una view Blade personalizzata o JSON per Livewire/API
     */
    public function render(Request $request): Response
    {
        // Per richieste API o Livewire → JSON
        if ($request->expectsJson() || $request->is('api/*') || $request->header('X-Livewire')) {
            return Response::json([
                'error'         => 'slots_mismatch',
                'message'       => $this->getMessage(),
                'license'       => $this->licenseNumber,
                'declared_slots'=> $this->declaredSlots,
                'actual_count'  => $this->actualCount,
            ], 422);
        }

        // Per richieste web normali → view dedicata
        return response()->view('errors.slots-mismatch', [
            'message'        => $this->getMessage(),
            'license'        => $this->licenseNumber,
            'declared_slots' => $this->declaredSlots,
            'actual_count'   => $this->actualCount,
        ], 422);
    }

    /**
     * Contesto per i log (opzionale ma utile)
     */
    public function context(): array
    {
        return [
            'license_number'   => $this->licenseNumber,
            'declared_slots'   => $this->declaredSlots,
            'actual_count'     => $this->actualCount,
        ];
    }
}