<?php declare(strict_types = 1);

namespace Chassis\Infrastructure\Persistence;

use Chassis\Domain\Entity;
use Chassis\Domain\EntityRepositoryInterface;
use Chassis\Infrastructure\Exception\NotFoundException;
use Doctrine\DBAL\Connection;

class EntityDBALRepository implements EntityRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Entity $entity
     *
     * @throws \Exception if the entity cannot be saved.
     */
    public function save(Entity $entity)
    {
        // TODO: Implement save() method.
    }

    /**
     * @param mixed $entityId
     *
     * @throws NotFoundException if the entity cannot be found.
     * @return Entity
     */
    public function find($entityId): Entity
    {
        // TODO: Implement find() method.
    }

    /**
     * @param array $criteria
     * @param array $sort
     * @param int|null $take
     * @param int $skip
     *
     * @return Entity[]
     */
    public function findBy(array $criteria, array $sort = [], int $take = null, int $skip = 0): array
    {
        // TODO: Implement findBy() method.
    }

    /**
     * Count entities matching criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria): int
    {
        // TODO: Implement countBy() method.
    }

    /**
     * Delete an existing entity.
     *
     * @param Entity|string|int $entityIdOrEntity
     *
     * @throws NotFoundException if the specified id doesn't match any entity.
     * @throws \Exception if the entity cannot be deleted.
     */
    public function delete($entityIdOrEntity): void
    {
        // TODO: Implement delete() method.
    }
}
