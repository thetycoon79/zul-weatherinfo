<?php
/**
 * Location repository implementation
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Repositories;

use Zul\Weather\Interfaces\LocationRepositoryInterface;
use Zul\Weather\Domain\Entities\Location;
use Zul\Weather\Domain\ValueObjects\Status;
use Zul\Weather\Support\Db;

class LocationRepository implements LocationRepositoryInterface
{
    private Db $db;
    private string $table;

    public function __construct(?Db $db = null)
    {
        $this->db = $db ?? new Db();
        $this->table = $this->db->getTableName('zul_weather_info');
    }

    public function findById(int $id): ?Location
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = %d",
            $id
        );

        $row = $this->db->getRow($sql);
        return $row ? $this->hydrate($row) : null;
    }

    public function findActiveById(int $id): ?Location
    {
        $sql = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = %d AND status = 'active'",
            $id
        );

        $row = $this->db->getRow($sql);
        return $row ? $this->hydrate($row) : null;
    }

    public function list(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = $this->buildWhereClause($filters);
        $orderBy = $this->buildOrderByClause($filters);

        $sql = "SELECT * FROM {$this->table} {$where} {$orderBy}";

        if ($limit > 0) {
            $sql = $this->db->prepare("{$sql} LIMIT %d OFFSET %d", $limit, $offset);
        }

        $rows = $this->db->getResults($sql);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function count(array $filters = []): int
    {
        $where = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) FROM {$this->table} {$where}";

        return (int) $this->db->getVar($sql);
    }

    public function insert(Location $location): int
    {
        $data = [
            'location' => $location->getLocation(),
            'description' => $location->getDescription(),
            'latitude' => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'created_by' => $location->getCreatedBy(),
            'status' => $location->getStatus()->value,
            'create_dt' => $location->getCreateDt()->format('Y-m-d H:i:s'),
            'modified_dt' => null,
        ];

        $result = $this->db->insert($this->table, $data);

        if ($result === false) {
            throw new \RuntimeException('Failed to insert location: ' . $this->db->lastError());
        }

        return $result;
    }

    public function update(Location $location): bool
    {
        if (!$location->getId()) {
            throw new \InvalidArgumentException('Cannot update location without ID');
        }

        $data = [
            'location' => $location->getLocation(),
            'description' => $location->getDescription(),
            'latitude' => $location->getLatitude(),
            'longitude' => $location->getLongitude(),
            'status' => $location->getStatus()->value,
            'modified_dt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];

        $result = $this->db->update(
            $this->table,
            $data,
            ['id' => $location->getId()]
        );

        return $result !== false;
    }

    public function delete(int $id): bool
    {
        $result = $this->db->delete($this->table, ['id' => $id]);
        return $result !== false;
    }

    private function hydrate(object $row): Location
    {
        return new Location(
            location: $row->location,
            latitude: (float) $row->latitude,
            longitude: (float) $row->longitude,
            createdBy: (int) $row->created_by,
            description: $row->description,
            status: Status::from($row->status),
            id: (int) $row->id,
            createDt: new \DateTimeImmutable($row->create_dt),
            modifiedDt: $row->modified_dt ? new \DateTimeImmutable($row->modified_dt) : null
        );
    }

    private function buildWhereClause(array $filters): string
    {
        $conditions = [];

        if (!empty($filters['status'])) {
            $conditions[] = $this->db->prepare('status = %s', $filters['status']);
        }

        if (!empty($filters['created_by'])) {
            $conditions[] = $this->db->prepare('created_by = %d', $filters['created_by']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = $this->db->prepare('(location LIKE %s OR description LIKE %s)', $search, $search);
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    private function buildOrderByClause(array $filters): string
    {
        $orderBy = $filters['orderby'] ?? 'create_dt';
        $order = strtoupper($filters['order'] ?? 'DESC');

        $allowedColumns = ['id', 'location', 'status', 'latitude', 'longitude', 'create_dt', 'modified_dt'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'create_dt';
        }

        if (!in_array($order, $allowedOrder, true)) {
            $order = 'DESC';
        }

        return "ORDER BY {$orderBy} {$order}";
    }
}
