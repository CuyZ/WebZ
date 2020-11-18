<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Transformer;

use Psr\Http\Message\ResponseInterface;

final class JsonTransformer implements Transformer
{
    public function toArray(ResponseInterface $input): array
    {
        /** @var array<mixed, mixed> $result */
        $result = json_decode(
            $input->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $result;
    }
}
