<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture;

final class Utils
{
    public static function random(string $input): string
    {
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = 64;

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $pieces [] = $keyspace[random_int(0, $max)];
        }

        return $input . '---' . implode('', $pieces);
    }
}
