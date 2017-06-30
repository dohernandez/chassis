<?php declare(strict_types = 1);

namespace Chassis\Domain;

use Chassis\Infrastructure\Exception\NotFoundException;

interface EntityRepositoryInterface
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @param Entity $entity
     *
     * @throws \Exception if the entity cannot be saved.
     */
    public function save(Entity $entity);

    /**
     * @param mixed $id
     *
     * @return Entity
     *
     * @throws NotFoundException if the entity cannot be found.
     */
    public function find($id): Entity;

    /**
     * @param array $criteria
     * @param array $sort
     * @param int|null $take
     * @param int $skip
     *
     * @return Entity[]
     */
    public function findBy(array $criteria, array $sort = [], int $take = null, int $skip = 0): array;

    /**
     * Count entities matching criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria): int;

    /**
     * Delete an existing entity.
     *
     * @param Entity|string|int $idOrEntity
     *
     * @return void
     *
     * @throws NotFoundException if the specified id doesn't match any entity.
     * @throws \Exception if the entity cannot be deleted.
     */
    public function delete($idOrEntity): void;
}
