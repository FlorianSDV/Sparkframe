<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Entity\Entity;

class NoteMockEntity extends Entity
{
    public const string ID = 'id';
    public const string TITLE = 'title';
    public const string CONTENT = 'content';
    public const string USER_ID = 'user_id';
    public int $id;
    public string $title;
    public string $content;
    public int $user_id;
    protected const array COLUMN_DESCRIPTIONS = [
        'id' => ['integer', 'primary'],
        'title' => ['string'],
        'content' => ['text']
    ];
}