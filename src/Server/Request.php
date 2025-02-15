<?php
namespace MyPHPServer\Server;

class Request {
    private $method;
    private $path;
    private $headers;
    private $queryParams;
    private $bodyParams;

    public static function createFromSocket($socket, int $maxPostSize): self {
        $request = new self();
        $rawRequest = socket_read($socket, $maxPostSize);
        
        $request->parseRequest($rawRequest);
        return $request;
    }

    private function parseRequest(string $rawRequest): void {
        $lines = explode("\r\n", $rawRequest);
        $requestLine = array_shift($lines);
        
        // Parse method and path
        [$this->method, $path] = explode(' ', $requestLine, 3);
        $this->path = parse_url($path, PHP_URL_PATH);

        // Parse headers
        $this->headers = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) break;
            [$name, $value] = explode(': ', $line, 2);
            $this->headers[strtolower($name)] = $value;
        }

        // Parse query string
        $queryString = parse_url($path, PHP_URL_QUERY) ?? '';
        parse_str($queryString, $this->queryParams);

        // Parse body
        $body = substr($rawRequest, strpos($rawRequest, "\r\n\r\n") + 4);
        if ($this->method === 'POST') {
            parse_str($body, $this->bodyParams);
        }
    }

    // Getters and security methods
    public function getMethod(): string {
        return strtoupper($this->method);
    }

    public function getPath(): string {
        return $this->sanitizePath($this->path);
    }

    public function getHeader(string $name): ?string {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function getQueryParam(string $key): ?string {
        return $this->sanitizeInput($this->queryParams[$key] ?? null);
    }

    public function getBodyParam(string $key): ?string {
        return $this->sanitizeInput($this->bodyParams[$key] ?? null);
    }

    private function sanitizePath(string $path): string {
        $path = preg_replace('/[^a-zA-Z0-9\-_\/\.]/', '', $path);
        return '/' . ltrim($path, '/');
    }

    private function sanitizeInput(?string $input): ?string {
        return $input ? htmlspecialchars($input, ENT_QUOTES, 'UTF-8') : null;
    }
}