<?php

namespace App\Exceptions;

use App\Exceptions\MatrixIntegrityException;

class SlotsMismatchException extends MatrixIntegrityException
{
    public function __construct(
        protected string $licenseNumber,
        protected int $declaredSlots,
        protected int $actualCount
    ) {
        parent::__construct("Licenza #{$licenseNumber}: Incoerenza conteggio slot.");
    }

    protected function getPayload(): array {
        return [
            'license'        => $this->licenseNumber,
            'declared_slots' => $this->declaredSlots,
            'actual_count'   => $this->actualCount,
        ];
    }

    protected function getView(): string { return 'errors.slots-mismatch'; }
    protected function getErrorCode(): string { return 'slots_mismatch'; }
}