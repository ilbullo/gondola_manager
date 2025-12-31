<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CapacityOverflowException extends Exception
{
    protected $licenseNumber;
    protected $assigned;
    protected $capacity;

    public function __construct(
        string $licenseNumber,
        int $assigned,
        int $capacity,
        string $message = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->licenseNumber = $licenseNumber;
        $this->assigned       = $assigned;
        $this->capacity       = $capacity;

        $defaultMessage = "Licenza #{$licenseNumber}: Overflow! " .
                          "Assegnati {$assigned} lavori su una capacità di {$capacity}.";

        parent::__construct($message ?? $defaultMessage, $code, $previous);
    }

    /**
     * Renderizza una view Blade personalizzata quando l'eccezione viene lanciata
     */
    public function render(Request $request): Response
    {
        // Se è una richiesta API → restituisci JSON
        if ($request->expectsJson() || $request->is('api/*') || $request->header('X-Livewire')) {
            return Response::json([
                'error'   => 'capacity_overflow',
                'message' => $this->getMessage(),
                'license' => $this->licenseNumber,
                'assigned' => $this->assigned,
                'capacity' => $this->capacity,
            ], 422); // o 400/409 a tua scelta
        }

        // Altrimenti → mostra una view Blade custom
        return response()->view('errors.capacity-overflow', [
            'exception'   => $this,
            'message'     => $this->getMessage(),
            'license'     => $this->licenseNumber,
            'assigned'    => $this->assigned,
            'capacity'    => $this->capacity,
        ], 422);
    }

    /**
     * Opzionale: aggiungi contesto extra nei log
     */
    public function context(): array
    {
        return [
            'license_number' => $this->licenseNumber,
            'assigned_works' => $this->assigned,
            'max_capacity'   => $this->capacity,
        ];
    }
}