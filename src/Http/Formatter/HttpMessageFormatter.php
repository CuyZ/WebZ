<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Formatter;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HttpMessageFormatter
{
    private ?int $maxBodyLength;

    public function __construct(?int $maxBodyLength = 1000)
    {
        $this->maxBodyLength = $maxBodyLength;
    }

    public function formatRequest(RequestInterface $request): string
    {
        $message = sprintf(
            "%s %s HTTP/%s\n",
            $request->getMethod(),
            $request->getRequestTarget(),
            $request->getProtocolVersion()
        );

        /**
         * @var string $name
         * @var array $values
         */
        foreach ($request->getHeaders() as $name => $values) {
            $message .= $name . ': ' . implode(', ', $values) . "\n";
        }

        return $this->addBody($request, $message);
    }

    public function formatResponse(ResponseInterface $response): string
    {
        $message = sprintf(
            "HTTP/%s %s %s\n",
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        /**
         * @var string $name
         * @var array $values
         */
        foreach ($response->getHeaders() as $name => $values) {
            $message .= $name . ': ' . implode(', ', $values) . "\n";
        }

        return $this->addBody($response, $message);
    }

    private function addBody(MessageInterface $request, string $message): string
    {
        $message .= "\n";
        $stream = $request->getBody();
        if (!$stream->isSeekable() || 0 === $this->maxBodyLength) {
            // Do not read the stream
            return $message;
        }

        $data = $stream->__toString();
        $stream->rewind();

        // all non-printable ASCII characters and <DEL> except for \t, \r, \n
        if (preg_match('/([\x00-\x09\x0C\x0E-\x1F\x7F])/', $data) !== 0) {
            return $message . '[binary stream omitted]';
        }

        if (null === $this->maxBodyLength) {
            return $message . $data;
        }

        return $message . mb_substr($data, 0, $this->maxBodyLength);
    }
}
