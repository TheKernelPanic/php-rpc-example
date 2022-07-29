<?php
declare(strict_types=1);

namespace RpcPHPSandbox;

use http\Exception\Exception;

/**
 * @method execute(array $arguments)
 */
class Client
{
    /**
     * @param string $host
     * @param int $port
     * @param string $class
     */
    public function __construct(
        private readonly string $host,
        private readonly int    $port,
        private readonly string $class
    )
    {}

    /**
     * @throws \Exception
     */
    public function __call(string $name, array $arguments): void
    {
        $socketHandler = fsockopen($this->host, $this->port);
        if ($socketHandler === false) {
            throw new \Exception(
                message: 'Open socket failure'
            );
        }

        $arguments = serialize($arguments);
        $payload = "Rpc-Class: {$this->class}\nRpc-Method: execute\nRpc-arguments: {$arguments}\n";
        fputs($socketHandler, $payload);

        $startTime = time();
        $response = '';
        while(!feof($socketHandler)) {

            $response .= fread($socketHandler, 1024);

            $timeElapsed = time() - $startTime;
            if ($timeElapsed > 24) {
                throw new \Exception(
                    message: 'Timeout error'
                );
            }
            $status = stream_get_meta_data($socketHandler);
            if ($status['timed_out']) {
                throw new \Exception(
                    message: 'Stream timeout'
                );
            }
        }
        echo $response;
        fclose($socketHandler);
    }
}