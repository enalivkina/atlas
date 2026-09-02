<?php

declare(strict_types=1);

namespace Atlas\Resource\Connection;

use Atlas\Resource\Connection\Contract\ConnectionFactoryInterface;
use Atlas\Resource\Connection\Contract\DataBaseConnectionInterface;

final class ConnectionFactory implements ConnectionFactoryInterface
{
    public function createConnection(array $config): DataBaseConnectionInterface
    {
        return match ($config['driver']) {
            'mysql' => new DbDataBaseConnection($config),
            'file' => new FileDataBaseConnection(...$config),
        };
    }
}
