<?php 

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

abstract class MatrixIntegrityException extends Exception
{
    /**
     * Dati specifici dell'errore da passare alla view/JSON
     */
    abstract protected function getPayload(): array;

    /**
     * Nome della view Blade (es: 'errors.shift-mismatch')
     */
    abstract protected function getView(): string;

    /**
     * Codice identificativo dell'errore per le API
     */
    abstract protected function getErrorCode(): string;

    public function render(Request $request)
    {
        $payload = array_merge([
            'message' => $this->getMessage(),
        ], $this->getPayload());

        if ($request->expectsJson() || $request->header('X-Livewire')) {
            return Response::json(array_merge(['error' => $this->getErrorCode()], $payload), 422);
        }

        return response()->view($this->getView(), $payload, 422);
    }

    public function context(): array
    {
        return $this->getPayload();
    }
}