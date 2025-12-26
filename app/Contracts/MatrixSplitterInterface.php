<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface MatrixSplitterInterface
{
    public function execute(array|Collection $licenseTable): Collection;
}