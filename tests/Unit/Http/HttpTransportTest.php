<?php

namespace CuyZ\WebZ\Tests\Unit\Http;

use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Http\HttpTransport
 */
class HttpTransportTest extends TestCase
{
    public function factoriesDataProvider(): array
    {
        return [
            [null],

            [fn() => new Client()],

            [
                new class implements GuzzleClientFactory {
                    public function build(?string $asyncCallHash): Client
                    {
                        return new Client();
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider factoriesDataProvider
     * @param mixed $clientFactory
     */
    public function test_returns_null_for_an_incompatible_payload($clientFactory)
    {
        $transport = new HttpTransport($clientFactory);

        $result = $transport->send(new stdClass());
        $resultAsync = $transport->sendAsync(new stdClass(), null);

        self::assertNull($result);
        self::assertNull($resultAsync);
    }

    public function test_returns_a_raw_result()
    {
        $payload = HttpPayload::request('GET', 'https://localhost')
            ->withTransformer(new ScalarTransformer());

        $client = Mocks::httpClient(
            new Response(200, [], 'foo'),
            new Response(200, [], 'bar')
        );

        $transport = new HttpTransport(fn() => $client);

        $result = $transport->send($payload);

        /** @var RawResult $resultAsync */
        $resultAsync = $transport->sendAsync($payload, null)->wait();

        self::assertSame(['value' => 'foo'], $result->data());

        self::assertInstanceOf(RawResult::class, $resultAsync);
        self::assertSame(['value' => 'bar'], $resultAsync->data());
    }

    public function test_throws_an_exception_on_errors()
    {
        $this->expectException(RequestException::class);

        $payload = HttpPayload::request('GET', 'https://localhost');

        $client = Mocks::httpClient(new RequestException('some_error', new Request('GET', 'test')));

        $transport = new HttpTransport(fn() => $client);
        $transport->send($payload);
    }
}
