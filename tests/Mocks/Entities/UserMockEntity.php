<?php
declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Entity\Entity;

class UserMockEntity extends Entity
{
    public const string ID = 'id';
    public const string NAME = 'name';
    public const string EMAIL_ADDRESS = 'email_address';
    public const string AGE = 'age';
    public const string PHONE_NUMBER = 'phone_number';
    public int $id;
    public string $name;
    public string $email_address;
    public int $age;
    public string $phone_number;
    protected const array COLUMN_DESCRIPTIONS = [
        'id' => ['int', 'primary'],
        'name' => ['string'],
        'email_address' => ['string'],
        'age' => ['int'],
        'phone_number' => ['string']
    ];
}
