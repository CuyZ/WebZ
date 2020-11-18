<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Payload;

final class RequestPayload extends HttpPayload
{
    private string $method;
    private string $url;
    private array $options = [];

    public function __construct(string $method, string $url)
    {
        $this->method = $method;
        $this->url = $url;
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param mixed $body
     * @return self
     */
    public function withBody($body): self
    {
        unset($this->options['json']);

        $this->options['body'] = $body;

        return $this;
    }

    public function withJson(array $data): self
    {
        unset($this->options['body']);

        $this->options['json'] = $data;

        return $this;
    }

    public function withQuery(string $key, string $value): self
    {
        if (!isset($this->options['query'])
            || !is_array($this->options['query'])
        ) {
            $this->options['query'] = [];
        }

        $this->options['query'][$key] = $value;
        return $this;
    }

    public function withHeader(string $name, string $value): self
    {
        if (!isset($this->options['headers'])
            || !is_array($this->options['headers'])
        ) {
            $this->options['headers'] = [];
        }

        /** @var mixed $headers */
        $headers = $this->options['headers'][$name] ?? [];

        if (!is_array($headers)) {
            $headers = [];
        }

        $headers[] = $value;

        $this->options['headers'][$name] = $headers;
        return $this;
    }

    public function withAuthBasic(string $username, ?string $password = null): self
    {
        unset($this->options['auth_bearer']);
        unset($this->options['auth_ntlm']);

        $auth = $username;

        if (null !== $password && strlen($password) > 0) {
            $auth .= ":$password";
        }

        $this->options['auth_basic'] = $auth;

        return $this;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function options(): array
    {
        return $this->options;
    }
}
