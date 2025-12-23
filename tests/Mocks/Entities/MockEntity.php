<?php
declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Entity\Entity;

class MockEntity extends Entity
{
    public const string ID = 'id';
    public const string NAME = 'name';
    public int $id;
    public string $name;
    protected const array COLUMN_DESCRIPTIONS = [
        'id' => ['int', 'primary'],
        'name' => ['string']
    ];
}
