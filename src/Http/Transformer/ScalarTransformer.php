<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Transformer;

use Psr\Http\Message\ResponseInterface;

final class ScalarTransformer implements Transformer
{
    public function toArray(ResponseInterface $input): array
    {
        return [
            'value' => $input->getBody()->getContents(),
        ];
    }
}
