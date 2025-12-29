<?php 

namespace App\Contracts;

interface MatrixSpecificationInterface
{
    /**
     * Verifica se il lavoro può essere assegnato alla licenza (array).
     */
    public function isSatisfiedBy(array $license, array $work): bool;
}