<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Server;

use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use Exception;
use Laminas\Soap\AutoDiscover;
use Laminas\Soap\Server as SoapServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\Http\Message\Response;

final class SoapHandler implements RequestHandlerInterface
{
    private string $wsdl;

    public function __construct()
    {
        $wsdl = new AutoDiscover();
        $wsdl->setClass(FakeSoapServerClass::class)
            ->setUri(FakeSoapServerClass::URI);

        $this->wsdl = $wsdl->toXml();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return $this->sendWsdl($request);
        }

        return $this->handleSoap($request);
    }

    private function sendWsdl(ServerRequestInterface $request): Response
    {
        $query = $request->getQueryParams();

        if (!isset($query['wsdl'])) {
            return new Response(400);
        }

        return new Response(200, ['Content-Type' => 'application/wsdl+xml'], $this->wsdl);
    }

    private function handleSoap(ServerRequestInterface $request): Response
    {
        if ($request->getMethod() !== 'POST') {
            return new Response(400);
        }

        $server = new SoapServer(FakeSoapServerClass::WSDL);
        $server->setClass(FakeSoapServerClass::class);
        $server->setReturnResponse(true);

        $response = $server->handle($request->getBody()->getContents());

        return new Response(200, [], (string)$response);
    }

    public function generateWsdl(): void
    {
        $path = FakeSoapServerClass::WSDL;

        if (!file_exists(FakeSoapServerClass::TMP_DIR)) {
            mkdir(FakeSoapServerClass::TMP_DIR, 0777, true);
        }

        if (false === file_put_contents($path, $this->wsdl)) {
            throw new Exception('Unable to write WSDL file at ' . $path);
        }
    }
}
