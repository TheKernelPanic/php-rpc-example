<?php
declare(strict_types=1);

use RpcPHPSandbox\Client;
use RpcPHPSandbox\TargetClass;

require_once __DIR__ . '/vendor/autoload.php';

$client = new Client(
    host: '127.0.0.1',
    port: 8080,
    class: TargetClass::class
);
$client->execute('value');
