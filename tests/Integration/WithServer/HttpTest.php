<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Integration\WithServer;

use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Server\HttpHandler;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;
use CuyZ\WebZ\Tests\Integration\ServerTestCase;
use DateTime;
use DateTimeZone;

/**
 * @coversNothing
 */
final class HttpTest extends ServerTestCase
{
    private function sets(): array
    {
        $payload = fn() => HttpPayload::request('POST', HttpHandler::route('returnValue'));

        return [
            [
                'payload' => $payload()->withBody('foo')
                    ->withHeader('Content-Type', 'text/plain')
                    ->withTransformer(new ScalarTransformer()),
                'result' => ['value' => 'foo'],
            ],
            [
                'payload' => $payload()->withBody(null)
                    ->withHeader('Content-Type', 'text/plain')
                    ->withTransformer(new ScalarTransformer()),
                'result' => ['value' => ''],
            ],
            [
                'payload' => $payload()->withJson(['foo' => 'bar'])
                    ->withTransformer(new JsonTransformer()),
                'result' => ['foo' => 'bar'],
            ],
        ];
    }

    public function parsedResultsDataProvider(): array
    {
        return $this->sets();
    }

    /**
     * @dataProvider parsedResultsDataProvider
     * @param HttpPayload $payload
     * @param mixed $raw
     */
    public function test_returns_a_parsed_result(HttpPayload $payload, $raw)
    {
        $bus = WebServiceBus::builder()
            ->withTransport(new HttpTransport())
            ->build();

        $webService = new DummyWrapResultWebService($payload);

        /** @var FakeResult $result */
        $result = $bus->call($webService);

        self::assertInstanceOf(FakeResult::class, $result);
        self::assertSame($raw, $result->raw);
    }

    public function test_does_an_async_call()
    {
        $bus = WebServiceBus::builder()
            ->withTransport(new HttpTransport())
            ->build();

        $data = array_map(
            function (array $set) {
                return [
                    'webservice' => new DummyWrapResultWebService($set['payload']),
                    'result' => $set['result'],
                ];
            },
            $this->sets()
        );

        $promises = $bus->callAsync(
            ...array_map(fn(array $set) => $set['webservice'], $data)
        );

        foreach ($promises as $index => $promise) {
            /** @var FakeResult $result */
            $result = $promise->wait();

            self::assertInstanceOf(FakeResult::class, $result);
            self::assertSame($data[$index]['result'], $result->raw);
        }
    }

    public function test_contains_the_request_and_response_traces()
    {
        $payload = HttpPayload::request()
            ->withMethod('POST')
            ->withBaseUri(HttpHandler::URI)
            ->withHeader('X-Foo', 'bar')
            ->withHeader('User-Agent', 'cuyz/webz')
            ->withAuthBearer('abcd1234')
            ->withQuery('route', 'returnValue')
            ->withQuery('a', 'b')
            ->withJson(['foo' => 'bar'])
            ->withTransformer(new JsonTransformer());

        $transport = new HttpTransport();

        $result = $transport->send($payload);

        self::assertSame(
            <<<REQUEST
POST /http?route=returnValue&a=b HTTP/1.1
Content-Length: 13
Content-Type: application/json
Host: localhost:8080
X-Foo: bar
User-Agent: cuyz/webz
Authorization: Bearer abcd1234

{"foo":"bar"}
REQUEST,
            $result->requestTrace()
        );

        $date = (new DateTime('now', new DateTimeZone('GMT')))->format('r');
        $date = str_replace('+0000', 'GMT', $date);

        self::assertSame(
            <<<RESPONSE
HTTP/1.1 200 OK
Content-Type: application/json
Server: ReactPHP/1
Date: $date
Content-Length: 13
Connection: close

{"foo":"bar"}
RESPONSE,
            $result->responseTrace()
        );
    }

    public function test_handles_a_204_response(): void
    {
        $payload = HttpPayload::request('POST', HttpHandler::route('empty'))
            ->withTransformer(new ScalarTransformer());

        $bus = WebServiceBus::builder()
            ->withTransport(new HttpTransport())
            ->build();

        $webService = new DummyWrapResultWebService($payload);

        /** @var FakeResult $result */
        $result = $bus->call($webService);

        self::assertInstanceOf(FakeResult::class, $result);
        self::assertSame(['value' => ''], $result->raw);
    }
}
