<?php

namespace App\Exceptions;

use App\Exceptions\MatrixIntegrityException;

class CapacityOverflowException extends MatrixIntegrityException
{
    public function __construct(
        protected string $licenseNumber,
        protected int $assigned,
        protected int $capacity
    ) {
        parent::__construct("Licenza #{$licenseNumber}: Overflow Capacità.");
    }

    protected function getPayload(): array {
        return [
            'license'  => $this->licenseNumber,
            'assigned' => $this->assigned,
            'capacity' => $this->capacity,
        ];
    }

    protected function getView(): string { return 'errors.capacity-overflow'; }
    protected function getErrorCode(): string { return 'capacity_overflow'; }
}