<?php

namespace Sparkframe\Database\QueryBuilder;

use Exception;
use Sparkframe\Entity\Entity;

trait InsertQueryTrait
{
    /** @var class-string<Entity> $this ->entity_class */
    private string $entity_class;

    /** @var Entity[] $this ->entities */
    private array $entities = [];

    /**
     * @throws Exception
     */
    public function addEntity(Entity $entity): self
    {
        $class_name = $entity::class;
        if ($this->entity_class !== $class_name) {
            throw new Exception("Entity class $class_name does not match the expected class {$this->entity_class}.");
        }
        $this->entities[] = $entity;

        return $this;
    }

    public function clearEntities(): self
    {
        unset($this->entities);

        return $this;
    }

    public function clearEntityClass(): self
    {
        unset($this->entity_class);

        return $this;
    }
}