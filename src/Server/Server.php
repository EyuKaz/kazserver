<?php
namespace MyPHPServer\Server;

use MyPHPServer\Server\Router;
use MyPHPServer\Server\StaticFileServer;
use MyPHPServer\Server\Request;
use MyPHPServer\Server\Response;

class Server {
    private $config;
    private $socket;
    private $router;
    private $staticFileServer;

    public function __construct(array $config) {
        $this->config = $config;
        $this->router = new Router();
        $this->staticFileServer = new StaticFileServer(
            $config['document_root'],
            $config['max_post_size']
        );
        
        $this->initializeRoutes();
    }

    private function initializeRoutes(): void {
        require __DIR__ . '/../../routes/web.php';
    }

    public function start(): void {
        $this->createSocket();
        $this->listen();
    }

    private function createSocket(): void {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->config['host'], $this->config['port']);
        socket_listen($this->socket);
        
        echo "Server running on http://{$this->config['host']}:{$this->config['port']}\n";
    }

    private function listen(): void {
        while (true) {
            $client = socket_accept($this->socket);
            if ($client === false) {
                continue;
            }

            try {
                $request = Request::createFromSocket($client, $this->config['max_post_size']);
                $response = $this->handleRequest($request);
                $this->sendResponse($client, $response);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                $this->sendErrorResponse($client, 500);
            } finally {
                socket_close($client);
            }
        }
    }

    private function handleRequest(Request $request): Response {
        if ($this->router->hasRoute($request->getPath())) {
            return $this->router->dispatch($request);
        }

        return $this->staticFileServer->handleRequest($request);
    }

    private function sendResponse($client, Response $response): void {
        $headers = $response->getHeaders();
        $headers[] = "Content-Length: " . strlen($response->getBody());
        
        socket_write($client, "HTTP/1.1 {$response->getStatusCode()} {$response->getStatusText()}\r\n");
        socket_write($client, implode("\r\n", $headers));
        socket_write($client, "\r\n\r\n");
        socket_write($client, $response->getBody());
    }

    private function sendErrorResponse($client, int $code): void {
        $response = new Response();
        $response->setStatusCode($code)
                 ->setBody("<h1>Error {$code}</h1>");
        $this->sendResponse($client, $response);
    }

    public function __destruct() {
        if ($this->socket) {
            socket_close($this->socket);
        }
    }
}