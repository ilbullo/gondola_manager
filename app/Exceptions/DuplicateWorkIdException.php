<?php

namespace App\Exceptions;

use App\Exceptions\MatrixIntegrityException;

class DuplicateWorkIdException extends MatrixIntegrityException
{
    public function __construct(protected string $licenseNumber) {
        parent::__construct("Licenza #{$licenseNumber}: Rilevati ID lavoro duplicati.");
    }

    protected function getPayload(): array {
        return ['license' => $this->licenseNumber];
    }

    protected function getView(): string { return 'errors.duplicate-work-id'; }
    protected function getErrorCode(): string { return 'duplicate_id'; }
}