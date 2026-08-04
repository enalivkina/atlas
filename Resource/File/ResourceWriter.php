<?php

declare(strict_types=1);

namespace Atlas\Resource\File;

use Atlas\Resource\Connection\Contract\DataBaseConnectionInterface;
use Atlas\Resource\Contract\ResourceWriterInterface;
use InvalidArgumentException;

final class ResourceWriter implements ResourceWriterInterface
{
    private ?string $resourceName = null;

    private ?array $accessibleFields = null;

    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
    ) {}

    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
    }

    public function setAccessibleFields(array $fieldNames): static
    {
        $this->accessibleFields = $fieldNames;

        return $this;
    }

    public function create(array $values): int
    {
        $this->validateSelfState();

        return $this->connection->insert($this->resourceName, $values);
    }

    public function update(int|string $id, array $values): int
    {
        $this->validateSelfState();
        $this->validateFieldsAccessible(array_keys($values));

        $values['id'] = (int) $id;

        foreach ($this->accessibleFields as $fieldName) {
            $values[$fieldName] = $values[$fieldName] ?? null;
        }

        return $this->connection->update(
            $this->resourceName,
            $values,
            ['id' => $id],
        );

    }

    public function patch(int|string $id, array $values): int
    {
        $this->validateSelfState();
        $this->validateFieldsAccessible(array_keys($values));

        $values['id'] = (int) $id;

        return $this->connection->update(
            $this->resourceName,
            $values,
            ['id' => $id],
        );
    }

    public function delete(int|string $id): int
    {
        $this->validateSelfState();

        return $this->connection->delete(
            $this->resourceName,
            ['id' => $id],
        );
    }

    public function validateSelfState(): void
    {
        if ($this->resourceName === null) {
            throw new InvalidArgumentException('Ресурс не задан');
        }

        if ($this->accessibleFields === null) {
            throw new InvalidArgumentException('Доступные поля не заданы');
        }
    }

    private function validateFieldsAccessible(array $fieldNames): void
    {
        $notAllowedFields = array_diff($fieldNames, $this->accessibleFields);

        if (empty($notAllowedFields) === false) {
            throw new InvalidArgumentException('Запрещен доступ к полям: ' . implode(', ', $notAllowedFields));
        }
    }
}
