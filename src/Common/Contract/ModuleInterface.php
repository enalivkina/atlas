<?php

declare(strict_types=1);

namespace Atlas\Common\Contract;

interface ModuleInterface
{
    public function init(): void;
}
