<?php
namespace App\Specifications;

use App\Contracts\MatrixSpecificationInterface;
use App\Enums\DayType;

class TurnConstraintSpecification implements MatrixSpecificationInterface
{
    public function isSatisfiedBy(array $license, array $work): bool
{
    // Forza il valore a stringa per il confronto
    $turn = $license['turn'];
    $turnValue = ($turn instanceof DayType) ? $turn->value : $turn;

    if ($turnValue === DayType::FULL->value) return true;

    $workTime = $this->extractWorkTime($work);
    
    // Recupera le soglie con dei fallback se la config è vuota
    $morningEnd = config('app_settings.matrix.morning_end', '13:00');
    $afternoonStart = config('app_settings.matrix.afternoon_start', '13:00');

    if ($turnValue === DayType::MORNING->value) {
        return $workTime <= $morningEnd;
    }

    if ($turnValue === DayType::AFTERNOON->value) {
        return $workTime >= $afternoonStart;
    }

    return true;
}

    private function extractWorkTime(array $work): string {
        return substr($work['timestamp'] ?? '00:00:00', 11, 5);
    }
}