<?php

declare(strict_types=1);

namespace Atlas\tests\Support\Http;

use Atlas\Common\Contract\ModuleInterface;

final class TestModule implements ModuleInterface
{
    public function init(): void {}
}
