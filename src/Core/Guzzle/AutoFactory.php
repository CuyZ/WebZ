<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Guzzle;

use Closure;

final class AutoFactory implements GuzzleClientFactory
{
    private static ?SharedClientsFactory $sharedFactory = null;
    private GuzzleClientFactory $internal;

    /**
     * @param GuzzleClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        $this->internal = $this->wrap($factory);
    }

    public function build(?string $asyncCallHash): HttpClient
    {
        return $this->internal->build($asyncCallHash);
    }

    /**
     * @param GuzzleClientFactory|Closure|null $factory
     * @return GuzzleClientFactory
     */
    private function wrap($factory): GuzzleClientFactory
    {
        if (null === $factory) {
            return self::sharedFactory();
        }

        if ($factory instanceof Closure) {
            return new ClosureFactory($factory);
        }

        return $factory;
    }

    private static function sharedFactory(): SharedClientsFactory
    {
        return self::$sharedFactory ??= new SharedClientsFactory();
    }
}
