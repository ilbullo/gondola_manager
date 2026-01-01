<?php

namespace App\Exceptions;

use App\Exceptions\MatrixIntegrityException;

class ShiftMismatchException extends MatrixIntegrityException
{
    public function __construct(
        protected string $licenseNumber,
        protected string $expectedShift,
        protected string $foundShift,
        protected string $timestamp
    ) {
        parent::__construct("Licenza #{$licenseNumber}: Errore Turno! Atteso {$expectedShift}, trovato {$foundShift}.");
    }

    protected function getPayload(): array {
        return [
            'license'        => $this->licenseNumber,
            'expected_shift' => $this->expectedShift,
            'found_shift'    => $this->foundShift,
            'timestamp'      => $this->timestamp,
        ];
    }

    protected function getView(): string { return 'errors.shift-mismatch'; }
    protected function getErrorCode(): string { return 'shift_mismatch'; }
}