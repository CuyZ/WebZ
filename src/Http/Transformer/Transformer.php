<?php

namespace CuyZ\WebZ\Http\Transformer;

use Psr\Http\Message\ResponseInterface;

interface Transformer
{
    /**
     * @param ResponseInterface $input
     * @return array<mixed, mixed>
     */
    public function toArray(ResponseInterface $input): array;
}
