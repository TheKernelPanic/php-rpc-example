<?php
declare(strict_types=1);

namespace RpcPHPSandbox;

use http\Exception\Exception;
use Socket;

class Server
{
    private const SOCKET_BACKLOG_DEFAULT = 3;

    /**
     * @var Socket|bool
     */
    private Socket|bool $socket;

    /**
     * @param string $host
     * @param int $port
     */
    public function __construct(
        private readonly string $host,
        private readonly int $port
    )
    {}

    /**
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        /**
         * Create socket resource for IPv4 (TCP or UDP)
         * Bytes stream full-duplex
         * TCP protocol
         */
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (is_bool($this->socket)) {
            throw new \Exception(
                message: 'Socket creation failure: ' . socket_strerror(socket_last_error())
            );
        }

        /**
         * Check if local address can be declined
         */
        if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new \Exception(
                message: 'Socket set option failure: ' . socket_strerror(socket_last_error())
            );
        }

        /**
         * Binding
         */
        if (socket_bind($this->socket, $this->host, $this->port) === false) {
            throw new \Exception(
                message: 'Bind address failure: ' . socket_strerror(socket_last_error())
            );
        }

        /**
         * Listen
         */
        if (socket_listen($this->socket, self::SOCKET_BACKLOG_DEFAULT) === false ) {
            throw new \Exception(
                message: 'Socket listen failure: ' . socket_strerror(socket_last_error())
            );
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function process(): void
    {
        do {

            /**
             * Start
             */
            $socket = socket_accept($this->socket);

            if ($socket === false) {
                throw new \Exception(
                    message: 'Accept socket connection failure: ' . socket_strerror(socket_last_error())
                );
            }
            $buffer = socket_read($socket, 1024);

            try {
                $class = $this->getValueFromBuffer(
                    buffer: $buffer,
                    prefix: 'Rpc-Class'
                );
                $method = $this->getValueFromBuffer(
                    buffer: $buffer,
                    prefix: 'Rpc-Method'
                );
                $arguments = unserialize($this->getValueFromBuffer(
                    buffer: $buffer,
                    prefix: 'Rpc-Arguments'
                ));

                $class::$method(...$arguments);

                socket_write($socket, 'Successfully!');

            } catch (\InvalidArgumentException $exception) {
                socket_write($socket, $exception->getMessage());
            } finally {
                socket_close($socket);
            }
        } while (true);
    }

    /**
     * @param string $buffer
     * @param string $prefix
     * @return string
     * @throws \Exception
     */
    private function getValueFromBuffer(string $buffer, string $prefix): string
    {
        preg_match('/' . $prefix . ':\s(.*)\n/i', $buffer, $matches);

        if (count($matches) < 2) {
            throw new \InvalidArgumentException(
                message: "Invalid value for {$prefix}"
            );
        }

        return $matches[1];
    }
}