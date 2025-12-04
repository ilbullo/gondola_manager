<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Enums\DayType;

class DayBadge extends Component
{
    public DayType $day;
    public function __construct(string $day)
    {
        $this->day = DayType::tryFrom($day) ?? DayType::FULL;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.day-badge');
    }
}
