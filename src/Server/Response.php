<?php
namespace MyPHPServer\Server;

class Response {
    private $statusCode = 200;
    private $statusText = 'OK';
    private $headers = [];
    private $body = '';

    public function setStatusCode(int $code, string $text = ''): self {
        $this->statusCode = $code;
        $this->statusText = $text ?: $this->getDefaultStatusText($code);
        return $this;
    }

    public function addHeader(string $name, string $value): self {
        $this->headers[] = "$name: $value";
        return $this;
    }

    public function setBody(string $body): self {
        $this->body = $body;
        return $this;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getStatusText(): string {
        return $this->statusText;
    }

    public function getHeaders(): array {
        return array_merge([
            'Content-Type: text/html; charset=utf-8',
            'X-Content-Type-Options: nosniff',
            'Content-Security-Policy: default-src \'self\'',
            'X-Frame-Options: DENY',
        ], $this->headers);
    }

    public function getBody(): string {
        return $this->body;
    }

    private function getDefaultStatusText(int $code): string {
        $statusTexts = [
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];
        return $statusTexts[$code] ?? 'Unknown Status';
    }
}