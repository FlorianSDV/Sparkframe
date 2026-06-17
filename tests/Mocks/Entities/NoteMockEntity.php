<?php

declare(strict_types=1);

namespace Sparkframe\Tests\Mocks\Entities;

use Sparkframe\Attributes\Column;
use Sparkframe\Attributes\Primary;
use Sparkframe\Entity\Entity;

/**
 * Mock entity used in tests to represent a note row.
 */
class NoteMockEntity extends Entity
{
    public const string ID = 'id';
    public const string TITLE = 'title';
    public const string CONTENT = 'content';
    public const string USER_ID = 'user_id';

    #[Primary]
    public int $id;
    #[Column]
    public string $title;
    #[Column]
    public string $content;
    #[Column]
    public int $user_id;
}
