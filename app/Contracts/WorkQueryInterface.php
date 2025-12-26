<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface WorkQueryInterface
{
    public function allWorks(Collection|array $licenseTable): Collection;
    
    public function sharableWorks(Collection|array $licenseTable): Collection;

    public function sharableFirstAgencyWorks(Collection|array $licenseTable): Collection;

    public function sharableFirstCashWorks(Collection|array $licenseTable): Collection;

    public function pendingNWorks(Collection|array $licenseTable): Collection;

    public function pendingCashWorks(Collection|array $licenseTable): Collection;

    public function unsharableWorks(Collection|array $licenseTable): Collection;
    
    public function pendingMorningAgencyWorks(Collection|array $licenseTable): Collection;
    
    public function pendingAfternoonAgencyWorks(Collection|array $licenseTable): Collection;
    
    public function pendingPWorks(Collection|array $licenseTable): Collection;

    public function prepareMatrix(Collection|array $licenseTable): Collection;
}