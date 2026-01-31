<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Attributes\Column;
use Sparkframe\Attributes\Primary;
use Sparkframe\Entity\Entity;

class UserMockEntity extends Entity
{
    public const string ID = 'id';
    public const string NAME = 'name';
    public const string EMAIL_ADDRESS = 'email_address';
    public const string AGE = 'age';
    public const string PHONE_NUMBER = 'phone_number';
    #[Primary]
    public int $id;
    #[Column]
    public string $name;
    #[Column]
    public string $email_address;
    #[Column]
    public int $age;
    #[Column]
    public string $phone_number;

    public string $generic_property_that_is_not_a_column;
}
