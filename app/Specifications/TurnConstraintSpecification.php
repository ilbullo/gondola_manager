<?php
namespace App\Specifications;

use App\Contracts\MatrixSpecificationInterface;
use App\Enums\DayType;

class TurnConstraintSpecification implements MatrixSpecificationInterface
{
    public function isSatisfiedBy(array $license, array $work): bool
    {
        $turn = $license['turn'] ?? DayType::FULL->value;
        if ($turn === DayType::FULL->value) return true;

        $workTime = $this->extractWorkTime($work);

        if ($turn === DayType::MORNING->value) {
            return $workTime <= config('app_settings.matrix.morning_end');
        }

        if ($turn === DayType::AFTERNOON->value) {
            return $workTime >= config('app_settings.matrix.afternoon_start');
        }

        return true;
    }

    private function extractWorkTime(array $work): string {
        return substr($work['timestamp'] ?? '00:00:00', 11, 5);
    }
}