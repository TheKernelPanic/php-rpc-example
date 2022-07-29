<?php
declare(strict_types=1);

namespace RpcPHPSandbox;

class TargetClass
{
    /**
     * @param string $param
     * @return void
     */
    public static function execute(string $param): void
    {
        echo "Execute code  with param {$param}";
    }
}