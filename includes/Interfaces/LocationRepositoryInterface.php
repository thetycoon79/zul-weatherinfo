<?php
/**
 * Location repository interface
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Interfaces;

use Zul\Weather\Domain\Entities\Location;

interface LocationRepositoryInterface
{
    /**
     * Find location by ID
     */
    public function findById(int $id): ?Location;

    /**
     * Find active location by ID
     */
    public function findActiveById(int $id): ?Location;

    /**
     * List locations with optional filters
     *
     * @param array $filters Optional filters (status, search)
     * @param int $limit Number of results
     * @param int $offset Offset for pagination
     * @return Location[]
     */
    public function list(array $filters = [], int $limit = 20, int $offset = 0): array;

    /**
     * Count locations matching filters
     */
    public function count(array $filters = []): int;

    /**
     * Insert a new location
     *
     * @return int The new location ID
     */
    public function insert(Location $location): int;

    /**
     * Update an existing location
     */
    public function update(Location $location): bool;

    /**
     * Delete a location by ID
     */
    public function delete(int $id): bool;
}
