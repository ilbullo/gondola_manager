<?php
namespace App\Specifications;

use App\Contracts\MatrixSpecificationInterface;
use App\Enums\WorkType;

class CashOnlySpecification implements MatrixSpecificationInterface
{
    public function isSatisfiedBy(array $license, array $work): bool
    {
        $onlyCash = $license['only_cash_works'] ?? false;
        if (!$onlyCash) return true;

        return ($work['value'] ?? '') !== WorkType::AGENCY->value;
    }
}