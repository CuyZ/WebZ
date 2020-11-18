<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Transformer;

use Symfony\Contracts\HttpClient\ResponseInterface;

final class AutoTransformer implements Transformer
{
    public function toArray(ResponseInterface $input): array
    {
        return $input->toArray();
    }
}
