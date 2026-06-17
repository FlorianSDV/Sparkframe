<?php

declare(strict_types=1);

namespace Sparkframe\Database\QueryBuilder\Traits;

use Exception;
use Sparkframe\Entity\Entity;

trait QueryWithEntitiesTrait
{
    /** @var Entity[] $this ->entities */
    private array $entities = [];

    /**
     * @throws Exception
     */
    public function addEntity(Entity $entity): static
    {
        $class_name = $entity::class;

        if ($this->entity_class !== $class_name) {
            throw new Exception("Entity class $class_name does not match the expected class {$this->entity_class}.");
        }
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * Add entities to be used in the query
     * @param Entity[] $entities
     */
    public function addEntities(array $entities): static
    {
        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }

        return $this;
    }

    /**
     * Clears the entities from the query.
     */
    public function clearEntities(): void
    {
        unset($this->entities);
    }
}
