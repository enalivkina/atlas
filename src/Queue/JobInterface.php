<?php

declare(strict_types=1);

namespace Atlas\Queue;

use Atlas\Container\ContainerInterface;

interface JobInterface
{
    public function doJob(ContainerInterface $container): void;
}
