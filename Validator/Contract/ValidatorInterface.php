<?php

declare(strict_types=1);

namespace Atlas\Validator\Contract;

interface ValidatorInterface
{
    public function validate(mixed $value, string|array $rule): void;
}
