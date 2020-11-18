<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture;

/**
 * @immutable
 */
final class FakeResult
{
    public array $raw;

    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }
}
