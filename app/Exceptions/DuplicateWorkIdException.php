<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DuplicateWorkIdException extends Exception
{
    protected $licenseNumber;

    public function __construct(
        string $licenseNumber,
        string $message = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->licenseNumber = $licenseNumber;

        $defaultMessage = "Licenza #{$licenseNumber}: Rilevati ID lavoro duplicati nella stessa riga.";

        parent::__construct($message ?? $defaultMessage, $code, $previous);
    }

    /**
     * Renderizza JSON per API/Livewire o view Blade per web
     */
    public function render(Request $request): Response
    {
        // Per richieste API o Livewire → JSON
        if ($request->expectsJson() || $request->is('api/*') || $request->header('X-Livewire')) {
            return Response::json([
                'error'         => 'duplicate_work_id',
                'message'       => $this->getMessage(),
                'license'       => $this->licenseNumber,
            ], 422);
        }

        // Per richieste web normali → view dedicata
        return response()->view('errors.duplicate-work-id', [
            'message' => $this->getMessage(),
            'license' => $this->licenseNumber,
        ], 422);
    }

    /**
     * Contesto per logging
     */
    public function context(): array
    {
        return [
            'license_number' => $this->licenseNumber,
        ];
    }
}