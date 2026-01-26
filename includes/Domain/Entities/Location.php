<?php
/**
 * Location entity
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Domain\Entities;

use Zul\Weather\Domain\ValueObjects\Status;

class Location
{
    private ?int $id;
    private string $location;
    private ?string $description;
    private float $latitude;
    private float $longitude;
    private int $createdBy;
    private Status $status;
    private \DateTimeImmutable $createDt;
    private ?\DateTimeImmutable $modifiedDt;

    public function __construct(
        string $location,
        float $latitude,
        float $longitude,
        int $createdBy,
        ?string $description = null,
        ?Status $status = null,
        ?int $id = null,
        ?\DateTimeImmutable $createDt = null,
        ?\DateTimeImmutable $modifiedDt = null
    ) {
        $this->validateCoordinates($latitude, $longitude);

        $this->id = $id;
        $this->location = $location;
        $this->description = $description;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->createdBy = $createdBy;
        $this->status = $status ?? Status::ACTIVE;
        $this->createDt = $createDt ?? new \DateTimeImmutable();
        $this->modifiedDt = $modifiedDt;
    }

    private function validateCoordinates(float $lat, float $lon): void
    {
        if ($lat < -90 || $lat > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }
        if ($lon < -180 || $lon > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getCreateDt(): \DateTimeImmutable
    {
        return $this->createDt;
    }

    public function getModifiedDt(): ?\DateTimeImmutable
    {
        return $this->modifiedDt;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withLocation(string $location): self
    {
        $clone = clone $this;
        $clone->location = $location;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function withDescription(?string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function withCoordinates(float $latitude, float $longitude): self
    {
        $this->validateCoordinates($latitude, $longitude);
        $clone = clone $this;
        $clone->latitude = $latitude;
        $clone->longitude = $longitude;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function withStatus(Status $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        $clone->modifiedDt = new \DateTimeImmutable();
        return $clone;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'location' => $this->location,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'created_by' => $this->createdBy,
            'status' => $this->status->value,
            'create_dt' => $this->createDt->format('Y-m-d H:i:s'),
            'modified_dt' => $this->modifiedDt?->format('Y-m-d H:i:s'),
        ];
    }
}
