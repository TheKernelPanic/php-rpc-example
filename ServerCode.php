<?php
declare(strict_types=1);

use RpcPHPSandbox\Server;

require_once __DIR__ . '/vendor/autoload.php';

$server = new Server(
    host: 'localhost',
    port: 8080
);

$server->run();
$server->process();