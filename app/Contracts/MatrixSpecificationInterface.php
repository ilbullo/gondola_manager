<?php 

namespace App\Contracts;

/**
 * Interface MatrixSpecificationInterface
 * * Definisce il contratto per le regole di validazione (Specification Pattern) 
 * applicate alla distribuzione dei lavori nella matrice delle licenze.
 * * @package App\Contracts
 */
interface MatrixSpecificationInterface
{
    /**
     * Verifica se un determinato lavoro soddisfa i criteri per essere assegnato a una licenza.
     * * Implementa la logica di business per convalidare l'accoppiamento tra un'entità Licenza
     * (espressa come array di dati MatrixData) e un'entità Lavoro (Work).
     *
     * @param array{
     * id: int, 
     * licenseNumber: string, 
     * targetCapacity: int, 
     * onlyCashWorks: bool, 
     * worksMap: array, 
     * wallet: float
     * } $license Dati della licenza estratti dal MatrixData DTO.
     * * @param array{
     * id: int|string, 
     * value: string, 
     * excluded: bool, 
     * shared_from_first: bool, 
     * agency_code: ?string
     * } $work Dati del lavoro da sottoporre a verifica.
     * * @return bool True se il lavoro può essere assegnato, False in caso contrario.
     */
    public function isSatisfiedBy(array $license, array $work): bool;
}