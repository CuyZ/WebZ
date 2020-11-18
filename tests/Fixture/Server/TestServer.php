<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Server;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Message\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use Throwable;

final class TestServer
{
    public const DOMAIN = 'http://localhost:8080';

    private HttpHandler $http;
    private SoapHandler $soap;

    public function __construct()
    {
        $this->http = new HttpHandler();
        $this->soap = new SoapHandler();
    }

    public function start(int $port = 8080): void
    {
        $this->soap->generateWsdl();

        $loop = Factory::create();

        $server = new HttpServer($loop, function (ServerRequestInterface $request) {
            try {
                switch ($request->getUri()->getPath()) {
                    case '/http':
                        return $this->http->handle($request);
                    case '/soap':
                        return $this->soap->handle($request);
                }

                return new Response(404);
            } catch (Throwable $e) {
                return new Response(500, ['Content-Type' => 'text/plain'], $e->getMessage());
            }
        });

        $socket = new SocketServer($port, $loop);
        $server->listen($socket);

        $loop->run();
    }
}
