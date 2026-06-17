<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Entity\Entity;

/**
 * Mock entity without a primary key, used to test error handling.
 */
class EntityWithoutPrimaryKey extends Entity
{
}
