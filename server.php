<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

use MyPHPServer\Server;

// Configuration
$config = [
    'host'          => '127.0.0.1',
    'port'          => 8080,
    'document_root' => __DIR__ . '/public',
    'timeout'       => 30,
    'max_post_size' => 1024 * 1024, // 1MB
];

$server = new Server($config);
$server->start();