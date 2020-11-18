<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Server;

use CuyZ\WebZ\Tests\Fixture\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Http\Message\Response;

final class HttpHandler implements RequestHandlerInterface
{
    public const URI = TestServer::DOMAIN . '/http';

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();

        if (!isset($query['route'])) {
            return new Response(400);
        }

        switch ($query['route']) {
            case 'returnValue':
                return $this->returnValueAction($request);
            case 'random':
                return $this->randomAction($request);
        }

        return new Response(404);
    }

    private function returnValueAction(ServerRequestInterface $request)
    {
        if ($request->getMethod() === 'POST') {
            $value = $request->getBody()->getContents();
        } else {
            $value = $request->getQueryParams()['value'] ?? null;
        }

        return new Response(200, ['Content-Type' => $request->getHeader('Content-Type')], $value);
    }

    private function randomAction(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();

        if (!isset($query['input'])) {
            return new Response(400);
        }

        return new Response(
            200,
            ['Content-Type' => 'text/plain'],
            Utils::random($query['input'])
        );
    }

    public static function route(string $route, array $query = []): string
    {
        $url = self::URI . '?route=' . $route;

        if (count($query) > 0) {
            foreach ($query as $key => $value) {
                $url .= "&$key=$value";
            }
        }

        return $url;
    }
}
